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
use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use JWeiland\Avalex\Service\ApiService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class ApiServiceTest extends FunctionalTestCase
{
    protected AvalexClient|MockObject $avalexClientMock;

    protected ApiService $subject;

    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/avalex',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/tx_avalex_configuration.csv');

        // Set is_siteroot to 1
        $this->setUpFrontendRootPage(1);

        $this->avalexClientMock = $this->createMock(AvalexClient::class);
        GeneralUtility::addInstance(AvalexClient::class, $this->avalexClientMock);

        $this->subject = new ApiService(
            $this->avalexClientMock,
            $this->getContainer()->get(EventDispatcherInterface::class),
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
        );
    }

    #[Test]
    public function addLanguageToEndpointWithoutResponseSetsDefaultLanguageToEndpoint(): void
    {
        /** @var AvalexResponse|MockObject $avalexResponseMock */
        $avalexResponseMock = $this->createMock(AvalexResponse::class);
        $avalexResponseMock
            ->expects(self::atLeastOnce())
            ->method('getBody')
            ->willReturn('german text');

        $this->avalexClientMock
            ->expects(self::atLeastOnce())
            ->method('processRequest')
            ->with(self::isInstanceOf(ImpressumRequest::class))
            ->willReturn($avalexResponseMock);

        $endpoint = new ImpressumRequest();
        $endpoint->setAvalexConfiguration(new AvalexConfiguration(
            1,
            'demo-key-with-online-shop',
            'https://example.com',
            '',
        ));

        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObjectRenderer->setRequest(new ServerRequest());

        self::assertSame(
            'german text',
            $this->subject->getHtmlContentFromEndpoint(
                $endpoint,
                $contentObjectRenderer,
            ),
        );
    }
}
