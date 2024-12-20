<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional;

use JWeiland\Avalex\AvalexPlugin;
use JWeiland\Avalex\Client\AvalexClient;
use JWeiland\Avalex\Client\Request\GetDomainLanguagesRequest;
use JWeiland\Avalex\Client\Request\ImpressumRequest;
use JWeiland\Avalex\Client\Response\AvalexResponse;
use JWeiland\Avalex\Service\ApiService;
use JWeiland\Avalex\Utility\Typo3Utility;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Typolink\EmailLinkBuilder;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class AvalexPluginTest extends FunctionalTestCase
{
    protected ImpressumRequest $impressumRequest;

    /**
     * @var ApiService|MockObject
     */
    protected $apiServiceMock;

    /**
     * @var AvalexClient|MockObject
     */
    protected $avalexClientMock;

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

        // Set is_siteroot to 1
        $this->setUpFrontendRootPage(1);

        /** @var TypoScriptFrontendController|MockObject|AccessibleObjectInterface $typoScriptFrontendController */
        $typoScriptFrontendController = $this->getAccessibleMock(TypoScriptFrontendController::class, [], [], '', false);
        $GLOBALS['TSFE'] = $typoScriptFrontendController;
        $GLOBALS['TSFE']->id = 1;
        $GLOBALS['TSFE']->_set('spamProtectEmailAddresses', 1);

        $this->impressumRequest = new ImpressumRequest();
        $this->apiServiceMock = $this->createMock(ApiService::class);
        GeneralUtility::addInstance(ApiService::class, $this->apiServiceMock);
        $this->avalexClientMock = $this->createMock(AvalexClient::class);
        GeneralUtility::addInstance(AvalexClient::class, $this->avalexClientMock);

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
    public function processLinksEncryptsMailToLinks(): void
    {
        $avalexResponse = new AvalexResponse('{"de": {"avx-impressum": ""}}');
        $avalexResponse->setIsJsonResponse(true);

        $this->avalexClientMock
            ->expects(self::atLeastOnce())
            ->method('processRequest')
            ->with(self::isInstanceOf(GetDomainLanguagesRequest::class))
            ->willReturn($avalexResponse);

        $this->apiServiceMock
            ->expects(self::atLeastOnce())
            ->method('getHtmlForCurrentRootPage')
            ->with(
                self::isInstanceOf(ImpressumRequest::class),
                self::equalTo(
                    [
                        'uid' => 1,
                        'api_key' => 'demo-key-with-online-shop',
                        'domain' => 'https://example.com',
                    ]
                )
            )
            ->willReturn(
                '<p>Do not upgrade this text without modifying the tests in AvalexPluginTest.php! '
                . '<a href="mailto:john@doe.tld">johns mail</a>'
                . '</p>'
            );

        $encryptedMail = call_user_func_array($this->getEncryptedMailCallable(), ['john@doe.tld', 'johns mail']);
        if (count($encryptedMail) === 3) {
            // TYPO3 >= 11
            $expected = sprintf(
                '<a href="%s" %s>%s</a>',
                $encryptedMail[0],
                GeneralUtility::implodeAttributes($encryptedMail[2], true),
                $encryptedMail[1]
            );
        } else {
            $expected = sprintf(
                '<a href="%s">%s</a>',
                $encryptedMail[0],
                $encryptedMail[1]
            );
        }

        self::assertThat(
            $this->subject->render('', ['endpoint' => 'avx-impressum']),
            new StringContains($expected)
        );
    }

    protected function getEncryptedMailCallable(): callable
    {
        $cObj = $this->subject->cObj;

        return static function ($mailAddress, $linkText) use ($cObj) {
            $linkBuilder = GeneralUtility::makeInstance(EmailLinkBuilder::class, $cObj, $GLOBALS['TSFE']);
            return $linkBuilder->processEmailLink((string)$mailAddress, (string)$linkText);
        };
    }

    /**
     * @test
     */
    public function processLinksAddRequestUrlToAnchors(): void
    {
        $avalexResponse = new AvalexResponse('{"de": {"avx-impressum": ""}}');
        $avalexResponse->setIsJsonResponse(true);

        $this->avalexClientMock
            ->expects(self::atLeastOnce())
            ->method('processRequest')
            ->with(self::isInstanceOf(GetDomainLanguagesRequest::class))
            ->willReturn($avalexResponse);

        $this->apiServiceMock
            ->expects(self::atLeastOnce())
            ->method('getHtmlForCurrentRootPage')
            ->with(
                self::isInstanceOf(ImpressumRequest::class),
                self::equalTo(
                    [
                        'uid' => 1,
                        'api_key' => 'demo-key-with-online-shop',
                        'domain' => 'https://example.com',
                    ]
                )
            )
            ->willReturn(
                '<p>Do not upgrade this text without modifying the tests in AvalexPluginTest.php! '
                . '<a href="#hello">Hello World</a>.</p>' . chr(10)
                . '<p>Want another link? OK: <a href="#world">Another one</a>. '
                . '<a href="/test.html">Do not replace this</a> ok?</p>' . chr(10)
                . '<p>And also do <a href="https://domain.tld">not replace this</a>.</p>'
            );

        $requestUri = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
        $expected = [];
        $expected[] = '<p>Do not upgrade this text without modifying the tests in AvalexPluginTest.php! '
            . '<a href="$requestUri#hello">Hello World</a>.</p>';
        $expected[] = '<p>Want another link? OK: <a href="$requestUri#world">Another one</a>. '
            . '<a href="/test.html">Do not replace this</a> ok?</p>';
        $expected[] = '<p>And also do <a href="https://domain.tld">not replace this</a>.</p>';

        self::assertEquals(
            str_replace('$requestUri', $requestUri, implode(chr(10), $expected)),
            $this->subject->render('', ['endpoint' => 'avx-impressum'])
        );
    }
}
