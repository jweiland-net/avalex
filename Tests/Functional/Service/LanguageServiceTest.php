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
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 */
class LanguageServiceTest extends FunctionalTestCase
{
    /**
     * @var AvalexClient|ObjectProphecy
     */
    protected $avalexClientProphecy;

    /**
     * @var LanguageService
     */
    protected $subject;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/avalex',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet('ntf://Database/pages.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/tx_avalex_configuration.xml');

        // Set is_siteroot to 1
        parent::setUpFrontendRootPage(1);

        $this->avalexClientProphecy = $this->prophesize(AvalexClient::class);
        GeneralUtility::addInstance(AvalexClient::class, $this->avalexClientProphecy->reveal());

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

        /** @var TypoScriptFrontendController|ObjectProphecy $typoScriptFrontendController */
        $typoScriptFrontendController = $this->prophesize(TypoScriptFrontendController::class);
        $GLOBALS['TSFE'] = $typoScriptFrontendController->reveal();
        $GLOBALS['TSFE']->id = 1;
        $GLOBALS['TSFE']->lang = $language ?: 'en';
        $GLOBALS['TSFE']->spamProtectEmailAddresses = 1;
    }

    /**
     * @test
     */
    public function addLanguageToEndpointWithoutResponseSetsDefaultLanguageToEndpoint(): void
    {
        $this->setEnvironmentWithLanguage('');

        /** @var AvalexResponse|ObjectProphecy $avalexResponseProphecy */
        $avalexResponseProphecy = $this->prophesize(AvalexResponse::class);
        $avalexResponseProphecy
            ->getBody()
            ->shouldBeCalled()
            ->willReturn([]);

        $this->avalexClientProphecy
            ->processRequest(Argument::type(GetDomainLanguagesRequest::class))
            ->shouldBeCalled()
            ->willReturn($avalexResponseProphecy->reveal());

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

        /** @var AvalexResponse|ObjectProphecy $avalexResponseProphecy */
        $avalexResponseProphecy = $this->prophesize(AvalexResponse::class);
        $avalexResponseProphecy
            ->getBody()
            ->shouldBeCalled()
            ->willReturn([
                'de' => [
                    'invalid-endpoint' => 'foo->bar',
                ],
            ]);

        $this->avalexClientProphecy
            ->processRequest(Argument::type(GetDomainLanguagesRequest::class))
            ->shouldBeCalled()
            ->willReturn($avalexResponseProphecy->reveal());

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

        /** @var AvalexResponse|ObjectProphecy $avalexResponseProphecy */
        $avalexResponseProphecy = $this->prophesize(AvalexResponse::class);
        $avalexResponseProphecy
            ->getBody()
            ->shouldBeCalled()
            ->willReturn([
                'de' => [
                    'impressum' => 'TYPO3 works',
                ],
            ]);

        $this->avalexClientProphecy
            ->processRequest(Argument::type(GetDomainLanguagesRequest::class))
            ->shouldBeCalled()
            ->willReturn($avalexResponseProphecy->reveal());

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

        /** @var AvalexResponse|ObjectProphecy $avalexResponseProphecy */
        $avalexResponseProphecy = $this->prophesize(AvalexResponse::class);
        $avalexResponseProphecy
            ->getBody()
            ->shouldBeCalled()
            ->willReturn([
                'de' => [
                    'impressum' => 'TYPO3 klappt',
                ],
                'en' => [
                    'impressum' => 'TYPO3 works',
                ],
            ]);

        $this->avalexClientProphecy
            ->processRequest(Argument::type(GetDomainLanguagesRequest::class))
            ->shouldBeCalled()
            ->willReturn($avalexResponseProphecy->reveal());

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
