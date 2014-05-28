<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Evaluation;

/**
 * Trait PropositionEvaluationTrait
 * @package NicMart\Rulez\Evaluation
 */
trait PropositionEvaluationTrait
{

    /**
     * @var int
     */
    private $limit;


    /**
     * @var int
     */
    private $positiveInputCounter = 0;

    /**
     * @var null|bool
     */
    private $resolvedStatus = null;

    /**
     * @var callable
     */
    private $resolvedCallbacks = [];

    /**
     * @var PropositionEvaluation[]
     */
    private $children = [];

    /**
     * {@inheritdoc}
     */
    function resolvedStatus()
    {
        if (!$this->isResolved())
            throw new \LogicException("The proposition is not resolved yet.");

        return $this->resolvedStatus;
    }

    /**
     * {@inheritdoc}
     */
    function isResolved()
    {
        return $this->resolvedStatus !== null;
    }

    /**
     * {@inhteridoc}
     */
    function onResolved(callable $callback)
    {
        $this->resolvedCallbacks[] = $callback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    function children()
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    function addChild(PropositionEvaluation $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    function addChildren(array $children)
    {
        foreach ($children as $child)
            $this->addChild($child);

        return $this;
    }


    /**
     * {@inheritdoc}
     */
    function reset()
    {
        $this->positiveInputCounter = 0;
        $this->resolvedStatus = null;

        foreach ($this->children() as $child)
            $child->reset();

        return $this;
    }

    /**
     * @param bool $resolvedStatus
     *
     * @return $this
     */
    private function resolve($resolvedStatus)
    {
        $this->resolvedStatus = $resolvedStatus;

        foreach ($this->children() as $subEval)
            $subEval->input($resolvedStatus);

        foreach ($this->resolvedCallbacks as $callback)
            $callback($resolvedStatus, $this);

        return $this;
    }
} 