<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Domain\Model;

readonly class AvalexConfiguration
{
    public function __construct(
        private int $uid,
        private string $apiKey,
        private string $domain,
        private string $description,
    ) {}

    public function getUid(): int
    {
        return $this->uid;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
