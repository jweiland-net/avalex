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
                ->select('uid', 'content')
                ->where($this->getQueryBuilder(self::TABLE)->expr()->eq('website_root', (int)$websiteRoot))
                ->from(self::TABLE)
                ->execute()
                ->fetch();
        } else {
           $result = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
               'uid, content',
               self::TABLE,
               sprintf(
                   'website_root = %d %s',
                   $websiteRoot,
                   $this->getAdditionalWhereClause(self::TABLE)
               )
           );
        }
        return $result;
    }

    /**
     * Update legal text by website root
     *
     * @param string $legalText
     * @param int $websiteRoot
     * @return void
     */
    public function updateByWebsiteRoot($legalText, $websiteRoot)
    {
        if (version_compare(TYPO3_version, '8.4', '>')) {
            $this
                ->getQueryBuilder(self::TABLE)
                ->update(self::TABLE)
                ->set('content', trim($legalText))
                ->set('tstamp', time())
                ->where($this->getQueryBuilder(self::TABLE)->expr()->eq('website_root', (int)$websiteRoot))
                ->from(self::TABLE)
                ->execute();
        } else {
            $this->getDatabaseConnection()->exec_UPDATEquery(
                self::TABLE,
                'website_root = ' . (int)$websiteRoot,
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
     * @param int $websiteRoot
     * @return void
     */
    public function insert($legalText, $websiteRoot)
    {
        if (version_compare(TYPO3_version, '8.4', '>')) {
            $this
                ->getQueryBuilder(self::TABLE)
                ->insert(self::TABLE)
                ->values(
                    array(
                        'website_root' => (int)$websiteRoot,
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
                    'website_root' => (int)$websiteRoot,
                    'content' => trim($legalText),
                    'crdate' => time(),
                    'tstamp' => time()
                )
            );
        }
    }
}
