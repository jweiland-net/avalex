<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests;

use JWeiland\Avalex\AvalexPlugin;
use JWeiland\Avalex\Utility\AvalexUtility;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use PHPUnit\Framework\Constraint\StringContains;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test the main functionality of this extension: the output of legal texts
 */
class AvalexPluginTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/avalex'];

    /**
     * @var AvalexPlugin
     */
    protected $avalexPlugin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->avalexPlugin = new AvalexPlugin();
        $this->avalexPlugin->cObj = new ContentObjectRenderer();
        $GLOBALS['TSFE'] = $this->createMock(TypoScriptFrontendController::class);
        $GLOBALS['TSFE']->id = 1;
        $GLOBALS['TSFE']->spamProtectEmailAddresses = 1;

        $this->importDataSet('ntf://Database/pages.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/tx_avalex_configuration.xml');
    }

    protected function tearDown(): void
    {
        unset($this->avalexPlugin, $GLOBALS['TSFE']);
    }

    public function renderEndpointProvider(): array
    {
        // TODO: Enable tests for english bedingungen and widerruf as soon as official demo key
        //       contains an english version of them
        return [
            ['avx-datenschutzerklaerung', 'de', 'Weitere Einzelheiten zur verantwortlichen Stelle', 'Fetch datenschutzerklaerung in german'],
            ['avx-datenschutzerklaerung', 'abcde', 'Weitere Einzelheiten zur verantwortlichen Stelle', 'Fetch datenschutzerklaerung in german because language is invalid'],
            ['avx-datenschutzerklaerung', 'en', 'In the following, we inform you about the collection of personal data when using our website', 'Fetch datenschutzerklaerung in english'],
            ['avx-impressum', 'de', 'Wirtschaftsidentifikationsnummer', 'Fetch impressum in german'],
            ['avx-impressum', 'abcde', 'Wirtschaftsidentifikationsnummer', 'Fetch impressum in german because language is invalid'],
            ['avx-impressum', 'en', 'Business identification number', 'Fetch impressum in english'],
            ['avx-bedingungen', 'de', 'Die nachfolgenden Allgemeinen Geschäftsbedingungen', 'Fetch bedingungen in german'],
            ['avx-bedingungen', 'abcde', 'Die nachfolgenden Allgemeinen Geschäftsbedingungen', 'Fetch bedingungen in german because language is invalid'],
            //['avx-bedingungen', 'en', 'The following General Terms and Conditions', 'Fetch bedingungen in english'],
            ['avx-widerruf', 'de', 'Widerrufsrecht bei Kaufverträgen', 'Fetch widerruf in german'],
            ['avx-widerruf', 'abcde', 'Widerrufsrecht bei Kaufverträgen', 'Fetch widerruf in german because language is invalid'],
            //['avx-widerruf', 'en', 'Right of withdrawal for sales contracts', 'Fetch widerruf in english']
        ];
    }

    /**
     * @test
     *
     * @dataProvider renderEndpointProvider
     */
    public function renderEndpoint($endpoint, $language, $expected, $message): void
    {
        AvalexUtility::setApiUrl('https://dev.avalex.de/');
        AvalexUtility::setFrontendLocale($language);

        static::assertThat(
            $this->avalexPlugin->render(null, ['endpoint' => $endpoint]),
            new StringContains($expected),
            $message
        );
    }

    /**
     * @test
     */
    public function processLinksEncryptsMailToLinks()
    {
        AvalexUtility::setApiUrl('file://' . __DIR__ . '/Fixtures/Requests/EncryptMailTo/');
        $encryptedMail = $this->avalexPlugin->cObj->getMailTo('john@doe.tld', 'johns mail');
        if (count($encryptedMail) === 3) {
            // TYPO3 >= 11
            $attributes = GeneralUtility::implodeAttributes($encryptedMail[2], true);
            $expected = "<a href=\"$encryptedMail[0]\" $attributes>$encryptedMail[1]</a>";
        } else {
            $expected = "<a href=\"$encryptedMail[0]\">$encryptedMail[1]</a>";
        }
        static::assertThat(
            $this->avalexPlugin->render(null, ['endpoint' => 'avx-impressum']),
            new StringContains($expected)
        );
    }

    /**
     * @test
     */
    public function processLinksAddRequestUrlToAnchors()
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
            $this->avalexPlugin->render(null, ['endpoint' => 'avx-impressum'])
        );
    }
}
