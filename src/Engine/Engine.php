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
use NicMart\Rulez\Condition\PropositionEvaluationInterface;
use NicMart\Rulez\Condition\PropositionEvaluation;
use NicMart\Rulez\Condition\AndPropositionEvaluation;
use NicMart\Rulez\Condition\OrPropositionEvaluation;
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
        foreach($this->mapsToEvaluations as $mapName => $evaluations)
        {
            $value = $this->maps[$mapName]($x);
            if (isset($this->valuesToEvaluations[$mapName][$value])) {
                /** @var PropositionEvaluationInterface $propEval */
                foreach($this->valuesToEvaluations[$mapName][$value] as $propEval) {
                    if (!$propEval->isResolved()) {
                        $propEval->signalMatch();
                    }
                }
            }

            /** @var PropositionEvaluationInterface $propEval */
            foreach ($evaluations as $propEval) {
                if (!$propEval->isResolved())
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
     * @param PropositionEvaluationInterface $eval
     */
    function ruleResolved($resolvedStatus, Rule $rule, PropositionEvaluationInterface $eval)
    {
        if ($resolvedStatus)
            $this->matches->attach($rule);
    }

    private function registerPropEvalInValuesMap(PropositionEvaluationInterface $propEval, $mapName, $value)
    {
        if (!isset($this->valuesToEvaluations[$mapName][$value]))
            $this->valuesToEvaluations[$mapName][$value] = new \SplObjectStorage;

        $this->valuesToEvaluations[$mapName][$value]->attach($propEval);

        return $this;
    }

    private function registerPropEvalInRulesMap(PropositionEvaluationInterface $propEval, $mapName)
    {
        if (!isset($this->mapsToEvaluations[$mapName]))
            $this->mapsToEvaluations[$mapName] = new \SplObjectStorage;

        $this->mapsToEvaluations[$mapName]->attach($propEval);

        return $this;
    }

    private function createEvaluationFromRule(Rule $rule)
    {
        $proposition = $rule->proposition();
        $callback = function($state, $eval) use ($rule) {
            $this->ruleResolved($state, $rule, $eval);
        };

        if ($proposition->atLeast() >= $proposition->numOfMaps()) {
            return new AndPropositionEvaluation($proposition->numOfMaps(), $callback);
        }

        if ($proposition->atLeast() == 1 && count($proposition->conditions()) <= $proposition->atMost()) {
            return new OrPropositionEvaluation($proposition->numOfMaps(), $callback);
        }

        return new PropositionEvaluation(
            $proposition->numOfMaps(),
            $proposition->atLeast(),
            $proposition->atMost(),
            $callback
        );
    }

    private function reset()
    {
        $this->matches = new \SplObjectStorage;

        /** @var PropositionEvaluationInterface $propEval */
        foreach($this->propositionsEvals as $propEval)
        {
            $propEval->reset();
        }

        return $this;
    }
}