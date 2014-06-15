<?php
/**
 * This file is part of Benchmark
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

include '../vendor/autoload.php';
ini_set('xdebug.var_display_max_depth', '10');
ini_set('max_execution_time', '120');

use NicMart\Rulez\Expression\AndProposition;
use NicMart\Rulez\Expression\NotProposition;
use NicMart\Rulez\Expression\OrProposition;
use NicMart\Rulez\Expression\Condition;
use NicMart\Rulez\Engine\Rule;
use NicMart\Rulez\Engine\Engine;
use NicMart\Rulez\Engine\EngineInterface;
use NicMart\Rulez\Engine\ScanEngine;
use NicMart\Rulez\Maps\MapsCollection;

function nthLetter($n,$sleep = 0)
{
    return function($x) use ($n, $sleep)
    {
        return $x[$n];
    };
}

function benchmark(MapsCollection $collection, array $propositions, array $iterations, $input, $title = "ScanEngine vs SmartEngine")
{
    $smartEngine = engine(Engine::class, $collection, $propositions);
    $scanEngine = engine(ScanEngine::class, $collection, $propositions);

    $bench = new \Nicmart\Benchmark\FixedSizeEngine($title);

    $bench
        ->register('scan', 'Scan Engine', function() use ($scanEngine) {
            return $scanEngine->run("asdaskdjaòlkdjòalksdjaòlskdjòalksdj");
        }, true)
        ->register('smart', 'Smart Engine', function() use ($smartEngine) {
            return $smartEngine->run("asdaskdjaòlkdjòalksdjaòlskdjòalksdj");
        }, true)
    ;

    foreach ($iterations as $n)
    {
        $bench->benchmark($n);
    }

    return $bench->getResults();
}

function engine($class, MapsCollection $collection, $propositions)
{
    $engine = new $class($collection);

    foreach ($propositions as $i => $prop) {
        $rule = new Rule($prop, $i);
        $engine->addRule($rule);
    }

    return $engine;
}

function prop(MapsCollection $collection, $numConditions, $isOr = true)
{
    $prop = $isOr
        ? new OrProposition
        : new AndProposition
    ;

    $numOfMaps = count($collection);

    for ($i = 0; $i < $numConditions; $i++) {
        $mapName = (string) rand(0, $numOfMaps - 1);
        $condValue = (string) rand(0, 9);

        $prop->addExpression(new Condition($mapName, $condValue, $collection));
    }

    return $prop;
}

function collection($numOfMaps, $sleep = 0)
{
    $collection = new \NicMart\Rulez\Maps\MapsCollection;
    for ($i = 0; $i < $numOfMaps; $i++) {
        $collection[(string) $i] = nthLetter($i, $sleep);
    }

    return $collection;
}

function generateNumberOfMapsProgression($class)
{
    $engine = new $class(collection(1));

    return function($n) use ($engine)
    {
        $engine->setMapsCollection($collection = collection($n));

        addPropsToEngine($engine, [
            prop($collection, 1, true),
            prop($collection, 10, true),
            prop($collection, 10, false),
            prop($collection, 5, true),
            prop($collection, 20, false),
        ]);

        return function() use ($engine) {
            return $engine->run("asdòkasjdaòlskdjaòlskdjaòlsdkjaòslkdjaòlkdsjaòlksjd");
        };
    };
}

function generateNumberOfPropositionProgression($class, $numOfMaps = 10)
{
    return function($n) use ($class, $numOfMaps)
    {
        $collection = collection($numOfMaps);
        $engine = new $class(collection($numOfMaps));

        for ($i = 0; $i < $n; $i++) {
            if ($i % 3 == 0)
                $engine->addRule(new Rule(prop($collection, 10, 1, INF), $i));
            elseif ($i % 3 == 1)
                $engine->addRule(new Rule(prop($collection, 10, 10, 10), $i));
            else
                $engine->addRule(new Rule(prop($collection, 10, 0, 0), $i));
        }

        return function() use ($engine) {
            return $engine->run("asdòkasjdaòlskdjaòlskdjaòlsdkjaòslkdjaòlkdsjaòlksjd");
        };
    };
}

function addPropsToEngine(EngineInterface $engine, array $props)
{
    foreach($props as $i => $prop) {
        $engine->addRule(new Rule($prop, $i));
    }

    return $engine;
}

/*$groups[] = benchmark(
    $collection = collection(3), [
        (new AndProposition)
            ->addExpression(new Condition("0", "a", $collection))
            ->addExpression(new Condition("0", "s", $collection))
    ],
    [5000],
    "asdasdasdasdasdasdasdasdasdasdasd",
    "Double stupid matching condition"
);*/

