<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client\Request;

/**
 * Avalex Request to retrieve domain languages
 *
 * @link https://documenter.getpostman.com/view/5293147/SWLYDCAk#0964aca9-4e31-4a5d-a52b-d2281bbec28c
 */
class GetDomainLanguagesRequest extends AbstractRequest implements DomainRequestInterface
{
    protected string $endpoint = 'avx-get-domain-langs';

    protected bool $isJsonRequest = true;

    protected array $allowedParameters = [
        'apikey' => 1,
        'domain' => 1,
        'version' => 1,
    ];

    public function setDomain(string $domain): void
    {
        $this->addParameter('domain', $domain);
    }
}
