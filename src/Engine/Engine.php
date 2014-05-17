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

class Engine implements EngineInterface
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
     * @var \SplObjectStorage[]
     */
    private $propositionsEvals;

    /**
     * @var \SplObjectStorage
     */
    private $matches;

    /**
     * @param MapsCollection $maps
     */
    function __construct(MapsCollection $maps)
    {
        $this->setMapsCollection($maps);
        $this->matches = new \SplObjectStorage;
        $this->propositionsEvals = new \SplObjectStorage;
    }

    /**
     * {@inheritdoc}
     */
    function setMapsCollection(MapsCollection $maps)
    {
        $this->maps = $maps;

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    function addRule(Rule $rule)
    {
        $eval = $this->createEvaluationFromRule($rule);
        $this->propositionsEvals->attach($eval);

        foreach ($rule->proposition()->conditions() as $condition) {
            $this
                ->registerPropEvalInValuesMap($eval, $condition->getMapName(), $condition->getValue())
                ->registerPropEvalInRulesMap($eval, $condition->getMapName())
            ;
        }

        return $this;
    }


    /**
     * {@inheritdoc}
     */
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

            /** @var PropositionEvaluation $propEval */
            foreach ($this->mapsToEvaluations[$mapName] as $propEval) {
                $propEval->signalMapUsed();
            }
        }

        $matches = $this->matches;

        $this->reset();

        return $matches;
    }

    /**
     * @param bool $resolvedStatus
     * @param Rule $rule
     * @param PropositionEvaluation $eval
     */
    function ruleResolved($resolvedStatus, Rule $rule, PropositionEvaluation $eval)
    {
        if ($resolvedStatus)
            $this->matches->attach($rule);
    }

    private function registerPropEvalInValuesMap(PropositionEvaluation $propEval, $mapName, $value)
    {
        if (!isset($this->valuesToEvaluations[$mapName][$value]))
            $this->valuesToEvaluations[$mapName][$value] = new \SplObjectStorage;

        $this->valuesToEvaluations[$mapName][$value]->attach($propEval);

        return $this;
    }

    private function registerPropEvalInRulesMap(PropositionEvaluation $propEval, $mapName)
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
            function($state, $eval) use ($rule) {
                $this->ruleResolved($state, $rule, $eval);
            }
        );
    }

    private function reset()
    {
        $this->matches = new \SplObjectStorage;

        /** @var PropositionEvaluation $propEval */
        foreach($this->propositionsEvals as $propEval)
        {
            $propEval->reset();
        }

        return $this;
    }
} 