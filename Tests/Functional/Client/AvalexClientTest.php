<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Client;

use JWeiland\Avalex\Client\AvalexClient;
use JWeiland\Avalex\Client\Request\Endpoint\ImpressumRequest;
use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class AvalexClientTest extends FunctionalTestCase
{
    protected RequestFactory|MockObject $requestFactoryMock;

    protected AvalexClient $subject;

    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/avalex',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestFactoryMock = $this->createMock(RequestFactory::class);

        $this->subject = new AvalexClient(
            $this->requestFactoryMock,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->requestFactoryMock,
            $this->subject,
        );
    }

    #[Test]
    public function processRequestWithInvalidRequestWillAddErrorMessage(): void
    {
        $impressumRequest = new ImpressumRequest();
        $impressumRequest->setAvalexConfiguration(new AvalexConfiguration(
            1,
            '',
            'https://example.com',
            '',
        ));

        $request = (new ServerRequest('https://example.com/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $request = $request->withAttribute('normalizedParams', NormalizedParams::createFromServerParams($request->getServerParams()));

        self::assertSame(
            'URI is empty or contains invalid chars. URI: https://avalex.de/avx-impressum?domain=https%3A%2F%2Fexample.com',
            $this->subject->processRequest($impressumRequest, $request)->getErrorMessage(),
        );
    }

    #[Test]
    public function processRequestWithEmptyResponseWillReturnErrorMessage(): void
    {
        $impressumRequest = new ImpressumRequest();
        $impressumRequest->setAvalexConfiguration(new AvalexConfiguration(
            1,
            'demo-key-with-online-shop',
            'https://example.com',
            '',
        ));

        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock
            ->expects($this->once())
            ->method('__toString')
            ->willReturn('');

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($streamMock);
        $responseMock
            ->expects($this->once())
            ->method('getHeaders')
            ->willReturn([]);
        $responseMock
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->requestFactoryMock
            ->expects($this->once())
            ->method('request')
            ->with('https://avalex.de/avx-impressum?apikey=demo-key-with-online-shop&domain=https%3A%2F%2Fexample.com')
            ->willReturn($responseMock);

        $request = (new ServerRequest('https://example.com/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $request = $request->withAttribute('normalizedParams', NormalizedParams::createFromServerParams($request->getServerParams()));

        self::assertSame(
            'The response of Avalex was empty.',
            $this->subject->processRequest($impressumRequest, $request)->getErrorMessage(),
        );
    }

    #[Test]
    public function processRequestWithHttpErrorWillReturnErrorMessage(): void
    {
        $impressumRequest = new ImpressumRequest();
        $impressumRequest->setAvalexConfiguration(new AvalexConfiguration(
            1,
            'demo-key-with-online-shop',
            'https://example.com',
            '',
        ));

        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock
            ->expects($this->once())
            ->method('__toString')
            ->willReturn('Error somewhere at avalex servers');

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($streamMock);
        $responseMock
            ->expects($this->once())
            ->method('getHeaders')
            ->willReturn([]);
        $responseMock
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(503);

        $this->requestFactoryMock
            ->expects($this->once())
            ->method('request')
            ->with('https://avalex.de/avx-impressum?apikey=demo-key-with-online-shop&domain=https%3A%2F%2Fexample.com')
            ->willReturn($responseMock);

        $request = (new ServerRequest('https://example.com/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $request = $request->withAttribute('normalizedParams', NormalizedParams::createFromServerParams($request->getServerParams()));

        self::assertSame(
            'Avalex Response ErrorError somewhere at avalex servers',
            $this->subject->processRequest($impressumRequest, $request)->getErrorMessage(),
        );
    }

    #[Test]
    public function processRequestWillRequestAvalexServer(): void
    {
        $impressumRequest = new ImpressumRequest();
        $impressumRequest->setAvalexConfiguration(new AvalexConfiguration(
            1,
            'demo-key-with-online-shop',
            'https://example.com',
            '',
        ));

        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock
            ->expects($this->once())
            ->method('__toString')
            ->willReturn('Hello World!');

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($streamMock);
        $responseMock
            ->expects($this->once())
            ->method('getHeaders')
            ->willReturn([]);
        $responseMock
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->requestFactoryMock
            ->expects($this->once())
            ->method('request')
            ->with('https://avalex.de/avx-impressum?apikey=demo-key-with-online-shop&domain=https%3A%2F%2Fexample.com')
            ->willReturn($responseMock);

        $request = (new ServerRequest('https://example.com/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $request = $request->withAttribute('normalizedParams', NormalizedParams::createFromServerParams($request->getServerParams()));

        self::assertSame(
            'Hello World!',
            $this->subject->processRequest($impressumRequest, $request)->getBody(),
        );
    }
}
