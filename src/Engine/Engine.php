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


use NicMart\Rulez\Evaluation\PositivePropositionEvaluation;
use NicMart\Rulez\Evaluation\PropositionEvaluation;
use NicMart\Rulez\Expression\AndProposition;
use NicMart\Rulez\Expression\CompositeExpression;
use NicMart\Rulez\Expression\Condition;
use NicMart\Rulez\Expression\Expression;
use NicMart\Rulez\Expression\OrProposition;
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
    private $activeMaps = [];

    /**
     * @var PropositionEvaluation[]
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
        $this->createAndIndexEvaluation($rule->expression(), $this->callbackForRule($rule));

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
                /** @var PropositionEvaluation $propEval */
                foreach($this->mapsValuesToEvals[$mapName][$value] as $propEval) {
                    if (!$propEval->isResolved()) {
                        $propEval->input(true);
                    }
                }
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

    private function createAndIndexEvaluation(Expression $expression, $callback = null)
    {
        if (!$expression instanceof CompositeExpression)
            return $this->createAndIndexEvaluation(new AndProposition([$expression]), $callback);

        $atLeast = $expression instanceof OrProposition ? 1 : count($expression->expressions());
        $eval = new PositivePropositionEvaluation($atLeast, $callback);
        $this->propositionsEvals[$this->propositionHash($expression)] = $eval;

        foreach ($expression->expressions() as $subExpression) {
            if ($subExpression instanceof Condition) {
                $mapName = $subExpression->getMapName();
                $mapValue = $subExpression->getValue();
                $this->mapsValuesToEvals[$mapName][$mapValue][$this->propositionHash($expression)] = $eval;
                $this->activeMaps[$mapName] = $this->maps[$mapName];
            } else {
                $subEval = $this->createAndIndexEvaluation($subExpression);
                $subEval->addChild($eval);
            }
        }

        return $eval;
    }

    private function callbackForRule(Rule $rule)
    {
        return function($state, $eval) use ($rule) {
            $this->ruleResolved($state, $rule, $eval);
        };
    }

    private function reset()
    {
        #$this->matches->removeAllExcept(new \SplObjectStorage);
        $this->matches = new \SplObjectStorage;

        /** @var PropositionEvaluation $propEval */
        foreach ($this->propositionsEvals as $propEval) {
            $propEval->reset();
        }

        return $this;
    }

    private function propositionHash(Expression $expression)
    {
        $type = get_class($expression);

        if ($expression instanceof Condition)
            $value = "{$expression->getMapName()}:{$expression->getValue()}";
        elseif ($expression instanceof CompositeExpression) {
            $subHashes = [];
            foreach ($expression->expressions() as $subExpression)
                $subHashes[] = $this->propositionHash($subExpression);
            sort($subHashes);
            $value = implode(":", $subHashes);
        } else {
            throw new \Exception("Unsupported expression");
        }

        return md5("$type:$value");
    }
}