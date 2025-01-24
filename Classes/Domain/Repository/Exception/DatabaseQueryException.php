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
 * This exception will be thrown, if there is a hard error while query the avalex configuration record
 */
class DatabaseQueryException extends \RuntimeException {}
