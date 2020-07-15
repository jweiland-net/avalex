<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Hooks\ApiService;

/**
 * Interface to be used for
 * $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex']['JWeiland\\Avalex\\Service\\ApiService']->preApiRequest hook
 */
interface PreApiRequestHookInterface
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
