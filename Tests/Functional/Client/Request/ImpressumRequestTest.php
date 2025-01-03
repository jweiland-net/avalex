<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Client\Request;

use JWeiland\Avalex\Client\Request\ImpressumRequest;
use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class ImpressumRequestTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/avalex',
    ];

    protected ImpressumRequest $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new ImpressumRequest();
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
            'avx-impressum',
            $this->subject->getEndpoint(),
        );
    }

    #[Test]
    public function getEndpointWithoutPrefixReturnsEndpointWithoutPrefix(): void
    {
        self::assertSame(
            'impressum',
            $this->subject->getEndpointWithoutPrefix(),
        );
    }

    #[Test]
    public function getParametersReturnsRequiredParameters(): void
    {
        self::assertSame(
            [
                'apikey' => 'demo-key-with-online-shop',
                'domain' => 'https://example.com',
            ],
            $this->subject->getParameters(),
        );
    }

    #[Test]
    public function getParametersWithDomainReturnsParametersWithDomain(): void
    {
        $this->subject->setDomain('https://www.jweiland.net');
        self::assertSame(
            [
                'domain' => 'https://www.jweiland.net',
                'apikey' => 'demo-key-with-online-shop',
            ],
            $this->subject->getParameters(),
        );
    }

    #[Test]
    public function getParametersWithLangReturnsParametersWithLang(): void
    {
        $this->subject->setLang('en');
        self::assertSame(
            [
                'lang' => 'en',
                'apikey' => 'demo-key-with-online-shop',
                'domain' => 'https://example.com',
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
                'domain' => 'https://example.com',
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
            'domain' => 'https://www.jweiland.net',
        ]);
        self::assertSame(
            [
                'lang' => 'en',
                'domain' => 'https://www.jweiland.net',
                'apikey' => 'demo-key-with-online-shop',
            ],
            $this->subject->getParameters(),
        );
    }
}
