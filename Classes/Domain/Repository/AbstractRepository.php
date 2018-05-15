<?php
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

/**
 * Class AbstractRepository
 */
abstract class tx_avalex_AbstractRepository
{
    /**
     * Get DatabaseConnection.
     * Only for < TYPO3 8.5
     *
     * @return t3lib_DB
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return tslib_fe
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
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
        if (TYPO3_MODE === 'FE') {
            $whereClause =  $this->getTypoScriptFrontendController()->sys_page->deleteClause($table)
                . $this->getTypoScriptFrontendController()->sys_page->enableFields($table);
        } else {
            $whereClause = t3lib_BEfunc::deleteClause($table) . t3lib_BEfunc::BEenableFields($table);
        }
        return $whereClause;
    }
}
