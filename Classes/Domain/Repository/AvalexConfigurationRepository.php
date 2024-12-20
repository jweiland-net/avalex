<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Domain\Repository;

use JWeiland\Avalex\Exception\AvalexConfigurationNotFoundException;
use JWeiland\Avalex\Utility\Typo3Utility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class AvalexConfigurationRepository
 */
class AvalexConfigurationRepository
{
    const TABLE = 'tx_avalex_configuration';

    /**
     * Find all configurations
     */
    public function findAll(): array
    {
        $result = $this
            ->getQueryBuilder()
            ->select('*')
            ->from(self::TABLE)
            ->executeQuery()
            ->fetchAllAssociative();

        return ($result !== null) ? $result : [];
    }

    /**
     * @throws AvalexConfigurationNotFoundException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByWebsiteRoot(int $websiteRoot, string $select = '*'): array
    {
        // Order by "global" to get the individual configuration records first.
        $result = $this
            ->getQueryBuilder(self::TABLE)
            ->select(...GeneralUtility::trimExplode(',', $select))
            ->from(self::TABLE)
            ->where($this->getQueryBuilder(self::TABLE)->expr()->inSet('website_root', $websiteRoot))
            ->orWhere($this->getQueryBuilder(self::TABLE)->expr()->eq('global', 1))
            ->orderBy('global', 'ASC')
            ->executeQuery()
            ->fetchAssociative();

        if ($result === false) {
            throw new AvalexConfigurationNotFoundException(
                'No Avalex configuration could be found in database for page UID: ' . $websiteRoot
            );
        }

        return $result;
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE);
    }

    /**
     * Get additional where clause for a table
     */
    protected function getAdditionalWhereClause(string $table): string
    {
        $table = trim($table);
        $environmentService = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Service\EnvironmentService::class);
        if ($environmentService->isEnvironmentInFrontendMode()) {
            $whereClause =  $this->getTypoScriptFrontendController()->sys_page->deleteClause($table)
                . $this->getTypoScriptFrontendController()->sys_page->enableFields($table);
        } else {
            $whereClause = BackendUtility::deleteClause($table) . BackendUtility::BEenableFields($table);
        }
        return $whereClause;
    }

    protected function getDatabaseConnection(): Connection
    {
        return $GLOBALS['TYPO3_DB'];
    }

    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
