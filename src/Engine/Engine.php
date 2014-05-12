<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Engine;


use NicMart\Rulez\Condition\Proposition;
use NicMart\Rulez\Condition\PropositionEvaluation;
use NicMart\Rulez\Maps\MapsCollection;

class Engine
{
    /**
     * @var MapsCollection
     */
    private $maps;

    /**
     * @var array
     */
    private $mapsValuesToRules = [];

    /**
     * @var array
     */
    private $mapsToRules = [];

    /**
     * @var Rule[]
     */
    private $rules = [];

    /**
     * @param Rule $rule
     *
     * @return $this
     */
    function addRule(Rule $rule)
    {
        foreach ($rule->proposition()->conditions() as $condition) {
            $this
                ->registerRuleInValuesMap($rule, $condition->getMapName(), $condition->getValue())
                ->registerRuleInRulesMap($rule, $condition->getMapName())
            ;

        }

        return $this;
    }

    /**
     * @param bool $resolvedStatus
     * @param Rule $rule
     */
    function ruleResolved($resolvedStatus, Rule $rule)
    {

    }

    private function registerRuleInValuesMap(Rule $rule, $mapName, $value)
    {
        if (!isset($this->mapsValuesToRules[$mapName][$value]))
            $this->mapsValuesToRules[$mapName][$value] = new \SplObjectStorage;

        $this->mapsValuesToRules[$mapName][$value]->attach($rule);

        return $this;
    }

    private function registerRuleInRulesMap(Rule $rule, $mapName)
    {
        if (!isset($this->mapsToRules[$mapName]))
            $this->mapsToRules[$mapName] = new \SplObjectStorage;

        $this->mapsToRules[$mapName]->attach($rule);

        return $this;
    }

    private function createEvaluationFromRule(Rule $rule)
    {
        $proposition = $rule->proposition();

        return new PropositionEvaluation(
            $proposition->numOfMaps(),
            $proposition->atLeast(),
            $proposition->atMost(),
            function($state) use ($rule) {
                $this->ruleResolved($state, $rule);
            }
        );
    }
} 