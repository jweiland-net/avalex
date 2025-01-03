<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\EventListener;

use JWeiland\Avalex\Client\Request\BedingungenRequest;
use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use JWeiland\Avalex\Event\PostProcessApiResponseContentEvent;
use JWeiland\Avalex\EventListener\UpdateLinksInAvalexContentEventListener;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class UpdateLinksInAvalexContentEventListenerTest extends FunctionalTestCase
{
    protected ContentObjectRenderer $contentObjectRenderer;

    protected UpdateLinksInAvalexContentEventListener $subject;

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

        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setConfigArray([
            'spamProtectEmailAddresses' => 5,
        ]);

        $request = (new ServerRequest())
            ->withAttribute('site', $site)
            ->withAttribute('routing', $routing)
            ->withAttribute('frontend.typoscript', $frontendTypoScript);

        $this->contentObjectRenderer = new ContentObjectRenderer();
        $this->contentObjectRenderer->setRequest($request);

        $this->subject = new UpdateLinksInAvalexContentEventListener();
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
        );
    }

    #[Test]
    public function invokeWillEncryptEmailLinks(): void
    {
        $endpointRequest = new BedingungenRequest();
        $endpointRequest->setAvalexConfiguration(new AvalexConfiguration(
            1,
            'demo-key-with-online-shop',
            'https://example.com',
            '',
        ));

        $postProcessApiResponseContentEvent = new PostProcessApiResponseContentEvent(
            'Contact me here: <a href="mailto:foo@example.com">Max Mustermann</a>',
            $endpointRequest,
            $this->contentObjectRenderer,
        );

        $this->subject->__invoke($postProcessApiResponseContentEvent);

        self::assertSame(
            'Contact me here: <a href="#" data-mailto-token="rfnqyt/kttEjcfruqj3htr" data-mailto-vector="5">Max Mustermann</a>',
            $postProcessApiResponseContentEvent->getContent(),
        );
    }
}
