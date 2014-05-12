<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Test\Condition;


use NicMart\Rulez\Condition\Condition;
use NicMart\Rulez\Condition\Proposition;
use NicMart\Rulez\Maps\MapsCollection;

class PropositionTest extends \PHPUnit_Framework_TestCase
{
    function testConstructor()
    {
        $proposition = new Proposition(1, 3);

        $this->assertSame(1, $proposition->atLeast());
        $this->assertSame(3, $proposition->atMost());

        $proposition = new Proposition;

        $this->assertSame(1, $proposition->atLeast());
        $this->assertSame(INF, $proposition->atMost());

        $this->setExpectedException("\\InvalidArgumentException");
        $proposition = new Proposition(3, 1);
    }

    function testAddAndGetConditions()
    {
        $conditions = [new Condition("a", "b"), new Condition("a", "c"), new Condition("b", "d")];

        $proposition = new Proposition;
        $proposition
            ->addCondition($conditions[0])
            ->addConditions(array_slice($conditions, 1));

        $this->assertEquals($conditions, $proposition->conditions());
    }

    function testResolveToCallback()
    {
        $maps = new MapsCollection;
        $maps["down"] = "strtolower";
        $maps["up"] = "strtoupper";
        $maps["mod5"] = function($x) { return $x % 5; };
        $maps["mod3"] = function($x) { return $x % 3; };

        // OR
        $proposition = (new Proposition(1))
            ->addCondition(new Condition("down", "bar"))
            ->addCondition(new Condition("up", "FOO"));

        $callback = $proposition->resolveToCallback($maps);

        $this->assertTrue($callback("bAr"));
        $this->assertTrue($callback("foo"));
        $this->assertFalse($callback("a"));


        // AND
        $proposition = (new Proposition(2))
            ->addCondition(new Condition("mod3", 0))
            ->addCondition(new Condition("mod5", 0));

        $callback = $proposition->resolveToCallback($maps);

        $this->assertTrue($callback(0));
        $this->assertTrue($callback(15));
        $this->assertTrue($callback(30));
        $this->assertFalse($callback(5));
        $this->assertFalse($callback(3));
        $this->assertFalse($callback(12));

        // NOT
        $proposition = (new Proposition(0, 0))
            ->addCondition(new Condition("mod3", 0))
            ->addCondition(new Condition("mod5", 0));

        $callback = $proposition->resolveToCallback($maps);

        $this->assertFalse($callback(0));
        $this->assertFalse($callback(15));
        $this->assertFalse($callback(30));
        $this->assertFalse($callback(5));
        $this->assertFalse($callback(3));
        $this->assertTrue($callback(11));
        $this->assertTrue($callback(17));
    }
}
 