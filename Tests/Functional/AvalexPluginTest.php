<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional;

use JWeiland\Avalex\AvalexPlugin;
use JWeiland\Avalex\Client\Request\BedingungenRequest;
use JWeiland\Avalex\Client\Request\DatenschutzerklaerungRequest;
use JWeiland\Avalex\Client\Request\ImpressumRequest;
use JWeiland\Avalex\Client\Request\RequestInterface;
use JWeiland\Avalex\Client\Request\WiderrufRequest;
use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use JWeiland\Avalex\Domain\Repository\AvalexConfigurationRepository;
use JWeiland\Avalex\Service\ApiService;
use JWeiland\Avalex\Service\LanguageService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
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

    protected AvalexConfigurationRepository|MockObject $avalexConfigurationRepositoryMock;

    protected FrontendInterface|MockObject $cacheMock;

    protected LoggerInterface|MockObject $loggerMock;

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
        $this->avalexConfigurationRepositoryMock = $this->createMock(AvalexConfigurationRepository::class);
        $this->cacheMock = $this->createMock(FrontendInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->subject = new AvalexPlugin(
            $this->apiServiceMock,
            $this->avalexConfigurationRepositoryMock,
            $this->createMock(LanguageService::class),
            $this->cacheMock,
            $this->loggerMock,
            [
                0 => new BedingungenRequest(),
                1 => new DatenschutzerklaerungRequest(),
                2 => new ImpressumRequest(),
                3 => new WiderrufRequest(),
            ],
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->apiServiceMock,
            $this->avalexConfigurationRepositoryMock,
            $this->subject,
        );
    }

    #[Test]
    public function renderWithNoAvalexConfigurationWillReturnErrorMessage(): void
    {
        $this->avalexConfigurationRepositoryMock
            ->expects(self::once())
            ->method('findByRootPageUid')
            ->with(self::identicalTo(1))
            ->willReturn(null);

        self::assertSame(
            'EXT:avalex error: See logs for more details',
            $this->subject->render('', [], $this->request),
        );
    }

    #[Test]
    public function renderWithNonRegisteredEndpointWillLogError(): void
    {
        $this->loggerMock
            ->expects(self::once())
            ->method('error')
            ->with(self::identicalTo('There is no registered avalex request with specified endpoint: foo'));

        $this->avalexConfigurationRepositoryMock
            ->expects(self::once())
            ->method('findByRootPageUid')
            ->with(self::equalTo(1))
            ->willReturn(new AvalexConfiguration(
                1,
                'demo-key-with-online-shop',
                'https://example.com',
                '',
            ));

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
    public function renderWillReturnContentFromCache(): void
    {
        $this->avalexConfigurationRepositoryMock
            ->expects(self::once())
            ->method('findByRootPageUid')
            ->with(self::equalTo(1))
            ->willReturn(new AvalexConfiguration(
                1,
                'demo-key-with-online-shop',
                'https://example.com',
                '',
            ));

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
            $this->subject->render(
                '',
                [
                    'endpoint' => 'avx-bedingungen',
                ],
                $this->request,
            ),
        );
    }

    #[Test]
    public function renderWillReturnContent(): void
    {
        $this->apiServiceMock
            ->expects(self::once())
            ->method('getHtmlContentFromEndpoint')
            ->with(
                self::isInstanceOf(RequestInterface::class),
                self::isInstanceOf(ContentObjectRenderer::class),
            )
            ->willReturn('Hello World!');

        $this->avalexConfigurationRepositoryMock
            ->expects(self::once())
            ->method('findByRootPageUid')
            ->with(self::equalTo(1))
            ->willReturn(new AvalexConfiguration(
                1,
                'demo-key-with-online-shop',
                'https://example.com',
                '',
            ));

        self::assertSame(
            'Hello World!',
            $this->subject->render(
                '',
                [
                    'endpoint' => 'avx-bedingungen',
                ],
                $this->request,
            ),
        );
    }
}
