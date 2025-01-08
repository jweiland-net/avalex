<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Client\Response;

use JWeiland\Avalex\Client\Response\AvalexResponse;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class AvalexResponseTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/avalex',
    ];

    #[Test]
    public function getBodyReturnsEmptyString(): void
    {
        $subject = new AvalexResponse(
            '',
            [
                'Expires' => '0',
                'Content-Length' => '123',
            ],
            200,
            false
        );

        self::assertSame(
            '',
            $subject->getBody(),
        );
    }

    #[Test]
    public function getBodyReturnsContentAsString(): void
    {
        $subject = new AvalexResponse(
            'test123',
            [
                'Expires' => '0',
                'Content-Length' => '123',
            ],
            200,
            false
        );

        self::assertSame(
            'test123',
            $subject->getBody(),
        );
    }

    #[Test]
    public function getBodyReturnsContentAsArray(): void
    {
        $subject = new AvalexResponse(
            '{"firstname":"stefan"}',
            [
                'Expires' => '0',
                'Content-Length' => '123',
            ],
            200,
            true
        );

        self::assertSame(
            [
                'firstname' => 'stefan',
            ],
            $subject->getBody(),
        );
    }

    #[Test]
    public function getHeadersReturnsEmptyArray(): void
    {
        $subject = new AvalexResponse(
            'test123',
            [],
            200,
            false
        );

        self::assertSame(
            [],
            $subject->getHeaders(),
        );
    }

    #[Test]
    public function getHeadersWithStringReturnsArray(): void
    {
        $subject = new AvalexResponse(
            'test123',
            'Expires: 0' . CRLF . 'Content-Length: 123',
            200,
            false
        );

        self::assertSame(
            [
                'Expires' => [
                    0 => '0',
                ],
                'Content-Length' => [
                    0 => '123',
                ],
            ],
            $subject->getHeaders(),
        );
    }

    #[Test]
    public function getHeadersWithSimpleArrayReturnsArray(): void
    {
        $subject = new AvalexResponse(
            'test123',
            [
                'Expires' => '0',
                'Content-Length' => '123',
            ],
            200,
            false
        );

        self::assertSame(
            [
                'Expires' => [
                    0 => '0',
                ],
                'Content-Length' => [
                    0 => '123',
                ],
            ],
            $subject->getHeaders(),
        );
    }

    #[Test]
    public function getHeadersWithComplexArrayReturnsArray(): void
    {
        $headers = [
            'Expires' => [
                0 => '0',
                1 => '2',
            ],
            'Content-Length' => [
                0 => '123',
                1 => '321',
            ],
        ];

        $subject = new AvalexResponse('', $headers, 200, false);

        self::assertSame(
            $headers,
            $subject->getHeaders(),
        );
    }

    #[Test]
    public function getStatusCodeReturnsInitially200(): void
    {
        $subject = new AvalexResponse(
            '',
            [
                'Expires' => '0',
                'Content-Length' => '123',
            ],
            200,
            false
        );

        self::assertSame(
            200,
            $subject->getStatusCode(),
        );
    }

    #[Test]
    public function getStatusCodeReturns401(): void
    {
        $subject = new AvalexResponse(
            '',
            [
                'Expires' => '0',
                'Content-Length' => '123',
            ],
            401,
            false
        );

        self::assertSame(
            401,
            $subject->getStatusCode(),
        );
    }

    #[Test]
    public function isJsonResponseInitiallyReturnsFalse(): void
    {
        $subject = new AvalexResponse(
            '',
            [
                'Expires' => '0',
                'Content-Length' => '123',
            ],
            200,
            false
        );

        self::assertFalse(
            $subject->isJsonResponse(),
        );
    }

    #[Test]
    public function setJsonResponseSetsJsonResponse(): void
    {
        $subject = new AvalexResponse(
            '',
            [
                'Expires' => '0',
                'Content-Length' => '123',
            ],
            200,
            true
        );

        self::assertTrue(
            $subject->isJsonResponse(),
        );
    }
}
