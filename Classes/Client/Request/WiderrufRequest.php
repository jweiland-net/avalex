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
class WiderrufRequest extends AbstractRequest implements DomainRequestInterface, LocalizeableRequestInterface
{
    protected string $endpoint = 'avx-widerruf';

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
