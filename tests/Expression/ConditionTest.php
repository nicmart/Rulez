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


use NicMart\Rulez\Expression\Condition;
use NicMart\Rulez\Maps\MapsCollection;

class ConditionTest extends \PHPUnit_Framework_TestCase
{
    function testPredicate()
    {
        $condition = new Condition("foo", "bar");

        $callback = $condition->predicate();

        $this->assertTrue($callback(["foo" => "bar"]));
        $this->assertFalse($callback(["foo" => "baz"]));
        $this->assertFalse($callback(["fooo" => "baz"]));
    }
}
 