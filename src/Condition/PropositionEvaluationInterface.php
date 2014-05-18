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


/**
 * Class PropositionEvaluation
 * @package NicMart\Rulez\Condition
 */
interface PropositionEvaluationInterface
{
    /**
     * @return bool|null
     * @throws \LogicException
     */
    function resolvedStatus();

    /**
     * @return $this
     */
    function reset();

    /**
     * @return bool
     */
    function isResolved();

    /**
     * @return $this
     */
    function signalMatch();

    /**
     * @return $this
     */
    function signalMapUsed();
}