/*$groups[] = benchmark(
    $collection = collection(3), [
        (new AndProposition)
            ->addExpression(new Condition("0", "b", $collection))
    ],
    [5000],
    "asdasdasdasdasdasdasdasdasdasdasd",
    "Single stupid not matching condition"
);*/

/*
$collection = collection(3);
$clonedPropositions = [];
$p = (new AndProposition)
    ->addExpression(new Condition("0", "b", $collection));
for ($i = 0; $i < 400; $i++) {
    $clonedPropositions[] = $p;
}

$groups[] = benchmark(
    $collection, $clonedPropositions,
    [1000],
    "asdasdasdasdasdasdasdasdasdasdasd",
    "A lot of cloned propositions"
);
*/

/*$groups[] = benchmark(
    $collection = collection(3), [
        (new OrProposition)
            ->addExpression(new Condition("0", "x", $collection))
            ->addExpression(new Condition("0", "y", $collection))
            ->addExpression(new Condition("0", "z", $collection))
            ->addExpression(new Condition("0", "u", $collection))
            ->addExpression(new Condition("0", "m", $collection))
            ->addExpression(new Condition("0", "n", $collection))
            ->addExpression(new Condition("1", "a", $collection))
            ->addExpression(new Condition("1", "b", $collection))
            ->addExpression(new Condition("1", "v", $collection))
            ->addExpression(new Condition("1", "c", $collection))
            ->addExpression(new Condition("2", "a", $collection))
            ->addExpression(new Condition("2", "b", $collection))
            ->addExpression(new Condition("2", "c", $collection))
            ->addExpression(new Condition("2", "m", $collection))
            ->addExpression(new Condition("0", "a", $collection))
    ],
    [1000],
    "asdasdasdasdasdasdasdasdasdasdasd",
    "Single OR Proposition"
);*/


$groups[] = benchmark(
    $collection = collection(30), [
        (new AndProposition)
            ->addExpression(new Condition("0", "x", $collection))
            ->addExpression(new Condition("1", "s", $collection))
            ->addExpression(new Condition("2", "d", $collection))
            ->addExpression(new Condition("3", "d", $collection))
            ->addExpression(new Condition("4", "d", $collection))
            ->addExpression(new Condition("5", "d", $collection))
            ->addExpression(new Condition("6", "d", $collection))
            ->addExpression(new Condition("7", "d", $collection))
            ->addExpression(new Condition("8", "d", $collection))
            ->addExpression(new Condition("9", "d", $collection))
            ->addExpression(new Condition("10", "d", $collection))
            ->addExpression(new Condition("11", "f", $collection))
            ->addExpression(new Condition("12", "f", $collection))
            ->addExpression(new Condition("13", "f", $collection))
            ->addExpression(new Condition("14", "f", $collection))
            ->addExpression(new Condition("15", "f", $collection))
            ->addExpression(new Condition("16", "f", $collection))
            ->addExpression(new Condition("17", "f", $collection))
            ->addExpression(new Condition("18", "f", $collection))
            ->addExpression(new Condition("19", "f", $collection))
            ->addExpression(new Condition("20", "f", $collection))
            ->addExpression(new Condition("21", "f", $collection))
            ->addExpression(new Condition("22", "f", $collection))
            ->addExpression(new Condition("23", "f", $collection))
    ],
    [10000],
    "asdasdasdasdasdasdasdasdasdasdasd",
    "Single AND AndProposition"
);

$groups[] = benchmark(
    $collection = collection(10), [
        (new NotProposition)
            ->addExpression(new Condition("1", "a", $collection))
            ->addExpression(new Condition("2", "a", $collection))
            ->addExpression(new Condition("0", "a", $collection))
    ],
    [10000],
    "asdasdasdasdasdasdasdasdasdasdasd",
    "Single Not AndProposition"
);

/*$groups[] = benchmark(
    $collection = collection(3),
    [(new OrProposition)
        ->addExpression(new Condition("0", "a", $collection))
        ->addExpression(new Condition("0", "b", $collection))
        ->addExpression(new Condition("0", "c", $collection))
        ->addExpression(new Condition("2", "a", $collection))
        ->addExpression(new Condition("2", "s", $collection)),
    (new AndProposition)
        ->addExpression(new Condition("0", "a", $collection))
        ->addExpression(new Condition("0", "b", $collection))
        ->addExpression(new Condition("0", "c", $collection))
        ->addExpression(new Condition("2", "a", $collection))
        ->addExpression(new Condition("2", "s", $collection))
    ],
    [1000, 10000],
    "asdasdasdasdasdasdasdasdasdasdasd",
    "Single prop, 5 expressions, 2 maps"
);*/

