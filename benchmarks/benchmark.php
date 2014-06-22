<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

include 'engineGenerator.php';

ini_set('xdebug.var_display_max_depth', '10');
ini_set('max_execution_time', '120');

use NicMart\Rulez\Expression\AndProposition;
use NicMart\Rulez\Expression\OrProposition;
use NicMart\Rulez\Expression\NotProposition;
use NicMart\Rulez\Engine\ScanEngine;
use NicMart\Rulez\Engine\Engine;

function progressionFromRulesGenerator($class, callable $ruleAndInputGenerator)
{
    return function($n) use ($class, $ruleAndInputGenerator)
    {
        /** @var \NicMart\Rulez\Engine\EngineInterface $engine */
        $engine = new $class;

        list($rules, $input) = $ruleAndInputGenerator($n);

        foreach($rules as $rule) {
            $engine->addRule($rule);
        }

        return function() use($engine, $input)
        {
            $engine->run($input);
        };
    };
}

$numOfRules = function($n)
{
    return generateSetOfRulesAndInput(
        randomArgument(new OrProposition, new AndProposition),
        5,  //Num of rules
        3,   //Prop depth
        4,  //numOfSubPropositionsPerProposition
        $n,   //$numOfLeafConditions
        30,  //Num of object Keys
        30  // Num of values
    );
};

$bench = new \Nicmart\Benchmark\VariabeSizeEngine('Test');
$bench
    ->registerFunctional('scan', 'ScanEngine', progressionFromRulesGenerator(ScanEngine::class, $numOfRules), true)
    ->registerFunctional('smart', 'SmartEngine', progressionFromRulesGenerator(Engine::class, $numOfRules), true)
;

$bench->progression(1000, 32, 2, 4);

$groups[] = $bench->getResults();

$template = new \Nicmart\Benchmark\PHPTemplate;
echo $template->render(array('groups' => $groups));