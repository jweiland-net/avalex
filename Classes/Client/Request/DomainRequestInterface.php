<?php

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
interface DomainRequestInterface extends RequestInterface
{
    public function setDomain(string $domain): void;
}
