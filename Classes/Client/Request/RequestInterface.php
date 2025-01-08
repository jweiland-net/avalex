<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client\Request;

use JWeiland\Avalex\Domain\Model\AvalexConfiguration;

/**
 * Interface for avalex requests
 */
interface RequestInterface
{
    public function isValidRequest(): bool;

    public function isJsonRequest(): bool;

    public function getEndpoint(): string;

    /**
     * Endpoint 'avx-datenschutzerklaerung' will be shorten to 'datenschutzerklaerung'
     */
    public function getEndpointWithoutPrefix(): string;

    public function getParameters(): array;

    public function setParameters(array $parameters): void;

    public function addParameter(string $parameter, mixed $value): void;

    public function getParameter(string $parameter): mixed;

    public function hasParameter(string $parameter): bool;

    public function setAvalexConfiguration(AvalexConfiguration $avalexConfiguration): void;

    /**
     * Merge all parameters to build a URI
     */
    public function buildUri(): string;
}
