<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Test\Evaluation;

use NicMart\Rulez\Evaluation\PositivePropositionEvaluation;

class PositivePropositionEvaluationTest extends \PHPUnit_Framework_TestCase
{
    public function testWithAtLeastEqualTo1()
    {
        $callCount = 0;
        $lastStatus = null;

        $callback = function($status) use (&$callCount, &$lastStatus) {
            $lastStatus = $status;
            $callCount++;
        };

        $evaluation = new PositivePropositionEvaluation(1, $callback);
        $this->assertFalse($evaluation->isResolved());
        $this->assertSame(0, $callCount);
        $this->assertNull($lastStatus);
        $this->setExpectedException("\\LogicException");
        $evaluation->resolvedStatus();

        $evaluation->input(true);
        $this->assertTrue($evaluation->isResolved());
        $this->assertTrue($evaluation->resolvedStatus());
        $this->assertSame(1, $callCount);
        $this->assertTrue($lastStatus);

        // Do not change anything
        $evaluation->input(false);
        $this->assertTrue($evaluation->isResolved());
        $this->assertTrue($evaluation->resolvedStatus());
        $this->assertSame(1, $callCount);
        $this->assertTrue($lastStatus);

        // Try to reset
        $evaluation->reset();
        $this->assertFalse($evaluation->isResolved());
        $evaluation->input(true);
        $this->assertTrue($evaluation->isResolved());
        $this->assertTrue($evaluation->resolvedStatus());
        $this->assertSame(2, $callCount);
        $this->assertTrue($lastStatus);
    }

    public function testWithAtLeastEqualTo3()
    {
        $callback = function($status) { };

        $evaluation = new PositivePropositionEvaluation(3, $callback);
        $this->assertFalse($evaluation->isResolved());

        $evaluation->input(true)->input(false)->input(true);
        $this->assertFalse($evaluation->isResolved());

        // Do not change anything
        $evaluation->input(true);
        $this->assertTrue($evaluation->isResolved());
        $this->assertTrue($evaluation->resolvedStatus());

        $evaluation->reset();
        $this->assertFalse($evaluation->isResolved());
    }
}
 