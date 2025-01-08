<?php

declare(strict_types=1);

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
            // define inSet filter for website_root to reuse as conditional AND sorting criteria.
            $websiteRootInSetFilter = $queryBuilder->expr()->inSet(
                'website_root',
                // PostgreSQL has issue using named parameters for inSet(), which may be either an issue how the
                // ExpressionBuilder compatibility is created by TYPO3 or PostgreSQL itself. Directly use value
                // directly here which is considerable safe in this place.
                (string)$websiteRoot,
            );
            $queryBuilder
                ->select('uid', 'api_key', 'domain', 'description')
                ->from(self::TABLE)
                ->where(
                    $queryBuilder->expr()->or(
                        $websiteRootInSetFilter,
                        $queryBuilder->expr()->eq(
                            'global',
                            $queryBuilder->createNamedParameter(1, Connection::PARAM_INT),
                        ),
                    ),
                );

            // Define correct multi-level sorting to retrieve higher preceding configuration first.
            // 1st level: $websiteRoot id found in record `website_root` set first, not matching last
            // 2nd level: for each 1st level sort by GLOBAL = 1 first and not global last.
            // 3nd level: in case 1st and 2nd level is not unique enough which could happen, uid is added as
            //            third sorting criteria to ensure a deterministic result set sorting and mitigates
            //            result randomization.
            $queryBuilder->getConcreteQueryBuilder()
                ->orderBy($queryBuilder->expr()->castInt($websiteRootInSetFilter), 'DESC')
                ->addOrderBy('global', 'DESC')
                ->addOrderBy('uid', 'ASC');

            // Only first result record is required and result set is limited to one record to ensure correctly
            // closed result buffer when only retrieving one record even if result holds more records to avoid
            // issues with some database vendors and drivers.
            $queryBuilder->setMaxResults(1);
            $configurationRecord = $queryBuilder->executeQuery()->fetchAssociative();

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
