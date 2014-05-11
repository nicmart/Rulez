<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Test\Maps;

use NicMart\Rulez\Maps\MapsCollection;

class MapsCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MapsCollection
     */
    private $collection;

    protected function setUp()
    {
        $this->collection = new MapsCollection;
    }

    /**
     * @expectedException \DomainException
     */
    function testOnlyCallableOffsetAreAllowed()
    {
        $this->collection["blabla"] = "foo";
    }

    function testRegisterAndGetMaps()
    {
        $this->collection["a"] = $a = function() { return "a"; };
        $this->collection->registerMap("b", "strtolower");

        $this->assertSame($a, $this->collection["a"]);
        $this->assertSame("strtolower", $this->collection["b"]);
    }

    function testIteration()
    {
        $this->collection["a"] = $a = function() { return "a"; };
        $this->collection->registerMap("b", "strtolower");

        $ary = iterator_to_array($this->collection);

        $this->assertEquals(["a" => $a, "b" => "strtolower"], $ary);
    }
}
 