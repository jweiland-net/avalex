<?php
namespace JWeiland\Avalex\Domain\Repository;

/*
 * This file is part of the avalex project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class AbstractRepository
 */
abstract class AbstractRepository
{
    /**
     * Get DatabaseConnection.
     * Only for < TYPO3 8.5
     *
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Get query builder
     *
     * @param string $table will use TABLE constant as default table
     * @return QueryBuilder
     */
    protected function getQueryBuilder($table)
    {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\ConnectionPool')
            ->getQueryBuilderForTable($table);
    }

    /**
     * Get additional where clause for a table
     *
     * @param string $table
     * @return string
     */
    protected function getAdditionalWhereClause($table)
    {
        $table = trim($table);
        /** @var EnvironmentService $environmentService */
        $environmentService = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Service\\EnvironmentService');
        if ($environmentService->isEnvironmentInFrontendMode()) {
            $whereClause =  $this->getTypoScriptFrontendController()->sys_page->deleteClause($table)
                . $this->getTypoScriptFrontendController()->sys_page->enableFields($table);
        } else {
            $whereClause = BackendUtility::deleteClause($table) . BackendUtility::BEenableFields($table);
        }
        return $whereClause;
    }
}
