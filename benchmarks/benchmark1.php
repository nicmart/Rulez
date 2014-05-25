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

function prop($numOfMaps, $numConditions, $atLeast, $atMost = INF)
{
    $prop = new AndProposition($atLeast, $atMost);

    for ($i = 0; $i < $numConditions; $i++) {
        $mapName = (string) rand(0, $numOfMaps - 1);
        $condValue = (string) rand(0, 9);

        $prop->addExpression(new Condition($mapName, $condValue));
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
        $engine->setMapsCollection(collection($n));

        addPropsToEngine($engine, [
            prop($n, 1, 1, INF),
            prop($n, 10, 1, INF),
            prop($n, 10, 10, INF),
            prop($n, 5, 0, 0),
            prop($n, 20, 20, INF),
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
        $engine = new $class(collection($numOfMaps));

        for ($i = 0; $i < $n; $i++) {
            if ($i % 3 == 0)
                $engine->addRule(new Rule(prop($numOfMaps, 10, 1, INF), $i));
            elseif ($i % 3 == 1)
                $engine->addRule(new Rule(prop($numOfMaps, 10, 10, 10), $i));
            else
                $engine->addRule(new Rule(prop($numOfMaps, 10, 0, 0), $i));
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
    collection(3), [
        (new AndProposition(1))
            ->addExpression(new Condition("0", "s"))
            //->addExpression(new Condition("0", "a"))
    ],
    [10000],
    "asdasdasdasdasdasdasdasdasdasdasd",
    "Double stupid matching condition"
);*/

$groups[] = benchmark(
    collection(3), [
        (new AndProposition(1))
            ->addExpression(new Condition("0", "b"))
    ],
    [5000],
    "asdasdasdasdasdasdasdasdasdasdasd",
    "Single stupid not matching condition"
);

$clonedPropositions = [];
$p = (new AndProposition(1))
        ->addExpression(new Condition("0", "b"));
for ($i = 0; $i < 400; $i++) {
    $clonedPropositions[] = $p;
}

$groups[] = benchmark(
    collection(3), $clonedPropositions,
    [5000],
    "asdasdasdasdasdasdasdasdasdasdasd",
    "A lot of cloned propositions"
);

$groups[] = benchmark(
    collection(3), [
        (new AndProposition(1))
            ->addExpression(new Condition("0", "x"))
            ->addExpression(new Condition("0", "y"))
            ->addExpression(new Condition("0", "z"))
            ->addExpression(new Condition("0", "u"))
            ->addExpression(new Condition("0", "m"))
            ->addExpression(new Condition("0", "n"))
            ->addExpression(new Condition("1", "a"))
            ->addExpression(new Condition("1", "b"))
            ->addExpression(new Condition("1", "v"))
            ->addExpression(new Condition("1", "c"))
            ->addExpression(new Condition("2", "a"))
            ->addExpression(new Condition("2", "b"))
            ->addExpression(new Condition("2", "c"))
            ->addExpression(new Condition("2", "m"))
            ->addExpression(new Condition("0", "a"))
    ],
    [10000],
    "asdasdasdasdasdasdasdasdasdasdasd",
    "Single OR AndProposition"
);

$groups[] = benchmark(
    collection(10), [
        (new AndProposition(3, 3))
            ->addExpression(new Condition("0", "a"))
            ->addExpression(new Condition("1", "s"))
            ->addExpression(new Condition("2", "d"))
    ],
    [10000],
    "asdasdasdasdasdasdasdasdasdasdasd",
    "Single AND AndProposition"
);

/*$groups[] = benchmark(
    $collection,
    [(new AndProposition(1))
        ->addExpression(new Condition("0", "a"))
        ->addExpression(new Condition("0", "b"))
        ->addExpression(new Condition("0", "c"))
        ->addExpression(new Condition("2", "a"))
        ->addExpression(new Condition("2", "s")),
    (new AndProposition(5, 5))
        ->addExpression(new Condition("0", "a"))
        ->addExpression(new Condition("0", "b"))
        ->addExpression(new Condition("0", "c"))
        ->addExpression(new Condition("2", "a"))
        ->addExpression(new Condition("2", "s"))
    ],
    [1000, 10000],
    "asdasdasdasdasdasdasdasdasdasdasd",
    "Single prop, 5 expressions, 2 maps"
);*/

/*$groups[] = benchmark(
    $collection,
    [(new AndProposition(1))
        ->addExpression(new Condition("0", "3"))
        ->addExpression(new Condition("0", "2"))
        ->addExpression(new Condition("0", "1"))
        ->addExpression(new Condition("0", "0"))
        ->addExpression(new Condition("2", "1"))
        ->addExpression(new Condition("2", "2")),
    (new AndProposition(0, 0))
        ->addExpression(new Condition("0", "0"))
        ->addExpression(new Condition("1", "1"))
        ->addExpression(new Condition("2", "2"))
        ->addExpression(new Condition("3", "3"))
        ->addExpression(new Condition("4", "0")),
    (new AndProposition(5, 5))
        ->addExpression(new Condition("0", "0"))
        ->addExpression(new Condition("1", "1"))
        ->addExpression(new Condition("2", "2"))
        ->addExpression(new Condition("3", "4")),
    (new AndProposition(2, 3))
        ->addExpression(new Condition("0", "0"))
        ->addExpression(new Condition("1", "1"))
        ->addExpression(new Condition("2", "2"))
        ->addExpression(new Condition("3", "4"))
    ],

    [1000, 10000],
    "01234567890123456789",
    "Single prop, 5 expressions, 2 maps"
);*/

/*$groups[] = benchmark(
    collection(20),
    [(new AndProposition(1))
        ->addExpression(new Condition("0", "3"))
        ->addExpression(new Condition("0", "2"))
        ->addExpression(new Condition("0", "1"))
        ->addExpression(new Condition("0", "0"))
        ->addExpression(new Condition("2", "1"))
        ->addExpression(new Condition("2", "2"))
        ->addExpression(new Condition("2", "2"))
        ->addExpression(new Condition("6", "6"))
        ->addExpression(new Condition("7", "3")),
    (new AndProposition(0, 0))
        ->addExpression(new Condition("0", "0"))
        ->addExpression(new Condition("1", "1"))
        ->addExpression(new Condition("2", "2"))
        ->addExpression(new Condition("3", "3"))
        ->addExpression(new Condition("3", "3"))
        ->addExpression(new Condition("4", "3"))
        ->addExpression(new Condition("5", "3"))
        ->addExpression(new Condition("6", "3"))
        ->addExpression(new Condition("7", "3"))
        ->addExpression(new Condition("7", "7"))
        ->addExpression(new Condition("7", "83"))
        ->addExpression(new Condition("7", "Q"))
        ->addExpression(new Condition("7", "3"))
        ->addExpression(new Condition("7", "2"))
        ->addExpression(new Condition("4", "0")),
    (new AndProposition(11, 11))
        ->addExpression(new Condition("0", "0"))
        ->addExpression(new Condition("1", "1"))
        ->addExpression(new Condition("2", "2"))
        ->addExpression(new Condition("3", "4"))
        ->addExpression(new Condition("3", "5"))
        ->addExpression(new Condition("3", "1"))
        ->addExpression(new Condition("3", "2"))
        ->addExpression(new Condition("3", "3"))
        ->addExpression(new Condition("3", "4"))
        ->addExpression(new Condition("5", "5"))
        ->addExpression(new Condition("5", "6")),
    (new AndProposition(1, 2))
        ->addExpression(new Condition("0", "0"))
        ->addExpression(new Condition("1", "1"))
        ->addExpression(new Condition("2", "2"))
        ->addExpression(new Condition("3", "4"))
        ->addExpression(new Condition("3", "5"))
        ->addExpression(new Condition("3", "1"))
        ->addExpression(new Condition("3", "2"))
        ->addExpression(new Condition("3", "3"))
        ->addExpression(new Condition("3", "4"))
        ->addExpression(new Condition("5", "5"))
        ->addExpression(new Condition("5", "6")),
    (new AndProposition(1))
        ->addExpression(new Condition("0", "0"))
        ->addExpression(new Condition("1", "1"))
        ->addExpression(new Condition("2", "2"))
        ->addExpression(new Condition("3", "4"))
        ->addExpression(new Condition("3", "5"))
        ->addExpression(new Condition("3", "1"))
        ->addExpression(new Condition("3", "2"))
        ->addExpression(new Condition("3", "3"))
        ->addExpression(new Condition("3", "4"))
        ->addExpression(new Condition("5", "5"))
        ->addExpression(new Condition("5", "6")),
    (new AndProposition(2, 3))
        ->addExpression(new Condition("0", "0"))
        ->addExpression(new Condition("1", "1"))
        ->addExpression(new Condition("2", "2"))
        ->addExpression(new Condition("3", "4")),
    (new AndProposition(10, 10))
        ->addExpression(new Condition("0", "0"))
        ->addExpression(new Condition("1", "1"))
        ->addExpression(new Condition("2", "2"))
        ->addExpression(new Condition("3", "3"))
        ->addExpression(new Condition("4", "4"))
        ->addExpression(new Condition("5", "5"))
        ->addExpression(new Condition("6", "6"))
        ->addExpression(new Condition("7", "7"))
        ->addExpression(new Condition("8", "8"))
        ->addExpression(new Condition("9", "9")),
    (new AndProposition(11, 11))
        ->addExpression(new Condition("0", "0"))
        ->addExpression(new Condition("1", "1"))
        ->addExpression(new Condition("2", "2"))
        ->addExpression(new Condition("3", "3"))
        ->addExpression(new Condition("4", "4"))
        ->addExpression(new Condition("5", "5"))
        ->addExpression(new Condition("6", "6"))
        ->addExpression(new Condition("7", "7"))
        ->addExpression(new Condition("8", "8"))
        ->addExpression(new Condition("9", "9"))
        ->addExpression(new Condition("0", "1"))
        ->addExpression(new Condition("10", "0")),
    (new AndProposition(1))
        ->addExpression(new Condition("0", "1"))
        ->addExpression(new Condition("1", "2"))
        ->addExpression(new Condition("2", "3"))
        ->addExpression(new Condition("3", "4"))
        ->addExpression(new Condition("4", "5"))
        ->addExpression(new Condition("5", "6"))
        ->addExpression(new Condition("6", "7"))
        ->addExpression(new Condition("7", "7"))
        ->addExpression(new Condition("8", "8"))
        ->addExpression(new Condition("9", "9"))
        ->addExpression(new Condition("0", "1"))
        ->addExpression(new Condition("10", "0")),
    ],

    [1000, 10000],
    "01234567890123456789",
    "Single prop, 5 expressions, 2 maps"
);*/

$groups[] = benchmark(
    collection(10),
    [prop(10, 10, 1), prop(10, 5, 1), prop(10, 3, 3, 3), prop(10, 3, 0, 0)],

    [1000, 10000],
    "01234567890123456789120893710928370198237918237981273873872873",
    "Random expressions"
);

$groups[] = benchmark(
    collection(20),
    [prop(20, 10, 1), prop(20, 5, 5, 5), prop(20, 20, 1), prop(20, 3, 0, 0), prop(20, 15, 1), prop(20, 20, 5), prop(20, 50, 1), prop(20, 50, 1), prop(20, 50, 1), prop(20, 100, 1)],

    [1000, 10000],
    "01234567890123456789120893710928370198237918237981273873872873",
    "Random expressions"
);

$groups[] = benchmark(
    collection(30),
    [prop(30, 100, 20), prop(30, 100, 20, 30), prop(30, 20, 1), prop(30, 30, 30)],

    [1000],
    "01234567890123456789120893710928370198237918237981273873872873",
    "Random expressions"
);

/*$bench = new \Nicmart\Benchmark\VariabeSizeEngine('NumberOfMaps');
$bench
    ->registerFunctional('scan', 'ScanEngine', generateNumberOfMapsProgression(ScanEngine::class), true)
    ->registerFunctional('smart', 'SmartEngine', generateNumberOfMapsProgression(Engine::class), true)
;

$bench->progression(2000, 8, 5);

$groups[] = $bench->getResults();
*/
$bench = new \Nicmart\Benchmark\VariabeSizeEngine('Number of Propositions');
$bench
    ->registerFunctional('scan', 'ScanEngine', generateNumberOfPropositionProgression(ScanEngine::class), true)
    ->registerFunctional('smart', 'SmartEngine', generateNumberOfPropositionProgression(Engine::class), true)
;

$bench->progression(5000, 4, 5);

$groups[] = $bench->getResults();

$template = new \Nicmart\Benchmark\PHPTemplate;
echo $template->render(array('groups' => $groups));
