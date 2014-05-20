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


class SingleConditionPropositionEvaluation implements PropositionEvaluationInterface
{
    use PropositionEvaluationTrait;

    function __construct(callable $callback)
    {
        $this->resolveCallback = $callback;
    }

    /**
     * @return $this
     */
    function reset()
    {
    }

    /**
     * @return $this
     */
    function signalMatch()
    {
        $this->resolve(true);
    }

    /**
     * @return $this
     */
    function signalMapUsed()
    {
    }

    function resolvedStatus()
    {
        return false;
    }

    function isResolved()
    {
        return false;
    }


} 