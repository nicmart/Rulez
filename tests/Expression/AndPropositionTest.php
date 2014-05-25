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
use NicMart\Rulez\Expression\AndProposition;
use NicMart\Rulez\Maps\MapsCollection;

class AndPropositionTest extends \PHPUnit_Framework_TestCase
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
        $a = $this->mockExpression(true);

        $prop = new AndProposition([
            $a = $this->mockExpression(true),
            $b = $this->mockExpression(false)
        ]);

        $this->assertEquals([$a, $b], $prop->expressions());
    }

    function testAddAndGetConditions()
    {
        $conditions = [$this->mockExpression("a"), $this->mockExpression("b"), $this->mockExpression("c")];

        $proposition = new AndProposition;
        $proposition
            ->addExpression($conditions[0])
            ->addExpressions(array_slice($conditions, 1));

        $this->assertEquals($conditions, $proposition->expressions());
    }

    function testPredicate()
    {
        $proposition = (new AndProposition)
            ->addExpression($this->mockExpression("a"))
            ->addExpression($this->mockExpression("a"));

        $callback = $proposition->predicate();

        $this->assertTrue($callback("a"));
        $this->assertFalse($callback("b"));


        $proposition->addExpression($this->mockExpression("b"));

        $callback = $proposition->predicate();

        $this->assertFalse($callback("asdsd"));
    }
}
 