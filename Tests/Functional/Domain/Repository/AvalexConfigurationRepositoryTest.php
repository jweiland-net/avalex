<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Domain\Repository;

use JWeiland\Avalex\Domain\Repository\AvalexConfigurationRepository;
use JWeiland\Avalex\Domain\Repository\Exception\NoAvalexConfigurationException;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class AvalexConfigurationRepositoryTest extends FunctionalTestCase
{
    protected AvalexConfigurationRepository $subject;

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

        $site = new Site('main', 1, []);
        $routing = new PageArguments(12, '', []);

        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest())
            ->withAttribute('site', $site)
            ->withAttribute('routing', $routing)
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);

        $this->subject = new AvalexConfigurationRepository(
            $this->getConnectionPool()->getQueryBuilderForTable('tx_avalex_configuration'),
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
        $this->expectException(NoAvalexConfigurationException::class);

        // We have to delete the configuration which is configured as "global"
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_avalex_configuration');
        $connection->delete(
            'tx_avalex_configuration',
            [
                'uid' => 1,
            ],
        );

        $request = (new ServerRequest('https://example.com/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $request = $request->withAttribute('normalizedParams', NormalizedParams::createFromServerParams($request->getServerParams()));

        $this->subject->findByRootPageUid(12, $request);
    }

    #[Test]
    public function findByWebsiteRootWithConfigurationWillReturnConfiguration(): void
    {
        $request = (new ServerRequest('https://example.com/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $request = $request->withAttribute('normalizedParams', NormalizedParams::createFromServerParams($request->getServerParams()));

        $avalexConfiguration = $this->subject->findByRootPageUid(25, $request);

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
        $request = (new ServerRequest('https://example.com/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $request = $request->withAttribute('normalizedParams', NormalizedParams::createFromServerParams($request->getServerParams()));

        $avalexConfiguration = $this->subject->findByRootPageUid(414, $request);

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
