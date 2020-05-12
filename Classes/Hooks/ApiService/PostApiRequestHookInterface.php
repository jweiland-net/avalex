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
 * $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex']['JWeiland\\Avalex\\Service\\ApiService']->postApiRequest hook
 */
interface tx_avalex_PostApiRequestHookInterface
{
    /**
     * Hook that allows you to modify the return value of ApiService::getHtmlForCurrentRootPage() using $content
     * and all public methods for information about the request.
     *
     * @param $content
     * @param tx_avalex_ApiService $apiService
     * @return mixed
     */
    public function postApiRequest(&$content, tx_avalex_ApiService $apiService);
}
