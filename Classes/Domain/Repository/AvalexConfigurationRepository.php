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
        if (version_compare(TYPO3_version, '8.4', '>')) {
            $result = $this->getQueryBuilder(self::TABLE)->select('*')->from(self::TABLE)->execute()->fetchAll();
        } else {
            $result = $this->getDatabaseConnection()->exec_SELECTgetRows(
                '*',
                'tx_avalex_configuration',
                $this->getAdditionalWhereClause(self::TABLE)
            );
        }
        return ($result !== null) ? $result : array();
    }
}
