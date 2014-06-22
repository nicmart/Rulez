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


use NicMart\Arrayze\ArrayAdapter;
use NicMart\Arrayze\MapsCollection;
use NicMart\Rulez\Expression\Condition;
use NicMart\Rulez\Expression\AndProposition;
use NicMart\Rulez\Engine\Engine;
use NicMart\Rulez\Engine\Rule;
use NicMart\Rulez\Engine\ScanEngine;
use NicMart\Rulez\Expression\NotProposition;
use NicMart\Rulez\Expression\OrProposition;

class EngineTest extends \PHPUnit_Framework_TestCase
{
    function testRun()
    {
        $collection = (new MapsCollection)->registerMaps([
            "=" => function($x) { return $x; },
            "+1" => function($x) { return $x + 1; },
            "*2" => function($x) { return 2 * $x; },
            "*5" => function($x) { return $x * 5; },
            "%3" => function($x) { return $x % 3; },
            "%5" => function($x) { return $x % 5; },
            "%7" => function($x) { return $x % 7; },
            "%15" => function($x) { return $x % 15; },
            "%2" => function($x) { return $x % 2; },
            ">50" => function($x) { return $x > 50; },
        ]);

        $engine = new Engine($collection);
        $scanEngine = new ScanEngine($collection);

        $prop1 = (new OrProposition)
            ->addExpression(new Condition("%3", 0))
            ->addExpression(new Condition("%5", 0))
        ;

        $prop2 = (new AndProposition)
            ->addExpression(new Condition("%3", 0))
            ->addExpression(new Condition("%5", 0))
        ;
        $prop3 = (new OrProposition)
            ->addExpression(new Condition("=", 10))
            ->addExpression(new Condition("=", 11))
            ->addExpression(new Condition("=", 12))
            ->addExpression(new Condition("=", 13))
            ->addExpression(new Condition("=", 14))
        ;

        $prop4 = (new OrProposition)
            ->addExpression(new Condition("%7", 1))
            ->addExpression(new Condition("%7", 2))
            ->addExpression(new Condition("%7", 3))
        ;

        $prop5 = (new AndProposition)
            ->addExpression(new Condition(">50", true))
        ;

        $prop6 = (new NotProposition)
            ->addExpression(new Condition("%7", 1))
            ->addExpression(new Condition("%7", 2))
            ->addExpression(new Condition("%7", 3))
        ;

        $propNotCombined = (new NotProposition)
            ->addExpression($prop6)
            ->addExpression(new Condition("%5", 0))
        ;

        $propCompositeOr = new OrProposition([
            $prop1, $prop4
        ]);

        $propCompositeAnd = new AndProposition([
            $prop1, $prop4
        ]);

        $engine
            ->addRule(new Rule($prop1, "Mod 3 o 5"))
            ->addRule(new Rule($prop2, "Mod 15"))
            ->addRule(new Rule($prop3, "10-14"))
            ->addRule(new Rule($prop4, "7k +1,2,3"))
            ->addRule(new Rule($prop5, "Greater than 50"))
            ->addRule(new Rule($propCompositeOr, "Mod 3 or Mod 5 or 7k +1,2,3"))
            ->addRule(new Rule($propCompositeAnd, "Mod 3 or Mod 5 AND 7k+1,2,3"))
            ->addRule(new Rule($prop6, "NOT 7k+1,2,3"))
            ->addRule(new Rule($propNotCombined, "7k+1,2,3 DOUBLE NOT"))
        ;
        $scanEngine
            ->addRule(new Rule($prop1, "Mod 3 o 5"))
            ->addRule(new Rule($prop2, "Mod 15"))
            ->addRule(new Rule($prop3, "10-14"))
            ->addRule(new Rule($prop4, "7k +1,2,3"))
            ->addRule(new Rule($prop5, "Greater than 50"))
            ->addRule(new Rule($propCompositeOr, "Mod 3 or Mod 5 or 7k +1,2,3"))
            ->addRule(new Rule($propCompositeAnd, "Mod 3 or Mod 5 AND 7k+1,2,3"))
            ->addRule(new Rule($prop6, "NOT 7k+1,2,3"))
            ->addRule(new Rule($propNotCombined, "7k+1,2,3 DOUBLE NOT"))
        ;

        $n = rand(0, 100);

        $x = new ArrayAdapter($n, $collection);

        var_dump($n);
        var_dump($this->toAry($engine->run($x)));
        var_dump($this->toAry($scanEngine->run($x)));
        var_dump($this->toAry($scanEngine->run($x)));
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
 