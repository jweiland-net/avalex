<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Hook;

use JWeiland\Avalex\Client\AvalexClient;
use JWeiland\Avalex\Client\Request\Endpoint\ImpressumRequest;
use JWeiland\Avalex\Client\Request\Endpoint\IsApiKeyConfiguredRequest;
use JWeiland\Avalex\Client\Response\AvalexResponse;
use JWeiland\Avalex\Hook\DataHandlerHook;
use JWeiland\Avalex\Service\ApiService;
use JWeiland\Avalex\Service\LanguageService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class DataHandlerHookTest extends FunctionalTestCase
{
    protected AvalexClient|MockObject $avalexClientMock;

    protected FlashMessageQueue|MockObject $flashMessageQueueMock;

    protected DataHandlerHook $subject;

    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/avalex',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->avalexClientMock = $this->createMock(AvalexClient::class);
        $this->flashMessageQueueMock = $this->createMock(FlashMessageQueue::class);

        $this->subject = new DataHandlerHook(
            $this->avalexClientMock,
            $this->flashMessageQueueMock,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->avalexClientMock,
            $this->flashMessageQueueMock,
            $this->subject,
        );
    }

    #[Test]
    public function processWithEmptyAvalexConfigurationWillNotEnqueueFlashMessage(): void
    {
        $this->flashMessageQueueMock
            ->expects($this->never())
            ->method('enqueue');

        /** @var DataHandler $dataHandler */
        $dataHandler = $this->get(DataHandler::class);
        $dataHandler->datamap = [];

        $this->subject->processDatamap_afterAllOperations($dataHandler);
    }

    #[Test]
    public function processWithRequestErrorWillEnqueueErrorFlashMessage(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);

        $this->flashMessageQueueMock
            ->expects($this->atLeastOnce())
            ->method('enqueue')
            ->with(self::callback(static function (FlashMessage $flashMessage): bool {
                return $flashMessage->getSeverity() === ContextualFeedbackSeverity::ERROR;
            }));

        $this->avalexClientMock
            ->expects($this->atLeastOnce())
            ->method('processRequest')
            ->with(
                new IsApiKeyConfiguredRequest('avalex'),
                $GLOBALS['TYPO3_REQUEST'],
            )
            ->willReturn(new AvalexResponse(
                '{}',
                [],
                500,
                true,
                'Avalex Response Error',
            ));

        /** @var DataHandler $dataHandler */
        $dataHandler = $this->get(DataHandler::class);
        $dataHandler->datamap = [
            'tx_avalex_configuration' => [
                0 => [
                    'api_key' => 'avalex',
                ],
            ],
        ];

        $this->subject->processDatamap_afterAllOperations($dataHandler);
    }

    #[Test]
    public function processWithMissingApiKeyWillNotEnqueueFlashMessage(): void
    {
        $this->flashMessageQueueMock
            ->expects($this->never())
            ->method('enqueue');

        /** @var DataHandler $dataHandler */
        $dataHandler = $this->get(DataHandler::class);
        $dataHandler->datamap = [
            'tx_avalex_configuration' => [
                0 => [
                    'foo' => 'bar',
                ],
            ],
        ];

        $this->subject->processDatamap_afterAllOperations($dataHandler);
    }

    #[Test]
    public function processWithInvalidApiKeyWillEnqueueErrorFlashMessage(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);

        $this->flashMessageQueueMock
            ->expects($this->atLeastOnce())
            ->method('enqueue')
            ->with(self::callback(static function (FlashMessage $flashMessage): bool {
                return $flashMessage->getSeverity() === ContextualFeedbackSeverity::ERROR;
            }));

        $this->avalexClientMock
            ->expects($this->atLeastOnce())
            ->method('processRequest')
            ->with(
                new IsApiKeyConfiguredRequest('avalex'),
                $GLOBALS['TYPO3_REQUEST'],
            )
            ->willReturn(new AvalexResponse(
                '{"message":"Invalid API Key"}',
                [],
                400,
                true,
                '',
            ));

        /** @var DataHandler $dataHandler */
        $dataHandler = $this->get(DataHandler::class);
        $dataHandler->datamap = [
            'tx_avalex_configuration' => [
                0 => [
                    'api_key' => 'avalex',
                ],
            ],
        ];

        $this->subject->processDatamap_afterAllOperations($dataHandler);
    }

    #[Test]
    public function processWithValidApiKeyWillEnqueueOkFlashMessage(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);

        $this->flashMessageQueueMock
            ->expects($this->atLeastOnce())
            ->method('enqueue')
            ->with(self::callback(static function (FlashMessage $flashMessage): bool {
                return $flashMessage->getSeverity() === ContextualFeedbackSeverity::OK;
            }));

        $this->avalexClientMock
            ->expects($this->atLeastOnce())
            ->method('processRequest')
            ->with(
                new IsApiKeyConfiguredRequest('avalex'),
                $GLOBALS['TYPO3_REQUEST'],
            )
            ->willReturn(new AvalexResponse(
                '{"message":"OK","result":"example.com"}',
                [],
                200,
                true,
                '',
            ));

        /** @var DataHandler $dataHandler */
        $dataHandler = $this->get(DataHandler::class);
        $dataHandler->datamap = [
            'tx_avalex_configuration' => [
                0 => [
                    'api_key' => 'avalex',
                ],
            ],
        ];

        $this->subject->processDatamap_afterAllOperations($dataHandler);
    }
}
