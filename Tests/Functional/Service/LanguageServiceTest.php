<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Service;

use JWeiland\Avalex\Client\AvalexClient;
use JWeiland\Avalex\Client\Request\GetDomainLanguagesRequest;
use JWeiland\Avalex\Client\Request\ImpressumRequest;
use JWeiland\Avalex\Client\Response\AvalexResponse;
use JWeiland\Avalex\Service\LanguageService;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class LanguageServiceTest extends FunctionalTestCase
{
    /**
     * @var AvalexClient|MockObject
     */
    protected $avalexClientMock;

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
        GeneralUtility::addInstance(AvalexClient::class, $this->avalexClientMock);

        $this->subject = new LanguageService([
            'domain' => 'https://example.com',
            'api_key' => 'demo-key-with-online-shop',
        ]);
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $GLOBALS['TSFE']
        );
    }

    protected function setEnvironmentWithLanguage(string $language): void
    {
        if (class_exists(SiteLanguage::class)) {
            $serverRequest = new ServerRequest(
                new Uri('/'),
                'GET'
            );
            $GLOBALS['TYPO3_REQUEST'] = $serverRequest->withAttribute(
                'language',
                new SiteLanguage(
                    1,
                    $language,
                    new Uri('/'),
                    [
                        'enabled' => true,
                        'iso-639-1' => $language,
                    ]
                )
            );
        }

        /** @var TypoScriptFrontendController|MockObject|AccessibleObjectInterface $typoScriptFrontendControllerMock */
        $typoScriptFrontendControllerMock = $this->getAccessibleMock(TypoScriptFrontendController::class, [], [], '', false);
        $GLOBALS['TSFE'] = $typoScriptFrontendControllerMock;
        $GLOBALS['TSFE']->id = 1;
        $GLOBALS['TSFE']->_set('lang', $language ?: 'en');
        $GLOBALS['TSFE']->_set('spamProtectEmailAddresses', 1);
    }

    /**
     * @test
     */
    public function addLanguageToEndpointWithoutResponseSetsDefaultLanguageToEndpoint(): void
    {
        $this->setEnvironmentWithLanguage('');

        /** @var AvalexResponse|MockObject $avalexResponseMock */
        $avalexResponseMock = $this->createMock(AvalexResponse::class);
        $avalexResponseMock
            ->expects(self::atLeastOnce())
            ->method('getBody')
            ->willReturn([]);

        $this->avalexClientMock
            ->expects(self::atLeastOnce())
            ->method('processRequest')
            ->with(self::isInstanceOf(GetDomainLanguagesRequest::class))
            ->willReturn($avalexResponseMock);

        $endpoint = new ImpressumRequest();
        $this->subject->addLanguageToEndpoint($endpoint);

        self::assertSame(
            'de',
            $endpoint->getParameter('lang')
        );
    }

    /**
     * @test
     */
    public function addLanguageToEndpointWithoutEndpointSetsDefaultLanguageToEndpoint(): void
    {
        $this->setEnvironmentWithLanguage('de');

        /** @var AvalexResponse|MockObject $avalexResponseMock */
        $avalexResponseMock = $this->createMock(AvalexResponse::class);
        $avalexResponseMock
            ->expects(self::atLeastOnce())
            ->method('getBody')
            ->willReturn([
                'de' => [
                    'invalid-endpoint' => 'foo->bar',
                ],
            ]);

        $this->avalexClientMock
            ->expects(self::atLeastOnce())
            ->method('processRequest')
            ->with(self::isInstanceOf(GetDomainLanguagesRequest::class))
            ->willReturn($avalexResponseMock);

        $endpoint = new ImpressumRequest();
        $this->subject->addLanguageToEndpoint($endpoint);

        self::assertSame(
            'de',
            $endpoint->getParameter('lang')
        );
    }

    /**
     * @test
     */
    public function addLanguageToEndpointWithEndpointSetsLanguageToEndpoint(): void
    {
        $this->setEnvironmentWithLanguage('de');

        /** @var AvalexResponse|MockObject $avalexResponseMock */
        $avalexResponseMock = $this->createMock(AvalexResponse::class);
        $avalexResponseMock
            ->expects(self::atLeastOnce())
            ->method('getBody')
            ->willReturn([
                'de' => [
                    'impressum' => 'TYPO3 works',
                ],
            ]);

        $this->avalexClientMock
            ->expects(self::atLeastOnce())
            ->method('processRequest')
            ->with(self::isInstanceOf(GetDomainLanguagesRequest::class))
            ->willReturn($avalexResponseMock);

        $endpoint = new ImpressumRequest();
        $this->subject->addLanguageToEndpoint($endpoint);

        self::assertSame(
            'de',
            $endpoint->getParameter('lang')
        );
    }

    /**
     * @test
     */
    public function addLanguageToEndpointWithMultipleEndpointsSetsLanguageToEndpoint(): void
    {
        $this->setEnvironmentWithLanguage('en');

        /** @var AvalexResponse|MockObject $avalexResponseMock */
        $avalexResponseMock = $this->createMock(AvalexResponse::class);
        $avalexResponseMock
            ->expects(self::atLeastOnce())
            ->method('getBody')
            ->willReturn([
                'de' => [
                    'impressum' => 'TYPO3 klappt',
                ],
                'en' => [
                    'impressum' => 'TYPO3 works',
                ],
            ]);

        $this->avalexClientMock
            ->expects(self::atLeastOnce())
            ->method('processRequest')
            ->with(self::isInstanceOf(GetDomainLanguagesRequest::class))
            ->willReturn($avalexResponseMock);

        $endpoint = new ImpressumRequest();
        $this->subject->addLanguageToEndpoint($endpoint);

        self::assertSame(
            'en',
            $endpoint->getParameter('lang')
        );
    }

    public function languageDataProvider(): array
    {
        return [
            'language empty. Fallback to en' => ['', 'en'],
            'language de' => ['de', 'de'],
            'language en' => ['en', 'en'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider languageDataProvider
     */
    public function getFrontendLocaleReturnsDefaultLanguage(string $language, string $expected): void
    {
        $this->setEnvironmentWithLanguage($language);

        self::assertSame(
            $expected,
            $this->subject->getFrontendLocale()
        );
    }
}
