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


use NicMart\Rulez\Evaluation\NegativePropositionEvaluation;
use NicMart\Rulez\Evaluation\PositivePropositionEvaluation;
use NicMart\Rulez\Evaluation\PropositionEvaluation;
use NicMart\Rulez\Expression\AndProposition;
use NicMart\Rulez\Expression\CompositeExpression;
use NicMart\Rulez\Expression\Condition;
use NicMart\Rulez\Expression\Expression;
use NicMart\Rulez\Expression\NotProposition;
use NicMart\Rulez\Expression\OrProposition;

class Engine implements EngineInterface
{
    /**
     * @var array
     */
    private $keysValuesEvals = [];

    /**
     * @var array
     */
    private $keys = [];

    /**
     * @var PropositionEvaluation[]
     */
    private $propositionsEvals;

    /**
     * @var \SplObjectStorage
     */
    private $matches;

    /**
     * @var PropositionEvaluation[][]
     */
    private $negativeEvaluationIndex = [];

    private $evalsToReset = [];

    /**
     */
    function __construct()
    {
        $this->matches = new \SplObjectStorage;
        $this->evalsToReset = new \SplObjectStorage;
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
        foreach($this->keys as $key => $_) {
            $value = $x[$key];
            if (isset($this->keysValuesEvals[$key][$value])) {
                /** @var PropositionEvaluation $propEval */
                foreach($this->keysValuesEvals[$key][$value] as $propEval) {
                    if (!$propEval->isResolved()) {
                        $propEval->input(true);
                    }
                }
            }
        }

        foreach($this->negativeEvaluationIndex as $evaluations) {
            /** @var $evaluation PropositionEvaluation */
            foreach ($evaluations as $evaluation) {
                if(!$evaluation->isResolved())
                    $evaluation->resolve(true);
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

    private function createAndIndexEvaluation(Expression $expression, $callback = null, &$negativeLevel = 0)
    {
        if (!$expression instanceof CompositeExpression)
            return $this->createAndIndexEvaluation(new AndProposition([$expression]), $callback);

        $hash = $this->propositionHash($expression);

        $eval = isset($this->propositionsEvals[$hash])
            ? $this->propositionsEvals[$hash]
            : $this->propositionsEvals[$hash] = $this->evaluationFromProposition($expression)
        ;

        if ($callback)
            $eval->onResolved($callback);

        $maxNegativeLevelOfChildren = 0;

        foreach ($expression->expressions() as $subExpression) {
            if ($subExpression instanceof Condition) {
                $key = $subExpression->getKey();
                $value = $subExpression->getValue();
                $this->keysValuesEvals[$key][$value][$hash] = $eval;
                $this->keys[$key] = true;
            } else {
                $childLevel = 0;
                $subEval = $this->createAndIndexEvaluation($subExpression, $childLevel);
                $subEval->addChild($eval);
                if ($childLevel > $maxNegativeLevelOfChildren)
                    $maxNegativeLevelOfChildren = $childLevel;
            }
        }

        $negativeLevel = 0;

        if ($eval instanceof NegativePropositionEvaluation) {
            $negativeLevel = 1 + $maxNegativeLevelOfChildren;
            $this->negativeEvaluationIndex[$negativeLevel][] = $eval;
        }

        return $eval;
    }

    private function evaluationFromProposition(CompositeExpression $expression)
    {
        if ($expression instanceof OrProposition) {
            return new PositivePropositionEvaluation(1, null, $this->evalsToReset);
        } elseif ($expression instanceof AndProposition) {
            return new PositivePropositionEvaluation(count($expression->expressions()), null, $this->evalsToReset);
        } elseif ($expression instanceof NotProposition) {
            return new NegativePropositionEvaluation(0, null, $this->evalsToReset);
        }

        throw new \Exception("Invalid expression type");
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
        foreach ($this->evalsToReset as $propEval) {
            $propEval->reset();
        }

        $this->evalsToReset = new \SplObjectStorage;

        return $this;
    }

    private function propositionHash(Expression $expression)
    {
        $type = get_class($expression);

        if ($expression instanceof Condition)
            $value = "{$expression->getKey()}:{$expression->getValue()}";
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