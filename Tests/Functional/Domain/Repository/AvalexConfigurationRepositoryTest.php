<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Domain\Repository;

use JWeiland\Avalex\Domain\Repository\AvalexConfigurationRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class AvalexConfigurationRepositoryTest extends FunctionalTestCase
{
    protected AvalexConfigurationRepository $subject;

    protected MockObject|LoggerInterface $logger;

    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/avalex',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/tx_avalex_configuration.csv');

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->subject = new AvalexConfigurationRepository(
            $this->getConnectionPool()->getQueryBuilderForTable('tx_avalex_configuration'),
            $this->logger,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
        );
    }

    #[Test]
    public function findByWebsiteRootWithNoConfigurationWillThrowException(): void
    {
        // We have to delete the configuration which is configured as "global"
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_avalex_configuration');
        $connection->delete(
            'tx_avalex_configuration',
            [
                'uid' => 1,
            ],
        );

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with('No Avalex configuration could be found in database for page UID: ' . 12);

        self::assertNull(
            $this->subject->findByRootPageUid(12),
        );
    }

    #[Test]
    public function findByWebsiteRootWithConfigurationWillReturnConfiguration(): void
    {
        $avalexConfiguration = $this->subject->findByRootPageUid(25);

        self::assertSame(
            'invalid-key',
            $avalexConfiguration->getApiKey(),
        );
        self::assertSame(
            'https://jweiland.net',
            $avalexConfiguration->getDomain(),
        );
    }

    #[Test]
    public function findByWebsiteRootWithoutConfigurationWillReturnFallbackConfiguration(): void
    {
        $avalexConfiguration = $this->subject->findByRootPageUid(414);

        self::assertSame(
            'demo-key-with-online-shop',
            $avalexConfiguration->getApiKey(),
        );
        self::assertSame(
            'https://example.com',
            $avalexConfiguration->getDomain(),
        );
    }
}
