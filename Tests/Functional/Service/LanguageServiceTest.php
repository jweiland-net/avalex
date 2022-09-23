<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Service;

use JWeiland\Avalex\AvalexPlugin;
use JWeiland\Avalex\Client\AvalexClient;
use JWeiland\Avalex\Client\Request\DatenschutzerklaerungRequest;
use JWeiland\Avalex\Client\Request\GetDomainLanguagesRequest;
use JWeiland\Avalex\Client\Request\ImpressumRequest;
use JWeiland\Avalex\Client\Response\AvalexResponse;
use JWeiland\Avalex\Service\ApiService;
use JWeiland\Avalex\Service\LanguageService;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use PHPUnit\Framework\Constraint\StringContains;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 */
class LanguageServiceTest extends FunctionalTestCase
{
    /**
     * @var LanguageService
     */
    protected $subject;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/avalex'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet('ntf://Database/pages.xml');
        $this->importDataSet(__DIR__ . '/../Fixtures/tx_avalex_configuration.xml');

        // Set is_siteroot to 1
        parent::setUpFrontendRootPage(1);

        /** @var TypoScriptFrontendController|ObjectProphecy $typoScriptFrontendController */
        $typoScriptFrontendController = $this->prophesize(TypoScriptFrontendController::class);
        $GLOBALS['TSFE'] = $typoScriptFrontendController->reveal();
        $GLOBALS['TSFE']->id = 1;
        $GLOBALS['TSFE']->spamProtectEmailAddresses = 1;

        $this->impressumRequest = new ImpressumRequest();
        $this->apiServiceProphecy = $this->prophesize(ApiService::class);
        GeneralUtility::addInstance(ApiService::class, $this->apiServiceProphecy->reveal());
        $this->avalexClientProphecy = $this->prophesize(AvalexClient::class);
        GeneralUtility::addInstance(AvalexClient::class, $this->avalexClientProphecy->reveal());

        $this->subject = new AvalexPlugin();
        $this->subject->cObj = new ContentObjectRenderer();
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $GLOBALS['TSFE']
        );
    }

    /**
     * @test
     */
    public function addLanguageToEndpointAddsDefaultLanguage(): void
    {
        $endpoint = new DatenschutzerklaerungRequest();

        $this->subject = new LanguageService([]);
        $this->subject->addLanguageToEndpoint($endpoint);
    }
}
