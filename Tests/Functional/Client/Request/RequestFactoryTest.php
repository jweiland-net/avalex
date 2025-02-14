<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Client\Request;

use JWeiland\Avalex\Client\Request\Endpoint\BedingungenRequest;
use JWeiland\Avalex\Client\Request\Endpoint\DatenschutzerklaerungRequest;
use JWeiland\Avalex\Client\Request\Endpoint\ImpressumRequest;
use JWeiland\Avalex\Client\Request\Endpoint\WiderrufRequest;
use JWeiland\Avalex\Client\Request\Exception\InvalidAvalexEndpointException;
use JWeiland\Avalex\Client\Request\RequestFactory;
use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use JWeiland\Avalex\Domain\Repository\AvalexConfigurationRepository;
use JWeiland\Avalex\Domain\Repository\Exception\NoAvalexConfigurationException;
use JWeiland\Avalex\Service\LanguageService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class RequestFactoryTest extends FunctionalTestCase
{
    protected AvalexConfigurationRepository|MockObject $avalexConfigurationRepositoryMock;

    protected ServerRequestInterface $request;

    protected RequestFactory $subject;

    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/avalex',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $site = new Site('main', 1, []);
        $routing = new PageArguments(12, '', []);

        $this->request = (new ServerRequest())
            ->withAttribute('site', $site)
            ->withAttribute('routing', $routing)
            ->withAttribute('currentContentObject', new ContentObjectRenderer());

        $this->avalexConfigurationRepositoryMock = $this->createMock(AvalexConfigurationRepository::class);

        $this->subject = new RequestFactory(
            $this->avalexConfigurationRepositoryMock,
            $this->createMock(LanguageService::class),
            [
                0 => new BedingungenRequest(),
                1 => new DatenschutzerklaerungRequest(),
                2 => new ImpressumRequest(),
                3 => new WiderrufRequest(),
            ],
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->avalexConfigurationRepositoryMock,
            $this->request,
            $this->subject,
        );
    }

    #[Test]
    public function createWithNoAvalexConfigurationWillReturnErrorMessage(): void
    {
        $this->expectException(NoAvalexConfigurationException::class);

        $this->avalexConfigurationRepositoryMock
            ->expects(self::once())
            ->method('findByRootPageUid')
            ->with(self::identicalTo(1))
            ->willThrowException(
                new NoAvalexConfigurationException(
                    'No Avalex configuration could be found in database for page UID: 1',
                ),
            );

        $this->subject->create('', $this->request);
    }

    #[Test]
    public function createWithNonRegisteredEndpointWillReturnErrorMessage(): void
    {
        $this->expectException(InvalidAvalexEndpointException::class);

        $this->avalexConfigurationRepositoryMock
            ->expects(self::once())
            ->method('findByRootPageUid')
            ->with(self::equalTo(1))
            ->willReturn(new AvalexConfiguration(
                1,
                'demo-key-with-online-shop',
                'https://example.com',
                '',
            ));

        $this->subject->create('foo', $this->request);
    }

    public static function endpointDataProvider(): array
    {
        return [
            'Bedingungen Request' => ['avx-bedingungen', BedingungenRequest::class],
            'Datenschutzerklaerung Request' => ['avx-datenschutzerklaerung', DatenschutzerklaerungRequest::class],
            'Impressum Request' => ['avx-impressum', ImpressumRequest::class],
            'Widerruf Request' => ['avx-widerruf', WiderrufRequest::class],
        ];
    }

    #[Test]
    #[DataProvider('endpointDataProvider')]
    public function createWillReturnEndpointRequests(string $endpoint, string $expectedClass): void
    {
        $this->avalexConfigurationRepositoryMock
            ->expects(self::once())
            ->method('findByRootPageUid')
            ->with(self::equalTo(1))
            ->willReturn(new AvalexConfiguration(
                1,
                'demo-key-with-online-shop',
                'https://example.com',
                '',
            ));

        self::assertInstanceOf(
            $expectedClass,
            $this->subject->create($endpoint, $this->request),
        );
    }
}