/*$groups[] = benchmark(
    $collection = collection(10),
    [(new OrProposition)
        ->addExpression(new Condition("0", "3", $collection))
        ->addExpression(new Condition("0", "2", $collection))
        ->addExpression(new Condition("0", "1", $collection))
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("2", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection)),
    (new AndProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("3", "3", $collection))
        ->addExpression(new Condition("4", "0", $collection)),
    (new AndProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("3", "4", $collection)),
    (new OrProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("3", "4", $collection))
    ],

    [1000, 10000],
    "01234567890123456789",
    "Single prop, 5 expressions, 2 maps"
);*/

/*$groups[] = benchmark(
    $collection = collection(20),
    [(new OrProposition)
        ->addExpression(new Condition("0", "3", $collection))
        ->addExpression(new Condition("0", "2", $collection))
        ->addExpression(new Condition("0", "1", $collection))
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("2", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("6", "6", $collection))
        ->addExpression(new Condition("7", "3", $collection)),
    (new OrProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("3", "3", $collection))
        ->addExpression(new Condition("3", "3", $collection))
        ->addExpression(new Condition("4", "3", $collection))
        ->addExpression(new Condition("5", "3", $collection))
        ->addExpression(new Condition("6", "3", $collection))
        ->addExpression(new Condition("7", "3", $collection))
        ->addExpression(new Condition("7", "7", $collection))
        ->addExpression(new Condition("7", "83", $collection))
        ->addExpression(new Condition("7", "Q", $collection))
        ->addExpression(new Condition("7", "3", $collection))
        ->addExpression(new Condition("7", "2", $collection))
        ->addExpression(new Condition("4", "0", $collection)),
    (new AndProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("3", "4", $collection))
        ->addExpression(new Condition("3", "5", $collection))
        ->addExpression(new Condition("3", "1", $collection))
        ->addExpression(new Condition("3", "2", $collection))
        ->addExpression(new Condition("3", "3", $collection))
        ->addExpression(new Condition("3", "4", $collection))
        ->addExpression(new Condition("5", "5", $collection))
        ->addExpression(new Condition("5", "6", $collection)),
    (new OrProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("3", "4", $collection))
        ->addExpression(new Condition("3", "5", $collection))
        ->addExpression(new Condition("3", "1", $collection))
        ->addExpression(new Condition("3", "2", $collection))
        ->addExpression(new Condition("3", "3", $collection))
        ->addExpression(new Condition("3", "4", $collection))
        ->addExpression(new Condition("5", "5", $collection))
        ->addExpression(new Condition("5", "6", $collection)),
    (new OrProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("3", "4", $collection))
        ->addExpression(new Condition("3", "5", $collection))
        ->addExpression(new Condition("3", "1", $collection))
        ->addExpression(new Condition("3", "2", $collection))
        ->addExpression(new Condition("3", "3", $collection))
        ->addExpression(new Condition("3", "4", $collection))
        ->addExpression(new Condition("5", "5", $collection))
        ->addExpression(new Condition("5", "6", $collection)),
    (new AndProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("3", "4", $collection)),
    (new OrProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression((new NotProposition)
            ->addExpression(new Condition("0", "4", $collection))
            ->addExpression(new Condition("1", "5", $collection))
            ->addExpression(new Condition("2", "6", $collection))
            ->addExpression(new Condition("3", "3", $collection))
        )
        ->addExpression(new Condition("3", "3", $collection))
        ->addExpression((new AndProposition())
            ->addExpression((new NotProposition)
                ->addExpression(new Condition("0", "3", $collection))
                ->addExpression(new Condition("0", "2", $collection))
            )
            ->addExpression((new NotProposition)
                ->addExpression(new Condition("1", "3", $collection))
                ->addExpression(new Condition("1", "2", $collection))
            )
        )
        ->addExpression(new Condition("5", "5", $collection))
        ->addExpression((new NotProposition)
            ->addExpression(new Condition("1", "4", $collection))
            ->addExpression(new Condition("2", "5", $collection))
            ->addExpression(new Condition("4", "6", $collection))
            ->addExpression(new Condition("5", "3", $collection))
        )
        ->addExpression(new Condition("6", "6", $collection))
        ->addExpression(new Condition("7", "7", $collection))
        ->addExpression(new Condition("8", "8", $collection))
        ->addExpression(new Condition("9", "9", $collection)),
    (new NotProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("3", "3", $collection))
        ->addExpression(new Condition("4", "4", $collection))
        ->addExpression(new Condition("5", "5", $collection))
        ->addExpression(new Condition("6", "6", $collection))
        ->addExpression(new Condition("7", "7", $collection))
        ->addExpression(new Condition("8", "8", $collection))
        ->addExpression(new Condition("9", "9", $collection))
        ->addExpression(new Condition("0", "1", $collection))
        ->addExpression(new Condition("10", "0", $collection)),
    (new OrProposition)
        ->addExpression(new Condition("0", "1", $collection))
        ->addExpression(new Condition("1", "2", $collection))
        ->addExpression(new Condition("2", "3", $collection))
        ->addExpression(new Condition("3", "4", $collection))
        ->addExpression(new Condition("4", "5", $collection))
        ->addExpression(new Condition("5", "6", $collection))
        ->addExpression(new Condition("6", "7", $collection))
        ->addExpression(new Condition("7", "7", $collection))
        ->addExpression(new Condition("8", "8", $collection))
        ->addExpression(new Condition("9", "9", $collection))
        ->addExpression(new Condition("0", "1", $collection))
        ->addExpression(new Condition("10", "0", $collection)),
    ],

    [1000, 10000],
    "01234567890123456789",
    "Nested propositions, with nots"
);

$groups[] = benchmark(
    $collection = collection(20),
    [(new OrProposition)
        ->addExpression(new Condition("0", "3", $collection))
        ->addExpression(new Condition("0", "2", $collection))
        ->addExpression(new Condition("0", "1", $collection))
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("2", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("6", "6", $collection))
        ->addExpression(new Condition("7", "3", $collection)),
    (new OrProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("3", "3", $collection))
        ->addExpression(new Condition("3", "3", $collection))
        ->addExpression(new Condition("4", "3", $collection))
        ->addExpression(new Condition("5", "3", $collection))
        ->addExpression(new Condition("6", "3", $collection))
        ->addExpression(new Condition("7", "3", $collection))
        ->addExpression(new Condition("7", "7", $collection))
        ->addExpression(new Condition("7", "83", $collection))
        ->addExpression(new Condition("7", "Q", $collection))
        ->addExpression(new Condition("7", "3", $collection))
        ->addExpression(new Condition("7", "2", $collection))
        ->addExpression(new Condition("4", "0", $collection)),
    (new AndProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("3", "4", $collection))
        ->addExpression(new Condition("3", "5", $collection))
        ->addExpression(new Condition("3", "1", $collection))
        ->addExpression(new Condition("3", "2", $collection))
        ->addExpression(new Condition("3", "3", $collection))
        ->addExpression(new Condition("3", "4", $collection))
        ->addExpression(new Condition("5", "5", $collection))
        ->addExpression(new Condition("5", "6", $collection)),
    (new OrProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("3", "4", $collection))
        ->addExpression(new Condition("3", "5", $collection))
        ->addExpression(new Condition("3", "1", $collection))
        ->addExpression(new Condition("3", "2", $collection))
        ->addExpression(new Condition("3", "3", $collection))
        ->addExpression(new Condition("3", "4", $collection))
        ->addExpression(new Condition("5", "5", $collection))
        ->addExpression(new Condition("5", "6", $collection)),
    (new OrProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("3", "4", $collection))
        ->addExpression(new Condition("3", "5", $collection))
        ->addExpression(new Condition("3", "1", $collection))
        ->addExpression(new Condition("3", "2", $collection))
        ->addExpression(new Condition("3", "3", $collection))
        ->addExpression(new Condition("3", "4", $collection))
        ->addExpression(new Condition("5", "5", $collection))
        ->addExpression(new Condition("5", "6", $collection)),
    (new AndProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("3", "4", $collection)),
    (new OrProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression((new OrProposition)
            ->addExpression(new Condition("0", "4", $collection))
            ->addExpression(new Condition("1", "5", $collection))
            ->addExpression(new Condition("2", "6", $collection))
            ->addExpression(new Condition("3", "3", $collection))
        )
        ->addExpression(new Condition("3", "3", $collection))
        ->addExpression((new AndProposition())
            ->addExpression((new AndProposition)
                ->addExpression(new Condition("0", "3", $collection))
                ->addExpression(new Condition("0", "2", $collection))
            )
            ->addExpression((new AndProposition)
                ->addExpression(new Condition("1", "3", $collection))
                ->addExpression(new Condition("1", "2", $collection))
            )
        )
        ->addExpression(new Condition("5", "5", $collection))
        ->addExpression((new AndProposition)
            ->addExpression(new Condition("1", "4", $collection))
            ->addExpression(new Condition("2", "5", $collection))
            ->addExpression(new Condition("4", "6", $collection))
            ->addExpression(new Condition("5", "3", $collection))
        )
        ->addExpression(new Condition("6", "6", $collection))
        ->addExpression(new Condition("7", "7", $collection))
        ->addExpression(new Condition("8", "8", $collection))
        ->addExpression(new Condition("9", "9", $collection)),
    (new OrProposition)
        ->addExpression(new Condition("0", "0", $collection))
        ->addExpression(new Condition("1", "1", $collection))
        ->addExpression(new Condition("2", "2", $collection))
        ->addExpression(new Condition("3", "3", $collection))
        ->addExpression(new Condition("4", "4", $collection))
        ->addExpression(new Condition("5", "5", $collection))
        ->addExpression(new Condition("6", "6", $collection))
        ->addExpression(new Condition("7", "7", $collection))
        ->addExpression(new Condition("8", "8", $collection))
        ->addExpression(new Condition("9", "9", $collection))
        ->addExpression(new Condition("0", "1", $collection))
        ->addExpression(new Condition("10", "0", $collection)),
    (new OrProposition)
        ->addExpression(new Condition("0", "1", $collection))
        ->addExpression(new Condition("1", "2", $collection))
        ->addExpression(new Condition("2", "3", $collection))
        ->addExpression(new Condition("3", "4", $collection))
        ->addExpression(new Condition("4", "5", $collection))
        ->addExpression(new Condition("5", "6", $collection))
        ->addExpression(new Condition("6", "7", $collection))
        ->addExpression(new Condition("7", "7", $collection))
        ->addExpression(new Condition("8", "8", $collection))
        ->addExpression(new Condition("9", "9", $collection))
        ->addExpression(new Condition("0", "1", $collection))
        ->addExpression(new Condition("10", "0", $collection)),
    ],

    [1000, 10000],
    "01234567890123456789",
    "Nested propositions, without nots"
);*/

