<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Client\Request;

/**
 * Interface for avalex requests
 */
interface RequestInterface
{
    public function isValidRequest(): bool;

    public function isJsonRequest(): bool;

    public function getEndpoint(): string;

    /**
     * Endpoint 'avx-datenschutzerklaerung' - 'datenschutzerklaerung'
     */
    public function getEndpointWithoutPrefix(): string;

    public function getParameters(): array;

    public function setParameters(array $parameters): void;

    public function addParameter(string $parameter, mixed $value): void;

    public function getParameter(string $parameter): mixed;

    public function hasParameter(string $parameter): bool;

    /**
     * Merge all parameters to build a URI
     */
    public function buildUri(): string;
}
