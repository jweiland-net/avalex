<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Domain\Repository\Exception;

/**
 * This exception will be thrown, if DB query could not find an avalex configuration record
 */
class NoAvalexConfigurationException extends \InvalidArgumentException {}
