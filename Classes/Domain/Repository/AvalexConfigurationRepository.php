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
                substr($this->getAdditionalWhereClause(self::TABLE), 5)
            );
        }
        return ($result !== null) ? $result : array();
    }

    /**
     * @param int $websiteRoot
     * @return string
     */
    public function findApiKeyByWebsiteRoot($websiteRoot)
    {
        $websiteRoot = (int)$websiteRoot;
        if (version_compare(TYPO3_version, '8.4', '>')) {
            $result = $this
                ->getQueryBuilder(self::TABLE)
                ->select('c.api_key')
                ->from(self::TABLE, 'c')
                ->where($this->getQueryBuilder(self::TABLE)->expr()->inSet('c.website_root', $websiteRoot))
                ->orWhere($this->getQueryBuilder(self::TABLE)->expr()->eq('c.global', 1))
                ->execute()
                ->fetch();
        } else {
            $result = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
                'c.api_key',
                sprintf('%s c', self::TABLE),
                sprintf(
                    '(FIND_IN_SET(%d, c.website_root) OR c.global = 1) %s',
                    $websiteRoot,
                    $this->getAdditionalWhereClause(self::TABLE)
                )
            );
        }
        return ($result !== null) ? $result['api_key'] : '';
    }
}
