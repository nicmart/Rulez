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

class NegativePropositionEvaluation implements PropositionEvaluation
{
    use PropositionEvaluationTrait;

    /**
     * @param $atMost
     * @param callable $resolveCallback
     */
    function __construct($atMost = 0, $resolveCallback = null)
    {
        $this->limit = $atMost;

        if ($resolveCallback)
            $this->onResolved($resolveCallback);
    }

    /**
     * {@inheritdoc}
     */
    function input($value)
    {
        if ($value) {
            $this->positiveInputCounter++;

            if ($this->positiveInputCounter > $this->limit)
                $this->resolve(false);
        }

        return $this;
    }
} 