<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client\Request\Exception;

/**
 * This exception will be thrown, if no avalex endpoint object could be built for a specific request type
 */
class InvalidAvalexEndpointException extends \InvalidArgumentException {}
