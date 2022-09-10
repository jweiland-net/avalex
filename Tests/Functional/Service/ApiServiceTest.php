<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Service;


use JWeiland\Avalex\Client\AvalexClient;
use JWeiland\Avalex\Client\Request\ImpressumRequest;
use JWeiland\Avalex\Client\Response\AvalexResponse;
use JWeiland\Avalex\Service\ApiService;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 */
class ApiServiceTest extends FunctionalTestCase
{
    /**
     * @var AvalexClient|ObjectProphecy
     */
    protected $avalexClientProphecy;

    /**
     * @var ApiService
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

        $this->avalexClientProphecy = $this->prophesize(AvalexClient::class);
        GeneralUtility::addInstance(AvalexClient::class, $this->avalexClientProphecy->reveal());

        $this->subject = new ApiService();
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
    public function addLanguageToEndpointWithoutResponseSetsDefaultLanguageToEndpoint(): void
    {
        /** @var AvalexResponse|ObjectProphecy $avalexResponseProphecy */
        $avalexResponseProphecy = $this->prophesize(AvalexResponse::class);
        $avalexResponseProphecy
            ->getBody()
            ->shouldBeCalled()
            ->willReturn('german text');

        $this->avalexClientProphecy
            ->processRequest(Argument::type(ImpressumRequest::class))
            ->shouldBeCalled()
            ->willReturn($avalexResponseProphecy->reveal());

        $endpoint = new ImpressumRequest();

        self::assertSame(
            'german text',
            $this->subject->getHtmlForCurrentRootPage($endpoint, [])
        );
    }
}
