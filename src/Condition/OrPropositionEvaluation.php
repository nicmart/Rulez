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


class OrPropositionEvaluation implements PropositionEvaluationInterface
{
    use PropositionEvaluationTrait;

    private $numOfMaps;

    private $remainingMaps;

    private $signalMatched = false;

    function __construct($numOfMaps, callable $resolveCallback)
    {
        $this->numOfMaps = $numOfMaps;
        $this->resolveCallback = $resolveCallback;

        $this->reset();
    }

    /**
     * {@inheritdoc}
     */
    function reset()
    {
        $this->remainingMaps = $this->numOfMaps;
        $this->signalMatched = false;
    }

    /**
     * {@inheritdoc}
     */
    function signalMatch()
    {
        $this->signalMatched = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    function signalMapUsed()
    {
        if ($this->signalMatched) {
            return $this->resolve(true);
        }

        $this->remainingMaps--;

        if ($this->remainingMaps === 0)
            return $this->resolve(false);

        return $this;
    }
} 