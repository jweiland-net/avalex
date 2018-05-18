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
 * Class LegalTextRepository
 */
class LegalTextRepository extends AbstractRepository
{
    const TABLE = 'tx_avalex_legaltext';

    /**
     * Find by website root
     *
     * @param int $websiteRoot
     * @return array|false|null
     */
    public function findByWebsiteRoot($websiteRoot)
    {
        if (version_compare(TYPO3_version, '8.4', '>')) {
            $result = $this
                ->getQueryBuilder(self::TABLE)
                ->select('l.uid', 'l.content', 'l.configuration', 'c.global')
                ->from(self::TABLE, 'l')
                ->leftJoin('l', 'tx_avalex_configuration', 'c', 'c.uid = l.configuration')
                ->where($this->getQueryBuilder(self::TABLE)->expr()->eq('c.website_root', (int)$websiteRoot))
                ->orWhere($this->getQueryBuilder(self::TABLE)->expr()->eq('c.global', 1))
                ->execute()
                ->fetchAll();
        } else {
           $result = $this->getDatabaseConnection()->exec_SELECTgetRows(
               'tx_avalex_legaltext.uid, tx_avalex_legaltext.content, c.global',
               self::TABLE . ' LEFT JOIN tx_avalex_configuration c ON tx_avalex_legaltext.configuration = c.uid',
               sprintf(
                   '(FIND_IN_SET(%d, c.website_root) OR c.global = 1) %s',
                   $websiteRoot,
                   $this->getAdditionalWhereClause(self::TABLE)
               )
           );
        }
        $result = $this->sortRecords((array)$result);
        return array_shift($result);
    }

    /**
     * Find record by configuration uid
     *
     * @param $configurationUid
     * @return array|null
     */
    public function findByConfigurationUid($configurationUid)
    {
        $configurationUid = (int)$configurationUid;
        if (version_compare(TYPO3_version, '8.4', '>')) {
            $result = $this
                ->getQueryBuilder(self::TABLE)
                ->select('uid', 'content', 'configuration')
                ->from(self::TABLE)
                ->where($this->getQueryBuilder(self::TABLE)->expr()->eq('configuration', $configurationUid))
                ->execute()
                ->fetchAll();
        } else {
            $result = $this->getDatabaseConnection()->exec_SELECTgetRows(
                'uid, content, configuration',
                self::TABLE,
                sprintf(
                    'configuration = %d %s',
                    $configurationUid,
                    $this->getAdditionalWhereClause(self::TABLE)
                )
            );
        }
        $result = $this->sortRecords((array)$result);
        return array_shift($result);
    }

    /**
     * Sort records. The GLOBAL configurations will be inserted BELOW all non global records.
     *
     * @param array $records
     * @return array
     */
    private function sortRecords(array $records)
    {
        $nonGlobal = array();
        $global = array();
        foreach ($records as $record) {
            if ($record['global']) {
                $global[] = $record;
            } else {
                $nonGlobal[] = $record;
            }
        }
        return array_merge($nonGlobal, $global);
    }

    /**
     * Update legal text by configuration uid
     *
     * @param string $legalText
     * @param int $configurationUid
     * @return void
     */
    public function updateByConfigurationUid($legalText, $configurationUid)
    {
        $configurationUid = (int)$configurationUid;
        if (version_compare(TYPO3_version, '8.4', '>')) {
            $this
                ->getQueryBuilder(self::TABLE)
                ->update(self::TABLE)
                ->where($this->getQueryBuilder(self::TABLE)->expr()->eq('configuration', $configurationUid))
                ->set('content', trim($legalText))
                ->set('tstamp', time())
                ->execute();
        } else {
            $this->getDatabaseConnection()->exec_UPDATEquery(
                self::TABLE,
                'configuration = ' . (int)$configurationUid,
                array(
                    'content' => trim($legalText),
                    'tstamp' => time()
                )
            );
        }
    }

    /**
     * Insert a new legal text
     *
     * @param string $legalText
     * @param int $configurationUid
     * @return void
     */
    public function insert($legalText, $configurationUid)
    {
        $configurationUid = (int)$configurationUid;
        if (version_compare(TYPO3_version, '8.4', '>')) {
            $this
                ->getQueryBuilder(self::TABLE)
                ->insert(self::TABLE)
                ->values(
                    array(
                        'configuration' => $configurationUid,
                        'content' => trim($legalText),
                        'crdate' => time(),
                        'tstamp' => time()
                    )
                )
                ->execute();
        } else {
            $this->getDatabaseConnection()->exec_INSERTquery(
                self::TABLE,
                array(
                    'configuration' => $configurationUid,
                    'content' => trim($legalText),
                    'crdate' => time(),
                    'tstamp' => time()
                )
            );
        }
    }
}
