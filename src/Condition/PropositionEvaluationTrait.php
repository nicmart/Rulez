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


trait PropositionEvaluationTrait
{
    private $resolvedStatus = null;
    private $resolveCallback;

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
     * @param bool $resolvedStatus
     *
     * @return $this
     */
    private function resolve($resolvedStatus)
    {
        $this->resolvedStatus = $resolvedStatus;
        $callback = $this->resolveCallback;
        $callback($resolvedStatus, $this);

        return $this;
    }
} 