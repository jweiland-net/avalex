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
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Test the main functionality of this extension: the output of legal texts
 */
class AvalexPluginTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['avalex'];

    /**
     * @var AvalexPlugin
     */
    protected $avalexPlugin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->avalexPlugin = new AvalexPlugin();
        $this->avalexPlugin->cObj = new ContentObjectRenderer();
        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->id = 1;

        $this->importDataSet(__DIR__ . '/Fixtures/tx_avalex_configuration.xml');
    }

    public function renderEndpointProvider(): array
    {
        return [
            ['avx-datenschutzerklaerung', 'de', 'Weitere Einzelheiten zur verantwortlichen Stelle', 'Fetch datenschutzerklaerung in german'],
            ['avx-datenschutzerklaerung', 'abcde', 'Weitere Einzelheiten zur verantwortlichen Stelle', 'Fetch datenschutzerklaerung in german because language is invalid'],
            ['avx-datenschutzerklaerung', 'en', 'In the following, we inform you about the collection of personal data when using our website', 'Fetch datenschutzerklaerung in english'],
            ['avx-impressum', 'de', 'Geschäftsführer', 'Fetch impressum in german'],
            ['avx-impressum', 'abcde', 'Geschäftsführer', 'Fetch impressum in german because language is invalid'],
            ['avx-impressum', 'en', 'Managing Director', 'Fetch impressum in english'],
            ['avx-bedingungen', 'de', 'Die nachfolgenden Allgemeinen Geschäftsbedingungen', 'Fetch bedingungen in german'],
            ['avx-bedingungen', 'abcde', 'Die nachfolgenden Allgemeinen Geschäftsbedingungen', 'Fetch bedingungen in german because language is invalid'],
            ['avx-bedingungen', 'en', 'The following General Terms and Conditions', 'Fetch bedingungen in english'],
            ['avx-widerruf', 'de', 'Widerrufsrecht bei Kaufverträgen', 'Fetch widerruf in german'],
            ['avx-widerruf', 'abcde', 'Widerrufsrecht bei Kaufverträgen', 'Fetch widerruf in german because language is invalid'],
            ['avx-widerruf', 'en', 'Right of withdrawal for sales contracts', 'Fetch widerruf in english']
        ];
    }

    /**
     * @test
     * @dataProvider renderEndpointProvider
     */
    public function renderEndpoint($endpoint, $language, $expected, $message): void
    {
        AvalexUtility::setFrontendLocale($language);
        $this->assertSame(
            $expected,
            $this->avalexPlugin->render(null, ['endpoint' => $endpoint]),
            $message
        );
    }
}
