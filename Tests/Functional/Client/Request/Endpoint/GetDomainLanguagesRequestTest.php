<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Client\Request\Endpoint;

use JWeiland\Avalex\Client\Request\Endpoint\GetDomainLanguagesRequest;
use JWeiland\Avalex\Domain\Model\AvalexConfiguration;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class GetDomainLanguagesRequestTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/avalex',
    ];

    protected GetDomainLanguagesRequest $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new GetDomainLanguagesRequest();
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
            'avx-get-domain-langs',
            $this->subject->getEndpoint(),
        );
    }

    #[Test]
    public function getEndpointWithoutPrefixReturnsEndpointWithoutPrefix(): void
    {
        self::assertSame(
            'get-domain-langs',
            $this->subject->getEndpointWithoutPrefix(),
        );
    }

    #[Test]
    public function getParametersReturnsRequiredParameters(): void
    {
        $request = (new ServerRequest('https://example.com/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $normalizedParams = NormalizedParams::createFromServerParams($request->getServerParams());

        self::assertSame(
            [
                'apikey' => 'demo-key-with-online-shop',
                'domain' => 'https://example.com',
                'version' => '3.0.1',
            ],
            $this->subject->getParameters($normalizedParams),
        );
    }

    #[Test]
    public function getParametersWithDomainReturnsParametersWithDomain(): void
    {
        $this->subject->setDomain('https://www.jweiland.net');

        $request = (new ServerRequest('https://example.com/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $normalizedParams = NormalizedParams::createFromServerParams($request->getServerParams());

        self::assertSame(
            [
                'domain' => 'https://www.jweiland.net',
                'apikey' => 'demo-key-with-online-shop',
                'version' => '3.0.1',
            ],
            $this->subject->getParameters($normalizedParams),
        );
    }

    #[Test]
    public function getParametersWithInvalidParametersReturnsRequiredParameters(): void
    {
        $this->subject->addParameter('foo', 'bar');

        $request = (new ServerRequest('https://example.com/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $normalizedParams = NormalizedParams::createFromServerParams($request->getServerParams());

        self::assertSame(
            [
                'apikey' => 'demo-key-with-online-shop',
                'domain' => 'https://example.com',
                'version' => '3.0.1',
            ],
            $this->subject->getParameters($normalizedParams),
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

        $request = (new ServerRequest('https://example.com/', 'GET'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $normalizedParams = NormalizedParams::createFromServerParams($request->getServerParams());

        self::assertSame(
            [
                'domain' => 'https://www.jweiland.net',
                'apikey' => 'demo-key-with-online-shop',
                'version' => '3.0.1',
            ],
            $this->subject->getParameters($normalizedParams),
        );
    }
}
