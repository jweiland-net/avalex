<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests;

use JWeiland\Avalex\AvalexPlugin;
use JWeiland\Avalex\Client\AvalexClient;
use JWeiland\Avalex\Client\Request\GetDomainLanguagesRequest;
use JWeiland\Avalex\Client\Request\ImpressumRequest;
use JWeiland\Avalex\Client\Response\AvalexResponse;
use JWeiland\Avalex\Service\ApiService;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use PHPUnit\Framework\Constraint\StringContains;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test the main functionality of this extension: the output of legal texts
 */
class AvalexPluginTest extends FunctionalTestCase
{
    use ProphecyTrait;

    /**
     * @var ImpressumRequest
     */
    protected $impressumRequest;

    /**
     * @var ApiService|ObjectProphecy
     */
    protected $apiServiceProphecy;

    /**
     * @var AvalexClient|ObjectProphecy
     */
    protected $avalexClientProphecy;

    /**
     * @var AvalexPlugin
     */
    protected $subject;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/avalex'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet('ntf://Database/pages.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/tx_avalex_configuration.xml');

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
    public function processLinksEncryptsMailToLinks()
    {
        $avalexResponse = new AvalexResponse('{"de": {"avx-impressum": ""}}');
        $avalexResponse->setIsJsonResponse(true);

        $this->avalexClientProphecy
            ->processRequest(Argument::type(GetDomainLanguagesRequest::class))
            ->shouldBeCalled()
            ->willReturn($avalexResponse);

        $this->apiServiceProphecy
            ->getHtmlForCurrentRootPage(
                Argument::type(ImpressumRequest::class),
                [
                    'uid' => 1,
                    'api_key' => 'demo-key-with-online-shop',
                    'domain' => 'https://example.com',
                ]
            )
            ->shouldBeCalled()
            ->willReturn(
                '<p>Do not upgrade this text without modifying the tests in AvalexPluginTest.php! <a href="mailto:john@doe.tld">johns mail</a></p>'
            );

        $encryptedMail = $this->subject->cObj->getMailTo('john@doe.tld', 'johns mail');
        if (count($encryptedMail) === 3) {
            // TYPO3 >= 11
            $attributes = GeneralUtility::implodeAttributes($encryptedMail[2], true);
            $expected = "<a href=\"$encryptedMail[0]\" $attributes>$encryptedMail[1]</a>";
        } else {
            $expected = "<a href=\"$encryptedMail[0]\">$encryptedMail[1]</a>";
        }

        static::assertThat(
            $this->subject->render(null, ['endpoint' => 'avx-impressum']),
            new StringContains($expected)
        );
    }

    /**
     * @test
     */
    public function processLinksAddRequestUrlToAnchors(): void
    {
        $avalexResponse = new AvalexResponse('{"de": {"avx-impressum": ""}}');
        $avalexResponse->setIsJsonResponse(true);

        $this->avalexClientProphecy
            ->processRequest(Argument::type(GetDomainLanguagesRequest::class))
            ->shouldBeCalled()
            ->willReturn($avalexResponse);

        $this->apiServiceProphecy
            ->getHtmlForCurrentRootPage(
                Argument::type(ImpressumRequest::class),
                [
                    'uid' => 1,
                    'api_key' => 'demo-key-with-online-shop',
                    'domain' => 'https://example.com',
                ]
            )
            ->shouldBeCalled()
            ->willReturn(
                '<p>Do not upgrade this text without modifying the tests in AvalexPluginTest.php! <a href="#hello">Hello World</a>.</p>' . chr(10)
                . '<p>Want another link? OK: <a href="#world">Another one</a>. <a href="/test.html">Do not replace this</a> ok?</p>' . chr(10)
                . '<p>And also do <a href="https://domain.tld">not replace this</a>.</p>'
            );

        $requestUri = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
        $expected = [];
        $expected[] = '<p>Do not upgrade this text without modifying the tests in AvalexPluginTest.php! <a href="$requestUri#hello">Hello World</a>.</p>';
        $expected[] = '<p>Want another link? OK: <a href="$requestUri#world">Another one</a>. <a href="/test.html">Do not replace this</a> ok?</p>';
        $expected[] = '<p>And also do <a href="https://domain.tld">not replace this</a>.</p>';

        self::assertEquals(
            str_replace('$requestUri', $requestUri, implode(chr(10), $expected)),
            $this->subject->render(null, ['endpoint' => 'avx-impressum'])
        );
    }
}
