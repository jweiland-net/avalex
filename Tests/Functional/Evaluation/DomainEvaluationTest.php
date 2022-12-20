<?php

/*
 * This file is part of the package jweiland/avalex.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Avalex\Tests\Functional;

use JWeiland\Avalex\Evaluation\DomainEvaluation;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;

/**
 * Test case.
 */
class DomainEvaluationTest extends FunctionalTestCase
{
    /**
     * @var DomainEvaluation
     */
    protected $subject;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/avalex',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new DomainEvaluation();
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
    public function returnFieldJSWillReturnValidationJavaScript()
    {
        self::assertContains(
            'https://',
            $this->subject->returnFieldJS()
        );
    }

    /**
     * @test
     */
    public function evaluateFieldValueWillNotAddScheme()
    {
        $set = false;
        self::assertSame(
            'https://jweiland.net',
            $this->subject->evaluateFieldValue(
                'https://jweiland.net',
                '',
                $set
            )
        );
    }

    /**
     * @test
     */
    public function evaluateFieldValueWillAddScheme()
    {
        $set = false;
        self::assertSame(
            'https://jweiland.net',
            $this->subject->evaluateFieldValue(
                'jweiland.net',
                '',
                $set
            )
        );
    }
}
