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


use NicMart\Rulez\Expression\Expression;
use NicMart\Rulez\Expression\Condition;
use NicMart\Rulez\Expression\NotProposition;
use NicMart\Rulez\Maps\MapsCollection;

class NotPropositionTest extends \PHPUnit_Framework_TestCase
{
    function mockExpression($value)
    {
        $mock = $this->getMockBuilder('\NicMart\Rulez\Expression\Expression')
            ->getMock();

        $mock
            ->expects($this->any())
            ->method("predicate")
            ->will($this->returnCallback(function() use ($value) {
                return function ($x) use ($value) {
                    return $x == $value;
                };
            }));

        return $mock;
    }

    function testConstructor()
    {
        $prop = new NotProposition([
            $a = $this->mockExpression(true),
            $b = $this->mockExpression(false)
        ]);

        $this->assertEquals([$a, $b], $prop->expressions());
    }

    function testAddAndGetConditions()
    {
        $conditions = [$this->mockExpression("a"), $this->mockExpression("b"), $this->mockExpression("c")];

        $proposition = new NotProposition;
        $proposition
            ->addExpression($conditions[0])
            ->addExpressions(array_slice($conditions, 1));

        $this->assertEquals($conditions, $proposition->expressions());
    }

    function testPredicate()
    {
        $proposition = (new NotProposition)
            ->addExpression($this->mockExpression("a"))
            ->addExpression($this->mockExpression("a"));

        $callback = $proposition->predicate();

        $this->assertFalse($callback("a"));
        $this->assertTrue($callback("b"));


        $proposition->addExpression($this->mockExpression("b"));

        $callback = $proposition->predicate();

        $this->assertTrue($callback("asdsd"));
        $this->assertFalse($callback("b"));
    }
}
