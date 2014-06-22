<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

use NicMart\Arrayze\ArrayAdapterInterface;
use NicMart\Rulez\Expression\Expression;
use \NicMart\Rulez\Expression\CompositeExpression;
use \NicMart\Rulez\Expression\Condition;
use \NicMart\Rulez\Expression\AndProposition;
use \NicMart\Rulez\Expression\OrProposition;
use \NicMart\Rulez\Expression\NotProposition;
use NicMart\Arrayze\MapsCollection;

include "../vendor/autoload.php";

ini_set('xdebug.var_display_max_depth', '10');

/**
 * @return callable
 */
function randomArgument(/**, **/)
{
    $args = func_get_args();

    /**
     * @return CompositeExpression
     */
    return function() use($args) {
        return clone $args[array_rand($args)];
    };
}

function buildProposition(CompositeExpression $expr, callable $childrenGenerator, $level = 3)
{
    /** @var $child Expression */
    foreach ($childrenGenerator($level) as $child) {
        $expr->addExpression($child);

        if ($level > 0)
            buildProposition($child, $childrenGenerator, $level - 1);
    }
}

/**
 * @param callable $propGenerator
 * @param ArrayAdapterInterface $condGenerator
 * @param int $numOfChildProps
 * @param int $numOfLeafExpr
 *
 * @return callable
 */
function getChildrenGenerator(callable $propGenerator, ArrayAdapterInterface $condGenerator, $numOfChildProps, $numOfLeafExpr)
{
    return function($level) use($propGenerator, $condGenerator, $numOfChildProps, $numOfLeafExpr)
    {
        if ($level > 0) {
            return listOfProps($propGenerator, $numOfChildProps);
        }

        return listOfConditions($condGenerator, $numOfLeafExpr);
    };
}

/**
 * @param callable $propGenerator
 * @param int $n
 *
 * @return CompositeExpression[]
 */
function listOfProps(callable $propGenerator, $n)
{
    $props = [];
    for ($i = 0; $i < $n; $i++) {
        $props[] = $propGenerator();
    }

    return $props;
}

/**
 * @param ArrayAdapterInterface $adaptedAry
 * @param int $n
 * @param bool $allowRepeats
 *
 * @return Condition[]
 */
function listOfConditions(ArrayAdapterInterface $adaptedAry, $n, $allowRepeats = true)
{
    $conditions = [];

    foreach (randomKeys($adaptedAry, $n, $allowRepeats) as $key) {
        $conditions[] = new Condition($key, $adaptedAry[$key]);
    }

    return $conditions;
}

/**
 * @param ArrayAdapterInterface $adaptedAry
 * @param int $n
 * @param bool $allowRepeats
 * @return string[]
 */
function randomKeys(ArrayAdapterInterface $adaptedAry, $n, $allowRepeats = true)
{
    $ary = $adaptedAry->toArray();

    if (!$allowRepeats) {
        return (array) array_rand($ary, $n);
    }

    $keys = [];
    for ($i = 0; $i < $n; $i++) {
        $keys[] = array_rand($ary);
    }

    return $keys;
}

function randomIntGenerator($min, $max)
{
    return function() use($min, $max)
    {
        return rand($min, $max);
    };
}

function generateValueGenerator($numOfMaps, $codomainCardinality)
{
    $maps = new MapsCollection;
    for ($i = 0; $i < $numOfMaps; $i++) {
        $maps->registerMap((string) $i, randomIntGenerator(0, $codomainCardinality));
    }

    return new \NicMart\Arrayze\ArrayAdapter('', $maps);
}

$propositionGenerator = randomArgument(new AndProposition, new OrProposition, new NotProposition);

$adapted = generateValueGenerator(5, 10);

buildProposition($root = $propositionGenerator(), getChildrenGenerator($propositionGenerator, $adapted, 3, 4), 5);

//echo (string) $root;

function generateSetOfRulesAndInput(
    callable $propositionGenerator,
    $numOfRules,
    $propositionDepth,
    $numOfSubPropositionsPerProposition,
    $numOfLeafConditions,
    $numOfObjectKeys,
    $numOfObjectValuesPerKey
) {
    static $cache = [];

    $args = func_get_args();
    array_shift($args);
    $key = serialize($args);

    if (isset($cache[$key]))
        return $cache[$key];

    $rules = [];
    $adapted = generateValueGenerator($numOfObjectKeys, $numOfObjectValuesPerKey);
    $childrenGenerator = getChildrenGenerator(
        $propositionGenerator,
        $adapted,
        $numOfSubPropositionsPerProposition,
        $numOfLeafConditions
    );

    for ($i = 0; $i < $numOfRules; $i++) {
        $prop = $propositionGenerator();

        buildProposition($prop, $childrenGenerator, $propositionDepth);

        $rules[] = new \NicMart\Rulez\Engine\Rule($prop, (string) $i);
    }

    return $cache[$key] = [$rules, $adapted->toArray()];
}

/*$rules = generateSetOfRules(
    $propositionGenerator,
    10, // Num of rules
    1,  // Prop depth
    4,  // Num of subpropositions
    5, // Leaf conditions
    5,  // Object fields
    13  // Object values per field
);

var_dump($rules);

echo implode("\r\n\r\n ", $rules);

$rules = generateSetOfRules(
    $propositionGenerator,
    10, // Num of rules
    1,  // Prop depth
    4,  // Num of subpropositions
    5, // Leaf conditions
    5,  // Object fields
    13  // Object values per field
);

echo implode("\r\n\r\n ", $rules);*/