<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Client\Request\Endpoint;

use JWeiland\Avalex\Client\Request\Endpoint\IsApiKeyConfiguredRequest;
use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class IsApiKeyConfiguredRequestTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/avalex',
    ];

    protected IsApiKeyConfiguredRequest $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new IsApiKeyConfiguredRequest();
        $this->subject->setAvalexConfiguration(new AvalexConfiguration(
            1,
            'demo-key-with-online-shop',
            'https://example.com',
            '',
        ));
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $GLOBALS['TSFE'],
        );
    }

    #[Test]
    public function getEndpointReturnsEndpoint(): void
    {
        self::assertSame(
            'api_keys/is_configured.json',
            $this->subject->getEndpoint(),
        );
    }

    #[Test]
    public function getEndpointWithoutPrefixReturnsEndpointWithoutPrefix(): void
    {
        self::assertSame(
            'keys/is_configured.json',
            $this->subject->getEndpointWithoutPrefix(),
        );
    }

    #[Test]
    public function getParametersReturnsRequiredParameters(): void
    {
        self::assertSame(
            [
                'apikey' => 'demo-key-with-online-shop',
            ],
            $this->subject->getParameters(),
        );
    }

    #[Test]
    public function getParametersWithApiKeyReturnsParametersWithApiKey(): void
    {
        $this->subject->setApiKey('API_KEY');
        self::assertSame(
            [
                'apikey' => 'API_KEY',
            ],
            $this->subject->getParameters(),
        );
    }

    #[Test]
    public function getParametersWithInvalidParametersReturnsRequiredParameters(): void
    {
        $this->subject->addParameter('foo', 'bar');
        self::assertSame(
            [
                'apikey' => 'demo-key-with-online-shop',
            ],
            $this->subject->getParameters(),
        );
    }

    #[Test]
    public function setParametersWillOnlySetAllowedParameters(): void
    {
        $this->subject->setParameters([
            'foo' => 'bar',
            'lang' => 'en',
            'apikey' => 'API_KEY',
        ]);

        // API KEY will only be set through setApiKey(). setParameters has no effect
        self::assertSame(
            [
                'apikey' => 'demo-key-with-online-shop',
            ],
            $this->subject->getParameters(),
        );
    }
}
