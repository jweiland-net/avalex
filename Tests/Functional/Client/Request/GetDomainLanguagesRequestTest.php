<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Client\Request;

use JWeiland\Avalex\Client\Request\GetDomainLanguagesRequest;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test GetDomainLanguagesRequest
 */
class GetDomainLanguagesRequestTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/avalex'
    ];

    /**
     * @var GetDomainLanguagesRequest
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

        $this->subject = new GetDomainLanguagesRequest();
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
            'avx-get-domain-langs',
            $this->subject->getEndpoint()
        );
    }

    /**
     * @test
     */
    public function getEndpointWithoutPrefixReturnsEndpointWithoutPrefix(): void
    {
        self::assertSame(
            'get-domain-langs',
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
                'domain' => 'https://example.com',
                'version' => '3.0.1',
            ],
            $this->subject->getParameters()
        );
    }

    /**
     * @test
     */
    public function getParametersWithDomainReturnsParametersWithDomain(): void
    {
        $this->subject->setDomain('https://www.jweiland.net');
        self::assertSame(
            [
                'domain' => 'https://www.jweiland.net',
                'apikey' => 'demo-key-with-online-shop',
                'version' => '3.0.1',
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
                'domain' => 'https://example.com',
                'version' => '3.0.1',
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
            'domain' => 'https://www.jweiland.net',
        ]);
        self::assertSame(
            [
                'domain' => 'https://www.jweiland.net',
                'apikey' => 'demo-key-with-online-shop',
                'version' => '3.0.1',
            ],
            $this->subject->getParameters()
        );
    }
}
