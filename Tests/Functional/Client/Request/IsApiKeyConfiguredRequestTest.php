<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Client\Request;

use JWeiland\Avalex\Client\Request\IsApiKeyConfiguredRequest;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class IsApiKeyConfiguredRequestTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/avalex',
    ];

    protected IsApiKeyConfiguredRequest $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tx_avalex_configuration.csv');

        // Set is_siteroot to 1
        $this->setUpFrontendRootPage(1);

        /** @var TypoScriptFrontendController|MockObject|AccessibleObjectInterface $typoScriptFrontendController */
        $typoScriptFrontendController = $this->getAccessibleMock(TypoScriptFrontendController::class);
        $GLOBALS['TSFE'] = $typoScriptFrontendController;
        $GLOBALS['TSFE']->id = 1;
        $GLOBALS['TSFE']->_set('spamProtectEmailAddresses', 1);

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
