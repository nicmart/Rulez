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
    private $valuesToEvaluations = [];

    /**
     * @var array
     */
    private $mapsToEvaluations = [];

    /**
     * @var Rule[]
     */
    private $rules = [];

    /**
     * @param MapsCollection $maps
     */
    function __construct(MapsCollection $maps)
    {
        $this->maps = $maps;
    }

    /**
     * @param Rule $rule
     *
     * @return $this
     */
    function addRule(Rule $rule)
    {
        $eval = $this->createEvaluationFromRule($rule);
        foreach ($rule->proposition()->conditions() as $condition) {
            $this
                ->registerPropEvalInValuesMap($eval, $condition->getMapName(), $condition->getValue())
                ->registerRuleInRulesMap($eval, $condition->getMapName())
            ;
        }

        return $this;
    }

    function run($x)
    {
        foreach($this->maps as $mapName => $map)
        {
            if (!isset($this->mapsToEvaluations[$mapName]))
                continue;

            $value = $map($x);
            if (isset($this->valuesToEvaluations[$mapName][$value])) {
                /** @var PropositionEvaluation $propEval */
                foreach($this->valuesToEvaluations[$mapName][$value] as $propEval) {
                    if (!$propEval->isResolved())
                        $propEval->signalMatch();
                }
            }

            
        }
    }

    /**
     * @param bool $resolvedStatus
     * @param Rule $rule
     */
    function ruleResolved($resolvedStatus, Rule $rule)
    {

    }

    private function registerPropEvalInValuesMap(PropositionEvaluation $propEval, $mapName, $value)
    {
        if (!isset($this->valuesToEvaluations[$mapName][$value]))
            $this->valuesToEvaluations[$mapName][$value] = new \SplObjectStorage;

        $this->valuesToEvaluations[$mapName][$value]->attach($propEval);

        return $this;
    }

    private function registerRuleInRulesMap(PropositionEvaluation $propEval, $mapName)
    {
        if (!isset($this->mapsToEvaluations[$mapName]))
            $this->mapsToEvaluations[$mapName] = new \SplObjectStorage;

        $this->mapsToEvaluations[$mapName]->attach($propEval);

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