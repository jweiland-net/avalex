<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Domain\Repository;

use JWeiland\Avalex\Exception\AvalexConfigurationNotFoundException;
use JWeiland\Avalex\Utility\AvalexUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AvalexConfigurationRepository
 */
class AvalexConfigurationRepository extends AbstractRepository
{
    const TABLE = 'tx_avalex_configuration';

    /**
     * Find all configurations
     *
     * @return array
     */
    public function findAll()
    {
        if (version_compare(AvalexUtility::getTypo3Version(), '8.4', '>')) {
            $result = $this->getQueryBuilder(self::TABLE)->select('*')->from(self::TABLE)->execute()->fetchAll();
        } else {
            $result = $this->getDatabaseConnection()->exec_SELECTgetRows(
                '*',
                'tx_avalex_configuration',
                substr($this->getAdditionalWhereClause(self::TABLE), 5)
            );
        }
        return ($result !== null) ? $result : [];
    }

    /**
     * @param int $websiteRoot
     * @param string $select
     * @return array
     * @throws AvalexConfigurationNotFoundException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findByWebsiteRoot($websiteRoot, $select = '*')
    {
        $websiteRoot = (int)$websiteRoot;
        if (version_compare(AvalexUtility::getTypo3Version(), '8.4', '>')) {
            $result = $this
                ->getQueryBuilder(self::TABLE)
                ->select(...GeneralUtility::trimExplode(',', $select))
                ->from(self::TABLE)
                ->where($this->getQueryBuilder(self::TABLE)->expr()->inSet('website_root', $websiteRoot))
                ->orWhere($this->getQueryBuilder(self::TABLE)->expr()->eq('global', 1))
                ->execute()
                ->fetch();
        } else {
            $result = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
                $select,
                self::TABLE,
                sprintf(
                    '(FIND_IN_SET(%d, website_root) OR global = 1) %s',
                    $websiteRoot,
                    $this->getAdditionalWhereClause(self::TABLE)
                )
            );
        }

        if ($result === null || $result === false) {
            throw new AvalexConfigurationNotFoundException(
                'No Avalex configuration could be found in database for page UID: ' . $websiteRoot
            );
        }

        return $result;
    }
}
