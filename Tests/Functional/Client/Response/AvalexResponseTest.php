<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Client\Request;

use JWeiland\Avalex\Client\Response\AvalexResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case.
 */
class AvalexResponseTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected array $testExtensionsToLoad = [
        'jweiland/avalex',
    ];

    protected AvalexResponse $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/tx_avalex_configuration.csv');

        // Set is_siteroot to 1
        $this->setUpFrontendRootPage(1);

        /** @var TypoScriptFrontendController|MockObject|AccessibleObjectInterface $typoScriptFrontendController */
        $typoScriptFrontendController = $this->getAccessibleMock(TypoScriptFrontendController::class, [], [], '', false);
        $GLOBALS['TSFE'] = $typoScriptFrontendController;
        $GLOBALS['TSFE']->id = 1;
        $GLOBALS['TSFE']->_set('spamProtectEmailAddresses', 1);
    }

    protected function tearDown(): void
    {
        unset(
            $this->subject,
            $GLOBALS['TSFE'],
        );
    }

    #[Test]
    public function getBodyReturnsEmptyString(): void
    {
        $this->subject = new AvalexResponse('');
        self::assertSame(
            '',
            $this->subject->getBody(),
        );
    }

    #[Test]
    public function getBodyReturnsContentAsString(): void
    {
        $this->subject = new AvalexResponse('test123');
        self::assertSame(
            'test123',
            $this->subject->getBody(),
        );
    }

    #[Test]
    public function getBodyReturnsContentAsArray(): void
    {
        $this->subject = new AvalexResponse('{"firstname":"stefan"}');
        $this->subject->setIsJsonResponse(true);
        self::assertSame(
            [
                'firstname' => 'stefan',
            ],
            $this->subject->getBody(),
        );
    }

    #[Test]
    public function getHeadersReturnsEmptyArray(): void
    {
        $this->subject = new AvalexResponse('test123', []);
        self::assertSame(
            [],
            $this->subject->getHeaders(),
        );
    }

    #[Test]
    public function getHeadersWithStringReturnsArray(): void
    {
        $this->subject = new AvalexResponse(
            '',
            'Expires: 0' . CRLF . 'Content-Length: 123',
        );
        self::assertSame(
            [
                'Expires' => [
                    0 => '0',
                ],
                'Content-Length' => [
                    0 => '123',
                ],
            ],
            $this->subject->getHeaders(),
        );
    }

    #[Test]
    public function getHeadersWithSimpleArrayReturnsArray(): void
    {
        $this->subject = new AvalexResponse(
            '',
            [
                'Expires' => '0',
                'Content-Length' => '123',
            ],
        );
        self::assertSame(
            [
                'Expires' => [
                    0 => '0',
                ],
                'Content-Length' => [
                    0 => '123',
                ],
            ],
            $this->subject->getHeaders(),
        );
    }

    #[Test]
    public function getHeadersWithComplexArrayReturnsArray(): void
    {
        $headers = [
            'Expires' => [
                0 => '0',
                1 => '2',
            ],
            'Content-Length' => [
                0 => '123',
                1 => '321',
            ],
        ];
        $this->subject = new AvalexResponse('', $headers);
        self::assertSame(
            $headers,
            $this->subject->getHeaders(),
        );
    }

    #[Test]
    public function getStatusCodeReturnsInitially200(): void
    {
        $this->subject = new AvalexResponse();
        self::assertSame(
            200,
            $this->subject->getStatusCode(),
        );
    }

    #[Test]
    public function getStatusCodeReturns401(): void
    {
        $this->subject = new AvalexResponse('', [], 401);
        self::assertSame(
            401,
            $this->subject->getStatusCode(),
        );
    }

    #[Test]
    public function isJsonResponseInitiallyReturnsFalse(): void
    {
        $this->subject = new AvalexResponse('');
        self::assertFalse(
            $this->subject->isJsonResponse(),
        );
    }

    #[Test]
    public function setJsonResponseSetsJsonResponse(): void
    {
        $this->subject = new AvalexResponse('');
        $this->subject->setIsJsonResponse(true);
        self::assertTrue(
            $this->subject->isJsonResponse(),
        );
    }
}
