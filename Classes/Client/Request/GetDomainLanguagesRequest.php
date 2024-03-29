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
    /**
     * @var string
     */
    protected $endpoint = 'avx-get-domain-langs';

    /**
     * @var bool
     */
    protected $isJsonRequest = true;

    /**
     * @var array
     */
    protected $allowedParameters = [
        'apikey' => 1,
        'domain' => 1,
        'version' => 1,
    ];

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->addParameter('domain', $domain);
    }
}
