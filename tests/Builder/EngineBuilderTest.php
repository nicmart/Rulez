<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Test\Builder;


use NicMart\Rulez\Builder\EngineBuilder;
use NicMart\Rulez\Engine\Engine;

ini_set('xdebug.var_display_max_depth', '10');

class EngineBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuilder()
    {
        $builder = new EngineBuilder(new Engine);

        $engine = $builder
            ->ifAll()
                ->eq("foo", "fooval")
                ->eq("bar", "barval")
            ->end()
            ->then("A")

            ->ifAny()
                ->eq("a", "b")
                ->all()
                    ->eq("a", "b")
                    ->eq("c", "d")
                    ->eq("d", "f")
                ->end()
                ->eq("c", "d")
            ->end()
            ->then("B")

            ->ifNone()
                ->eq("a", "b")
                ->eq("c", "h")
            ->end()
            ->then("C")
        ->end();

        var_dump($engine);
    }
}
 