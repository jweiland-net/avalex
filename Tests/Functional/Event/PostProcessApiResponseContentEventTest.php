<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Event;

use JWeiland\Avalex\Client\Request\Endpoint\BedingungenRequest;
use JWeiland\Avalex\Client\Request\RequestInterface;
use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use JWeiland\Avalex\Event\PostProcessApiResponseContentEvent;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class PostProcessApiResponseContentEventTest extends FunctionalTestCase
{
    protected PostProcessApiResponseContentEvent $subject;

    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/avalex',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $endpointRequest = new BedingungenRequest();
        $endpointRequest->setAvalexConfiguration(new AvalexConfiguration(
            1,
            'demo-key-with-online-shop',
            'https://example.com',
            '',
        ));

        $request = (new ServerRequest('https://example.com/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $request = $request->withAttribute('normalizedParams', NormalizedParams::createFromServerParams($request->getServerParams()));

        $this->subject = new PostProcessApiResponseContentEvent(
            'Hello World!',
            $endpointRequest,
            $this->get(ContentObjectRenderer::class),
            $request,
        );
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
        );
    }

    #[Test]
    public function getContentWillReturnContent(): void
    {
        self::assertSame(
            'Hello World!',
            $this->subject->getContent(),
        );
    }

    #[Test]
    public function setContentWillSetContent(): void
    {
        $this->subject->setContent('Foo');

        self::assertSame(
            'Foo',
            $this->subject->getContent(),
        );
    }

    #[Test]
    public function getEndpointWillReturnEndpoint(): void
    {
        self::assertInstanceOf(
            RequestInterface::class,
            $this->subject->getAvalexRequest(),
        );
    }

    #[Test]
    public function getContentObjectRendererWillReturnContentObjectRenderer(): void
    {
        self::assertInstanceOf(
            ContentObjectRenderer::class,
            $this->subject->getContentObjectRenderer(),
        );
    }
}
