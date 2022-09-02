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
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 */
class AvalexConfigurationRepositoryTest extends FunctionalTestCase
{
    /**
     * @var AvalexConfigurationRepository
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
        $this->importDataSet(__DIR__ . '/../../Fixtures/tx_avalex_configuration.xml');

        // Set is_siteroot to 1
        parent::setUpFrontendRootPage(1);

        /** @var TypoScriptFrontendController|ObjectProphecy $typoScriptFrontendController */
        $typoScriptFrontendController = $this->prophesize(TypoScriptFrontendController::class);
        $GLOBALS['TSFE'] = $typoScriptFrontendController->reveal();
        $GLOBALS['TSFE']->id = 1;
        $GLOBALS['TSFE']->spamProtectEmailAddresses = 1;

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
                'uid' => 1
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
