<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional\Client\Request;

use JWeiland\Avalex\Client\Request\GetDomainLanguagesRequest;
use JWeiland\Avalex\Client\Response\AvalexResponse;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test AvalexResponse
 */
class AvalexResponseTest extends FunctionalTestCase
{
    use ProphecyTrait;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/avalex'
    ];

    /**
     * @var AvalexResponse
     */
    protected $subject;

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
    public function getBodyReturnsEmptyString(): void
    {
        $this->subject = new AvalexResponse('');
        self::assertSame(
            '',
            $this->subject->getBody()
        );
    }

    /**
     * @test
     */
    public function getBodyReturnsContentAsString(): void
    {
        $this->subject = new AvalexResponse('test123');
        self::assertSame(
            'test123',
            $this->subject->getBody()
        );
    }

    /**
     * @test
     */
    public function isJsonResponseInitiallyReturnsFalse(): void
    {
        $this->subject = new AvalexResponse('');
        self::assertFalse(
            $this->subject->isJsonResponse()
        );
    }

    /**
     * @test
     */
    public function setJsonResponseSetsJsonResponse(): void
    {
        $this->subject = new AvalexResponse('');
        $this->subject->setIsJsonResponse(true);
        self::assertTrue(
            $this->subject->isJsonResponse()
        );
    }

    /**
     * @test
     */
    public function getBodyReturnsContentAsArray(): void
    {
        $this->subject = new AvalexResponse('{"firstname":"stefan"}');
        $this->subject->setIsJsonResponse(true);
        self::assertSame(
            [
                'firstname' => 'stefan'
            ],
            $this->subject->getBody()
        );
    }
}
