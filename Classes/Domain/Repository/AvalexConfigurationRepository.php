<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Domain\Repository;

use Doctrine\DBAL\Exception;
use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class AvalexConfigurationRepository
 */
class AvalexConfigurationRepository
{
    public const TABLE = 'tx_avalex_configuration';

    public function __construct(
        private readonly QueryBuilder $queryBuilder,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Order by "global" to get the individual configuration records first.
     */
    public function findByRootPageUid(int $websiteRoot): ?AvalexConfiguration
    {
        $queryBuilder = $this->queryBuilder;
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));

        try {
            $configurationRecord = $queryBuilder
                ->select('uid', 'api_key', 'domain', 'description')
                ->from(self::TABLE)
                ->where(
                    $queryBuilder->expr()->inSet(
                        'website_root',
                        $queryBuilder->createNamedParameter($websiteRoot, Connection::PARAM_INT),
                    ),
                )
                ->orWhere(
                    $queryBuilder->expr()->eq(
                        'global',
                        $queryBuilder->createNamedParameter(1, Connection::PARAM_INT),
                    ),
                )
                ->orderBy('global', 'ASC')
                ->executeQuery()
                ->fetchAssociative();

            if ($configurationRecord === false) {
                $this->logger->error('No Avalex configuration could be found in database for page UID: ' . $websiteRoot);
                return null;
            }

            return new AvalexConfiguration(
                (int)$configurationRecord['uid'],
                $configurationRecord['api_key'],
                $configurationRecord['domain'],
                $configurationRecord['description'],
            );
        } catch (Exception $exception) {
            $this->logger->error('Error in query of AvalexConfigurationRepository::findByRootPageUid: ' . $exception->getMessage());
        }

        return null;
    }
}
