<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Expression;


class PropositionEvaluation implements PropositionEvaluationInterface
{
    use PropositionEvaluationTrait;

    /**
     * @var int
     */
    private $numOfMaps;

    /**
     * @var int
     */
    private $atLeast;

    /**
     * @var int
     */
    private $atMost;

    /**
     * @var int
     */
    private $matched = 0;

    /**
     * @var int
     */
    private $remainingMaps;

    /**
     * @param $numOfMaps
     * @param $atLeast
     * @param $atMost
     * @param callable $resolveCallback
     */
    function __construct($numOfMaps, $atLeast, $atMost, callable $resolveCallback)
    {
        $this->numOfMaps = $this->remainingMaps = $numOfMaps;
        $this->atLeast = $atLeast;
        $this->atMost = $atMost;
        $this->resolveCallback = $resolveCallback;
    }

    /**
     * {@inheritdoc}
     */
    function signalMatch()
    {
        $this->matched++;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    function signalMapUsed()
    {
        $this->remainingMaps--;
        $maxMatches = $this->matched + $this->remainingMaps;

        if ($maxMatches < $this->atLeast)
            return $this->resolve(false);
        if ($this->atLeast <= $this->matched && $maxMatches <= $this->atMost)
            return $this->resolve(true);
        if ($this->matched > $this->atMost)
            return $this->resolve(false);

        return $this;
    }

    function reset()
    {
        $this->matched = 0;
        $this->remainingMaps = $this->numOfMaps;
        $this->resolvedStatus = null;

        return $this;
    }
} 