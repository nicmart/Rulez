<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Condition;


class PositiveOnlyPropositionEvaluation implements PropositionEvaluationInterface
{
    use PropositionEvaluationTrait;

    private $atLeast;

    private $matchCount;

    function __construct($atLeast, callable $resolveCallback)
    {
        $this->atLeast = $atLeast;
        $this->resolveCallback = $resolveCallback;

        $this->reset();
    }

    /**
     * {@inheritdoc}
     */
    function reset()
    {
        $this->resolvedStatus = null;
        $this->matchCount = 0;
    }

    /**
     * {@inheritdoc}
     */
    function signalMatch()
    {
        if (++$this->matchCount >= $this->atLeast)
            $this->resolve(true);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    function signalMapUsed()
    {
        return $this;
    }
} 