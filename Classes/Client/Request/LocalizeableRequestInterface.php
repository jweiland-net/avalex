<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client\Request;

/**
 * Interface for localizeable Avalex requests
 */
interface LocalizeableRequestInterface extends RequestInterface
{
    public function setLang(string $twoLetterLangIsoCode): void;
}