/*$groups[] = benchmark(
    $collection = collection(10),
    [prop($collection, 10, true), prop($collection, 5, true), prop($collection, 3, false), prop($collection, 3, false)],

    [1000, 10000],
    "01234567890123456789120893710928370198237918237981273873872873",
    "Random expressions"
);*/

/*$groups[] = benchmark(
    $collection = collection(100),
    [prop($collection, 10, 1), prop($collection, 5, 5, 5), prop($collection, 20, 1), prop($collection, 3, 0, 0), prop($collection, 15, 1), prop($collection, 20, 5), prop($collection, 50, 1), prop($collection, 50, 1), prop($collection, 50, 1), prop($collection, 100, 1)],

    [1000, 10000],
    "01234567890123456789120893710928370198237918237981273873872873",
    "Random expressions"
);*/

/*$groups[] = benchmark(
    $collection = collection(30),
    [prop($collection, 100, true), prop($collection, 100, false), prop($collection, 20, true), prop($collection, 30, false)],

    [1000],
    "01234567890123456789120893710928370198237918237981273873872873",
    "Random expressions"
);*/

/*$bench = new \Nicmart\Benchmark\VariabeSizeEngine('NumberOfMaps');
$bench
    ->registerFunctional('scan', 'ScanEngine', generateNumberOfMapsProgression(ScanEngine::class), true)
    ->registerFunctional('smart', 'SmartEngine', generateNumberOfMapsProgression(Engine::class), true)
;

$bench->progression(2000, 8, 5);

$groups[] = $bench->getResults();*/


/*$bench = new \Nicmart\Benchmark\VariabeSizeEngine('Number of Propositions');
$bench
    ->registerFunctional('scan', 'ScanEngine', generateNumberOfPropositionProgression(ScanEngine::class), true)
    ->registerFunctional('smart', 'SmartEngine', generateNumberOfPropositionProgression(Engine::class), true)
;

$bench->progression(1000, 8, 5, 2);

$groups[] = $bench->getResults();*/


$template = new \Nicmart\Benchmark\PHPTemplate;
echo $template->render(array('groups' => $groups));
