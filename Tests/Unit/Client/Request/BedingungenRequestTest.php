<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Unit\Client\Request;

use JWeiland\Avalex\Client\Request\BedingungenRequest;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test BedingungenRequest
 */
class BedingungenRequestTest extends UnitTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/avalex'];

    /**
     * @var BedingungenRequest
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->subject = new BedingungenRequest();
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject
        );
    }

    /**
     * @test
     */
    public function getEndpointReturnsEndpoint(): void
    {
        self::assertSame(
            'avx-bedingungen',
            $this->subject->getEndpoint()
        );
    }

    /**
     * @test
     */
    public function getEndpointWithoutPrefixReturnsEndpointWithoutPrefix(): void
    {
        self::assertSame(
            'bedingungen',
            $this->subject->getEndpointWithoutPrefix()
        );
    }
}
