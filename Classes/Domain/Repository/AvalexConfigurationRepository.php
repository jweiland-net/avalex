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
use JWeiland\Avalex\Domain\Repository\Exception\DatabaseQueryException;
use JWeiland\Avalex\Domain\Repository\Exception\NoAvalexConfigurationException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This repo can query the avalex configuration record with the highest priority for a given page UID
 */
class AvalexConfigurationRepository
{
    private const TABLE = 'tx_avalex_configuration';

    public function __construct(
        private readonly QueryBuilder $queryBuilder,
    ) {}

    /**
     * Order by "global" to get the individual configuration record first.
     */
    public function findByRootPageUid(int $websiteRoot): AvalexConfiguration
    {
        $queryBuilder = $this->getPreConfiguredQueryBuilder();

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
            // 3rd level: in case 1st and 2nd level is not unique enough which could happen, uid is added as
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
                throw new NoAvalexConfigurationException(
                    'No Avalex configuration could be found in database for page UID: ' . $websiteRoot
                );
            }

            return new AvalexConfiguration(
                (int)$configurationRecord['uid'],
                $configurationRecord['api_key'],
                $configurationRecord['domain'],
                $configurationRecord['description'],
            );
        } catch (Exception $exception) {
            throw new DatabaseQueryException(
                'Error in query of AvalexConfigurationRepository::findByRootPageUid: ' . $exception->getMessage()
            );
        }
    }

    /**
     * Hidden records are normally displayed in the context of the TYPO3 backend. However, this can lead
     * to confusion for backend users if they see an avalex configuration record in the backend, but this record
     * is not taken into account in the frontend context. For this reason, we also disable the display of
     * hidden avalex configuration records in the backend (keep DefaultRestriction).
     */
    private function getPreConfiguredQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->queryBuilder;

        if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {
            $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        }

        return $queryBuilder;
    }
}
