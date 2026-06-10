<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex;

/**
 * Defines the four available legal text content types.
 */
enum LegalTextContentTypeEnum: string
{
    case PRIVACY_POLICY = 'avalex_avalex';
    case IMPRINT = 'avalex_imprint';
    case TERMS_AND_CONDITIONS = 'avalex_bedingungen';
    case CANCELLATION_NOTICE = 'avalex_widerruf';
}
