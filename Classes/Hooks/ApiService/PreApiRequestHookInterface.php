<?php
/*
 * This file is part of the TYPO3 CMS project.
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
 * Interface to be used for
 * $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex']['JWeiland\\Avalex\\Service\\ApiService']->preApiRequest hook
 */
interface tx_avalex_PreApiRequestHookInterface
{
    /**
     * Hook that allows you to modify the API key and/or the domain before sending
     * the request to the avalex servers.
     *
     * @param array $configuration avalex configuration record ['uid' => '<int>', 'api_key' => '<string>', 'domain' => '<string>']
     * @return string
     */
    public function preApiRequest(&$configuration);
}
