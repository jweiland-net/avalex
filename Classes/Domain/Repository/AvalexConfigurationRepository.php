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
 * Class AvalexConfigurationRepository
 */
class tx_avalex_AvalexConfigurationRepository extends tx_avalex_AbstractRepository
{
    const TABLE = 'tx_avalex_configuration';

    /**
     * Find all configurations
     *
     * @return array
     */
    public function findAll()
    {
        $result = $this->getDatabaseConnection()->exec_SELECTgetRows(
            '*',
            'tx_avalex_configuration',
            substr($this->getAdditionalWhereClause(self::TABLE), 5)
        );
        return ($result !== null) ? $result : array();
    }
}
