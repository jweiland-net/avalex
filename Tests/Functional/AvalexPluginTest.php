<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional;

use JWeiland\Avalex\AvalexPlugin;
use JWeiland\Avalex\Client\Request\Endpoint\ImpressumRequest;
use JWeiland\Avalex\Client\Request\RequestFactory;
use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use JWeiland\Avalex\Service\ApiService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class AvalexPluginTest extends FunctionalTestCase
{
    protected ApiService|MockObject $apiServiceMock;

    protected RequestFactory|MockObject $requestFactoryMock;

    protected ServerRequestInterface $request;

    protected AvalexPlugin $subject;

    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/avalex',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tx_avalex_configuration.csv');

        $site = new Site('main', 1, []);
        $routing = new PageArguments(12, '', []);

        $this->request = (new ServerRequest())
            ->withAttribute('site', $site)
            ->withAttribute('routing', $routing)
            ->withAttribute('currentContentObject', new ContentObjectRenderer());

        $this->apiServiceMock = $this->createMock(ApiService::class);
        $this->requestFactoryMock = $this->createMock(RequestFactory::class);

        $this->subject = new AvalexPlugin(
            $this->apiServiceMock,
            $this->requestFactoryMock,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->apiServiceMock,
            $this->requestFactoryMock,
            $this->request,
            $this->subject,
        );
    }

    #[Test]
    public function renderWithEmptyEndpointWillReturnErrorMessage(): void
    {
        $this->requestFactoryMock
            ->expects(self::once())
            ->method('create')
            ->with(self::equalTo('foo'))
            ->willReturn(null);

        self::assertSame(
            'EXT:avalex error: See logs for more details',
            $this->subject->render(
                '',
                [
                    'endpoint' => 'foo',
                ],
                $this->request,
            ),
        );
    }

    #[Test]
    public function renderWillReturnContent(): void
    {
        $this->requestFactoryMock
            ->expects(self::once())
            ->method('create')
            ->with(self::equalTo('avx-impressum'))
            ->willReturn(new ImpressumRequest());

        $this->apiServiceMock
            ->expects(self::once())
            ->method('getHtmlContentFromEndpoint')
            ->with(
                self::isInstanceOf(ImpressumRequest::class),
                self::isInstanceOf(ServerRequestInterface::class)
            )
            ->willReturn('Hello World!');

        self::assertSame(
            'Hello World!',
            $this->subject->render(
                '',
                [
                    'endpoint' => 'avx-impressum',
                ],
                $this->request,
            ),
        );
    }
}
