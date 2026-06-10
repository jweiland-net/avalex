<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client;

/**
 * Contains the four avalex API entry points used to retrieve content for avalex content elements.
 *
 * This enum intentionally does not include endpoints for domain language retrieval
 * or API key validation requests.
 */
enum AvalexEndpointEnum: string
{
    case PRIVACY_POLICY = 'avx-datenschutzerklaerung';
    case IMPRINT = 'avx-impressum';
    case TERMS_AND_CONDITIONS = 'avx-bedingungen';
    case CANCELLATION_NOTICE = 'avx-widerruf';
}
