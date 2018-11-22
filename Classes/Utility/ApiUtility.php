<?php
namespace JWeiland\Avalex\Utility;

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
 * Class ApiUtility
 */
class ApiUtility
{
    /**
     * @var string
     */
    protected static $apiUrl = 'https://avalex.de/';

    /**
     * Returns the API url with trailing slash
     *
     * @return string
     */
    public static function getApiUrl()
    {
        return self::$apiUrl;
    }
}
