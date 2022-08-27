<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests;

use JWeiland\Avalex\AvalexPlugin;
use JWeiland\Avalex\Client\Request\GetDomainLanguagesRequest;
use JWeiland\Avalex\Utility\AvalexUtility;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use PHPUnit\Framework\Constraint\StringContains;
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

    protected $testExtensionsToLoad = [
        'typo3conf/ext/avalex'
    ];

    /**
     * @var AvalexPlugin
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importDataSet('ntf://Database/pages.xml');
        $this->importDataSet(__DIR__ . '/../../Fixtures/tx_avalex_configuration.xml');

        // Set is_siteroot to 1
        parent::setUpFrontendRootPage(1);

        /** @var TypoScriptFrontendController|ObjectProphecy $typoScriptFrontendController */
        $typoScriptFrontendController = $this->prophesize(TypoScriptFrontendController::class);
        $GLOBALS['TSFE'] = $typoScriptFrontendController->reveal();
        $GLOBALS['TSFE']->id = 1;
        $GLOBALS['TSFE']->spamProtectEmailAddresses = 1;

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
        AvalexUtility::setApiUrl('file://' . __DIR__ . '/Fixtures/Requests/EncryptMailTo/');
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
        AvalexUtility::setApiUrl('file://' . __DIR__ . '/Fixtures/Requests/AddRequestUrlToAnchors/');
        $requestUri = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
        static::assertEquals(
            <<<HTML
<p>Do not upgrade this text without modifying the tests in AvalexPluginTest.php! <a href="$requestUri#hello">Hello World</a>.</p>
<p>Want another link? OK: <a href="$requestUri#world">Another one</a>. <a href="/test.html">Do not replace this</a> ok?</p>
<p>And also do <a href="https://domain.tld">not replace this</a>.</p>\n
HTML
            ,
            $this->subject->render(null, ['endpoint' => 'avx-impressum'])
        );
    }
}
