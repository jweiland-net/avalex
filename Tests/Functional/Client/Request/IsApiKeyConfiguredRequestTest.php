<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Client\Request;

use JWeiland\Avalex\Client\Request\IsApiKeyConfiguredRequest;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test IsApiKeyConfiguredRequest
 */
class IsApiKeyConfiguredRequestTest extends FunctionalTestCase
{
    use ProphecyTrait;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/avalex'
    ];

    /**
     * @var IsApiKeyConfiguredRequest
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


        $this->subject = new IsApiKeyConfiguredRequest();
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
    public function getEndpointReturnsEndpoint(): void
    {
        self::assertSame(
            'api_keys/is_configured.json',
            $this->subject->getEndpoint()
        );
    }

    /**
     * @test
     */
    public function getEndpointWithoutPrefixReturnsEndpointWithoutPrefix(): void
    {
        self::assertSame(
            'keys/is_configured.json',
            $this->subject->getEndpointWithoutPrefix()
        );
    }

    /**
     * @test
     */
    public function getParametersReturnsRequiredParameters(): void
    {
        self::assertSame(
            [
                'apikey' => 'demo-key-with-online-shop',
            ],
            $this->subject->getParameters()
        );
    }

    /**
     * @test
     */
    public function getParametersWithApiKeyReturnsParametersWithApiKey(): void
    {
        $this->subject->setApiKey('API_KEY');
        self::assertSame(
            [
                'apikey' => 'API_KEY',
            ],
            $this->subject->getParameters()
        );
    }

    /**
     * @test
     */
    public function getParametersWithInvalidParametersReturnsRequiredParameters(): void
    {
        $this->subject->addParameter('foo', 'bar');
        self::assertSame(
            [
                'apikey' => 'demo-key-with-online-shop',
            ],
            $this->subject->getParameters()
        );
    }

    /**
     * @test
     */
    public function setParametersWillOnlySetAllowedParameters(): void
    {
        $this->subject->setParameters([
            'foo' => 'bar',
            'lang' => 'en',
            'apikey' => 'API_KEY',
        ]);

        // API KEY will only be set through setApiKey(). setParameters has no effect
        self::assertSame(
            [
                'apikey' => 'demo-key-with-online-shop',
            ],
            $this->subject->getParameters()
        );
    }
}
