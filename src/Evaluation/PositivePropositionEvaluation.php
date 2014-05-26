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

class PositivePropositionEvaluation implements PropositionEvaluation
{
    use PropositionEvaluationTrait;

    /**
     * @var int
     */
    private $atLeast;

    /**
     * @var int
     */
    private $matched = 0;

    /**
     * @param $atLeast
     * @param callable $resolveCallback
     */
    function __construct($atLeast, $resolveCallback = null)
    {
        $this->atLeast = $atLeast;

        if ($resolveCallback)
            $this->onResolved($resolveCallback);
    }

    /**
     * {@inheritdoc}
     */
    function input($value)
    {
        if ($value) {
            $this->matched++;

            if ($this->matched >= $this->atLeast)
                $this->resolve(true);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    function reset()
    {
        $this->matched = 0;
        $this->resolvedStatus = null;

        foreach ($this->children() as $child)
            $child->reset();

        return $this;
    }
} 