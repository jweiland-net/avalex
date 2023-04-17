<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Domain\Repository;

use JWeiland\Avalex\Domain\Repository\AvalexConfigurationRepository;
use JWeiland\Avalex\Exception\AvalexConfigurationNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class AvalexConfigurationRepositoryTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    protected AvalexConfigurationRepository $subject;

    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/avalex',
    ];

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

        $this->subject = new AvalexConfigurationRepository();
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
    public function findAllWillFindAllConfigurationRecords(): void
    {
        $allConfigurationRecords = $this->subject->findAll();

        self::assertCount(
            2,
            $allConfigurationRecords
        );

        $firstConfigurationRecord = current($allConfigurationRecords);
        self::assertSame(
            'demo-key-with-online-shop',
            $firstConfigurationRecord['api_key']
        );
        self::assertSame(
            'https://example.com',
            $firstConfigurationRecord['domain']
        );
    }

    /**
     * @test
     */
    public function findByWebsiteRootWithNoConfigurationWillThrowException(): void
    {
        $this->expectException(AvalexConfigurationNotFoundException::class);

        // We have to delete the configuration which is configured as "global"
        $this->getDatabaseConnection()->delete(
            'tx_avalex_configuration',
            [
                'uid' => 1,
            ]
        );

        $this->subject->findByWebsiteRoot(12);
    }

    /**
     * @test
     */
    public function findByWebsiteRootWithConfigurationWillReturnConfiguration(): void
    {
        $configurationRecord = $this->subject->findByWebsiteRoot(25);
        self::assertSame(
            'invalid-key',
            $configurationRecord['api_key']
        );
        self::assertSame(
            'https://jweiland.net',
            $configurationRecord['domain']
        );
    }

    /**
     * @test
     */
    public function findByWebsiteRootWithoutConfigurationWillReturnFallbackConfiguration(): void
    {
        $configurationRecord = $this->subject->findByWebsiteRoot(414);
        self::assertSame(
            'demo-key-with-online-shop',
            $configurationRecord['api_key']
        );
        self::assertSame(
            'https://example.com',
            $configurationRecord['domain']
        );
    }
}
