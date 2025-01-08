<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Service;

use JWeiland\Avalex\Client\AvalexClient;
use JWeiland\Avalex\Client\Request\Endpoint\ImpressumRequest;
use JWeiland\Avalex\Client\Response\AvalexResponse;
use JWeiland\Avalex\Service\ApiService;
use JWeiland\Avalex\Service\LanguageService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class ApiServiceTest extends FunctionalTestCase
{
    protected AvalexClient|MockObject $avalexClientMock;

    protected FrontendInterface|MockObject $cacheMock;

    protected ServerRequestInterface $request;

    protected ApiService $subject;

    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/avalex',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $site = new Site('main', 1, []);
        $routing = new PageArguments(12, '', []);

        $this->request = (new ServerRequest())
            ->withAttribute('site', $site)
            ->withAttribute('routing', $routing)
            ->withAttribute('currentContentObject', new ContentObjectRenderer());

        $this->avalexClientMock = $this->createMock(AvalexClient::class);
        $this->cacheMock = $this->createMock(FrontendInterface::class);

        $this->subject = new ApiService(
            $this->avalexClientMock,
            $this->createMock(LanguageService::class),
            $this->cacheMock,
            $this->getContainer()->get(EventDispatcherInterface::class),
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
        );
    }

    #[Test]
    public function getHtmlContentFromEndpointWillReturnContentFromCache(): void
    {
        $this->avalexClientMock
            ->expects(self::never())
            ->method('processRequest');

        $this->cacheMock
            ->expects(self::once())
            ->method('has')
            ->with(self::stringStartsWith('avalex_'))
            ->willReturn(true);

        $this->cacheMock
            ->expects(self::once())
            ->method('get')
            ->with(self::stringStartsWith('avalex_'))
            ->willReturn('Hello World!');

        self::assertSame(
            'Hello World!',
            $this->subject->getHtmlContentFromEndpoint(new ImpressumRequest(), $this->request),
        );
    }

    #[Test]
    public function getHtmlContentFromEndpointWithEmptyContentWillNotCacheContent(): void
    {
        $this->avalexClientMock
            ->expects(self::once())
            ->method('processRequest')
            ->with(self::isInstanceOf(ImpressumRequest::class))
            ->willReturn(new AvalexResponse('', [], 200, false));

        $this->cacheMock
            ->expects(self::once())
            ->method('has')
            ->with(self::stringStartsWith('avalex_'))
            ->willReturn(false);

        $this->cacheMock
            ->expects(self::never())
            ->method('set');

        self::assertSame(
            '',
            $this->subject->getHtmlContentFromEndpoint(new ImpressumRequest(), $this->request),
        );
    }

    #[Test]
    public function getHtmlContentFromEndpointWillCacheAndReturnContent(): void
    {
        $this->avalexClientMock
            ->expects(self::once())
            ->method('processRequest')
            ->with(self::isInstanceOf(ImpressumRequest::class))
            ->willReturn(new AvalexResponse('Hello World!', [], 200, false));

        $this->cacheMock
            ->expects(self::once())
            ->method('has')
            ->with(self::stringStartsWith('avalex_'))
            ->willReturn(false);

        $this->cacheMock
            ->expects(self::once())
            ->method('set')
            ->with(
                self::stringStartsWith('avalex_'),
                self::identicalTo('Hello World!'),
                self::identicalTo([]),
                self::identicalTo(21600),
            );

        self::assertSame(
            'Hello World!',
            $this->subject->getHtmlContentFromEndpoint(new ImpressumRequest(), $this->request),
        );
    }
}
