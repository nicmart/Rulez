<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Test\Engine;


use NicMart\Rulez\Condition\Condition;
use NicMart\Rulez\Condition\Proposition;
use NicMart\Rulez\Engine\Engine;
use NicMart\Rulez\Engine\Rule;
use NicMart\Rulez\Maps\MapsCollection;

class EngineTest extends \PHPUnit_Framework_TestCase
{
    function testRun()
    {
        $collection = new MapsCollection;
        $collection["="] = function($x) { return $x; };
        $collection["+1"] = function($x) { return $x + 1; };
        $collection["*2"] = function($x) { return 2 * $x; };
        $collection["*5"] = function($x) { return $x * 5; };
        $collection["%3"] = function($x) { return $x % 3; };
        $collection["%5"] = function($x) { return $x % 5; };
        $collection["%7"] = function($x) { return $x % 7; };
        $collection["%15"] = function($x) { return $x % 15; };
        $collection["%2"] = function($x) { return $x % 2; };
        $collection[">50"] = function($x) { return $x > 50; };

        $engine = new Engine($collection);

        $prop1 = (new Proposition(1))
            ->addCondition(new Condition("%3", 0))
            ->addCondition(new Condition("%5", 0))
        ;

        $prop2 = (new Proposition(2, 2))
            ->addCondition(new Condition("%3", 0))
            ->addCondition(new Condition("%5", 0))
        ;
        $prop3 = (new Proposition(1))
            ->addCondition(new Condition("=", 10))
            ->addCondition(new Condition("=", 11))
            ->addCondition(new Condition("=", 12))
            ->addCondition(new Condition("=", 13))
            ->addCondition(new Condition("=", 14))
        ;

        $prop4 = (new Proposition(0, 0))
            ->addCondition(new Condition("%3", 0))
            ->addCondition(new Condition("%5", 0))
            ->addCondition(new Condition("%5", 1))
        ;

        $prop5 = (new Proposition(1))
            ->addCondition(new Condition(">50", true))
        ;

        $engine
            ->addRule(new Rule($prop1, "Mod 3 o 5"))
            ->addRule(new Rule($prop2, "Mod 15"))
            ->addRule(new Rule($prop3, "10-14"))
            ->addRule(new Rule($prop4, "NOT 3k, 5k, 5k + 1"))
            ->addRule(new Rule($prop5, "Greater than 50"))
        ;



        $n = rand(0, 100);
        var_dump($n);
        var_dump($this->toAry($engine->run($n)));
    }

    private function toAry(\SplObjectStorage $storage)
    {
        $ary = [];

        foreach($storage as $element) {
            $ary[] = $element->production();
        }

        return $ary;
    }
}
 