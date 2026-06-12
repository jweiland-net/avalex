<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Service;

use JWeiland\Avalex\Client\AvalexClient;
use JWeiland\Avalex\Client\Request\Endpoint\GetDomainLanguagesRequest;
use JWeiland\Avalex\Client\Request\Endpoint\ImpressumRequest;
use JWeiland\Avalex\Client\Response\AvalexResponse;
use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use JWeiland\Avalex\Service\LanguageService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class LanguageServiceTest extends FunctionalTestCase
{
    protected AvalexClient&MockObject $avalexClientMock;

    protected LanguageService $subject;

    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/avalex',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tx_avalex_configuration.csv');

        // Set is_siteroot to 1
        $this->setUpFrontendRootPage(1);

        $this->avalexClientMock = $this->createMock(AvalexClient::class);

        $this->subject = new LanguageService(
            $this->avalexClientMock,
            $this->createMock(FrontendInterface::class),
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->avalexClientMock,
            $this->subject,
        );
    }

    #[Test]
    public function addLanguageToEndpointWithoutResponseSetsDefaultLanguageToEndpoint(): void
    {
        $this->avalexClientMock
            ->expects($this->atLeastOnce())
            ->method('processRequest')
            ->with(self::isInstanceOf(GetDomainLanguagesRequest::class))
            ->willReturn(new AvalexResponse(
                '{}',
                [],
                200,
                true,
                '',
            ));

        $avalexConfiguration = new AvalexConfiguration(
            1,
            'avalex-api-key',
            'https://example.com',
            '',
        );

        $endpoint = new ImpressumRequest();
        $endpoint->setAvalexConfiguration($avalexConfiguration);

        $this->subject->addLanguageToEndpoint(
            $endpoint,
            $avalexConfiguration,
            $this->getRequestWithLanguage(''),
        );

        self::assertSame(
            'de',
            $endpoint->getParameter('lang'),
        );
    }

    #[Test]
    public function addLanguageToEndpointWithoutEndpointSetsDefaultLanguageToEndpoint(): void
    {
        $body = [
            'de' => [
                'invalid-endpoint' => 'foo->bar',
            ],
        ];

        $this->avalexClientMock
            ->expects($this->atLeastOnce())
            ->method('processRequest')
            ->with(self::isInstanceOf(GetDomainLanguagesRequest::class))
            ->willReturn(new AvalexResponse(
                json_encode($body),
                [],
                200,
                true,
                '',
            ));

        $avalexConfiguration = new AvalexConfiguration(
            1,
            'avalex-api-key',
            'https://example.com',
            '',
        );

        $endpoint = new ImpressumRequest();
        $endpoint->setAvalexConfiguration($avalexConfiguration);

        $this->subject->addLanguageToEndpoint(
            $endpoint,
            $avalexConfiguration,
            $this->getRequestWithLanguage('de'),
        );

        self::assertSame(
            'de',
            $endpoint->getParameter('lang'),
        );
    }

    #[Test]
    public function addLanguageToEndpointWithEndpointSetsLanguageToEndpoint(): void
    {
        $body = [
            'de' => [
                'impressum' => 'TYPO3 works',
            ],
        ];

        $this->avalexClientMock
            ->expects($this->atLeastOnce())
            ->method('processRequest')
            ->with(self::isInstanceOf(GetDomainLanguagesRequest::class))
            ->willReturn(new AvalexResponse(
                json_encode($body),
                [],
                200,
                true,
                '',
            ));

        $avalexConfiguration = new AvalexConfiguration(
            1,
            'avalex-api-key',
            'https://example.com',
            '',
        );

        $endpoint = new ImpressumRequest();
        $endpoint->setAvalexConfiguration($avalexConfiguration);

        $this->subject->addLanguageToEndpoint(
            $endpoint,
            $avalexConfiguration,
            $this->getRequestWithLanguage('de'),
        );

        self::assertSame(
            'de',
            $endpoint->getParameter('lang'),
        );
    }

    #[Test]
    public function addLanguageToEndpointWithMultipleEndpointsSetsLanguageToEndpoint(): void
    {
        $body = [
            'de' => [
                'impressum' => 'TYPO3 klappt',
            ],
            'en' => [
                'impressum' => 'TYPO3 works',
            ],
        ];

        $this->avalexClientMock
            ->expects($this->atLeastOnce())
            ->method('processRequest')
            ->with(self::isInstanceOf(GetDomainLanguagesRequest::class))
            ->willReturn(new AvalexResponse(
                json_encode($body),
                [],
                200,
                true,
                '',
            ));

        $avalexConfiguration = new AvalexConfiguration(
            1,
            'avalex-api-key',
            'https://example.com',
            '',
        );

        $endpoint = new ImpressumRequest();
        $endpoint->setAvalexConfiguration($avalexConfiguration);

        $this->subject->addLanguageToEndpoint(
            $endpoint,
            $avalexConfiguration,
            $this->getRequestWithLanguage('en'),
        );

        self::assertSame(
            'en',
            $endpoint->getParameter('lang'),
        );
    }

    public static function languageDataProvider(): array
    {
        return [
            'language empty. Fallback to en' => ['', 'en'],
            'language de' => ['de', 'de'],
            'language en' => ['en', 'en'],
        ];
    }

    #[Test]
    #[DataProvider('languageDataProvider')]
    public function getFrontendLocaleReturnsDefaultLanguage(string $language, string $expected): void
    {
        self::assertSame(
            $expected,
            $this->subject->getFrontendLocale($this->getRequestWithLanguage($language)),
        );
    }

    protected function getRequestWithLanguage(string $language): ServerRequestInterface
    {
        $site = new Site('main', 1, []);
        $routing = new PageArguments(12, '', []);

        return (new ServerRequest(new Uri('/'), 'GET'))
            ->withAttribute('site', $site)
            ->withAttribute('routing', $routing)
            ->withAttribute('currentContentObject', $this->get(ContentObjectRenderer::class))
            ->withAttribute(
                'language',
                new SiteLanguage(
                    1,
                    $language,
                    new Uri('/'),
                    [
                        'enabled' => true,
                        'iso-639-1' => $language,
                    ],
                ),
            );
    }
}
