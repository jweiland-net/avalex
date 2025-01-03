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
class ImpressumRequest implements RequestInterface, DomainRequestInterface, LocalizeableRequestInterface
{
    use RequestTrait;

    /**
     * Endpoint is something like "avx-get-domain-langs" or "avx-datenschutzerklaerung"
     *
     * @link https://documenter.getpostman.com/view/5293147/SWLYDCAk
     */
    const ENDPOINT = 'avx-impressum';

    const IS_JSON_REQUEST = false;

    protected array $allowedParameters = [
        'apikey' => 1,
        'domain' => 1,
        'lang' => 1,
    ];

    public function setDomain(string $domain): void
    {
        $this->addParameter('domain', $domain);
    }

    public function setLang(string $twoLetterLangIsoCode): void
    {
        $this->addParameter('lang', $twoLetterLangIsoCode);
    }
}
