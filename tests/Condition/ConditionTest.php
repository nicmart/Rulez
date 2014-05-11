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
use NicMart\Rulez\Maps\MapsCollection;

class ConditionTest extends \PHPUnit_Framework_TestCase
{
    function testResolveToCallback()
    {
        $condition = new Condition("foo", "bar");
        $collection = new MapsCollection;

        $callback = $condition->resolveToCallback($collection);

        $this->setExpectedException("\\OutOfBoundsException");
        $callback("bar");

        $collection["foo"] = function($x) { return $x; };

        $this->assertTrue($callback("bar"));
        $this->assertFalse($callback("baz"));
    }
}
 