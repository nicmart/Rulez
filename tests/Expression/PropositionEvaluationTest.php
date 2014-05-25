<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Test\Expression;

use NicMart\Rulez\Expression\PropositionEvaluation;

class PropositionEvaluationTest extends \PHPUnit_Framework_TestCase
{
    public function testAnOR()
    {
        $callback = function($status) { };

        // An OR, with only two maps
        $evaluation = new PropositionEvaluation(2, 1, INF, $callback);
        $this->assertFalse($evaluation->isResolved());

        $evaluation->signalMapUsed();
        $this->assertFalse($evaluation->isResolved());

        $this->setExpectedException("\\LogicException");
        $evaluation->resolvedStatus();

        $evaluation->signalMapUsed();
        $this->assertTrue($evaluation->isResolved());
        $this->assertFalse($evaluation->resolvedStatus());

        $evaluation->reset();
        $this->assertFalse($evaluation->isResolved());

        $evaluation->signalMatch();
        $evaluation->signalMapUsed();
        $this->assertTrue($evaluation->isResolved());
        $this->assertTrue($evaluation->resolvedStatus());
    }

    public function testAnAnd()
    {
        $callback = function($status) { };

        // An OR, with only two maps
        $evaluation = new PropositionEvaluation(3, 3, INF, $callback);
        $this->assertFalse($evaluation->isResolved());

        $evaluation->signalMapUsed();
        $this->assertTrue($evaluation->isResolved());
        $this->assertFalse($evaluation->resolvedStatus());

        $evaluation->reset();

        // First condition
        $evaluation->signalMatch();
        $evaluation->signalMapUsed();
        $this->assertFalse($evaluation->isResolved());

        // Second
        $evaluation->signalMatch();
        $evaluation->signalMapUsed();
        $this->assertFalse($evaluation->isResolved());

        // Third
        $evaluation->signalMatch();
        $evaluation->signalMapUsed();
        $this->assertTrue($evaluation->isResolved());
        $this->assertTrue($evaluation->resolvedStatus());
    }

    public function testANot()
    {
        $callback = function($status) { };

        // An OR, with only two maps
        $evaluation = new PropositionEvaluation(3, -INF, 0, $callback);
        $this->assertFalse($evaluation->isResolved());

        $evaluation->signalMapUsed();
        $this->assertFalse($evaluation->isResolved());

        $evaluation->signalMatch();
        $evaluation->signalMapUsed();
        $this->assertTrue($evaluation->isResolved());
        $this->assertFalse($evaluation->resolvedStatus());

        $evaluation->reset();

        // First condition
        $evaluation->signalMapUsed();
        $this->assertFalse($evaluation->isResolved());

        // Second
        $evaluation->signalMapUsed();
        $this->assertFalse($evaluation->isResolved());

        // Third
        $evaluation->signalMapUsed();
        $this->assertTrue($evaluation->isResolved());
        $this->assertTrue($evaluation->resolvedStatus());
    }
}
 