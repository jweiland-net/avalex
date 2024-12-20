<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Hooks\ApiService;

use JWeiland\Avalex\Service\ApiService;

/**
 * Interface to be used for
 * $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex'][\JWeiland\Avalex\Service\ApiService::class]->postApiRequest hook
 */
interface PostApiRequestHookInterface
{
    /**
     * Hook that allows you to modify the return value of ApiService::getHtmlForCurrentRootPage() using $content
     * and all public methods for information about the request.
     */
    public function postApiRequest(string &$content, ApiService $apiService): mixed;
}
