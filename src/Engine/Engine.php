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


use NicMart\Rulez\Expression\PositiveOnlyPropositionEvaluation;
use NicMart\Rulez\Expression\AndProposition;
use NicMart\Rulez\Expression\PropositionEvaluationInterface;
use NicMart\Rulez\Expression\PropositionEvaluation;
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
    private $mapsValuesToEvals = [];

    /**
     * @var array
     */
    private $mapsToNegativeAwareEvals = [];

    /**
     * @var array
     */
    private $activeMaps = [];

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

        foreach ($rule->proposition()->expressions() as $condition) {
            $mapName = $condition->getMapName();

            if (!isset($this->activeMaps[$mapName]))
                $this->activeMaps[$mapName] = $this->maps[$mapName];

            $this->indexEvaluation($eval, $condition->getMapName(), $condition->getValue());

        }

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    function run($x)
    {
        foreach($this->activeMaps as $mapName => $map)
        {
            $value = $map($x);
            if (isset($this->mapsValuesToEvals[$mapName][$value])) {
                /** @var PropositionEvaluationInterface $propEval */
                foreach($this->mapsValuesToEvals[$mapName][$value] as $propEval) {
                    if (!$propEval->isResolved()) {
                        $propEval->signalMatch();
                    }
                }
            }

            /** @var PropositionEvaluationInterface $propEval */
            foreach ($this->mapsToNegativeAwareEvals[$mapName] as $propEval) {
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

    private function indexEvaluation(PropositionEvaluationInterface $propEval, $mapName, $value)
    {
        if (!isset($this->mapsValuesToEvals[$mapName][$value]))
            $this->mapsValuesToEvals[$mapName][$value] = new \SplObjectStorage;

        $this->mapsValuesToEvals[$mapName][$value]->attach($propEval);

        if (!isset($this->mapsToNegativeAwareEvals[$mapName]))
            $this->mapsToNegativeAwareEvals[$mapName] = new \SplObjectStorage;

        if (!$propEval instanceof PositiveOnlyPropositionEvaluation)
            $this->mapsToNegativeAwareEvals[$mapName]->attach($propEval);

        return $this;
    }

    private function createEvaluationFromRule(Rule $rule)
    {
        $proposition = $rule->proposition();
        $callback = function($state, $eval) use ($rule) {
            $this->ruleResolved($state, $rule, $eval);
        };

        if ($proposition->atLeast() > 0 && count($proposition->expressions()) <= $proposition->atMost()) {
            return new PositiveOnlyPropositionEvaluation($proposition->atLeast(), $callback);
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
        #$this->matches->removeAllExcept(new \SplObjectStorage);
        $this->matches = new \SplObjectStorage;

        /** @var PropositionEvaluationInterface $propEval */
        foreach ($this->propositionsEvals as $propEval) {
            $propEval->reset();
        }

        return $this;
    }
}