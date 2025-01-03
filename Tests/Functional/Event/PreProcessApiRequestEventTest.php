<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Event;

use JWeiland\Avalex\Client\Request\BedingungenRequest;
use JWeiland\Avalex\Client\Request\RequestInterface;
use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use JWeiland\Avalex\Event\PreProcessApiRequestEvent;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class PreProcessApiRequestEventTest extends FunctionalTestCase
{
    protected PreProcessApiRequestEvent $subject;

    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/avalex',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $endpointRequest = new BedingungenRequest();
        $endpointRequest->setAvalexConfiguration(new AvalexConfiguration(
            1,
            'demo-key-with-online-shop',
            'https://example.com',
            '',
        ));

        $this->subject = new PreProcessApiRequestEvent(
            $endpointRequest,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
        );
    }

    #[Test]
    public function getEndpointWillReturnEndpoint(): void
    {
        self::assertInstanceOf(
            RequestInterface::class,
            $this->subject->getEndpointRequest(),
        );
    }
}
