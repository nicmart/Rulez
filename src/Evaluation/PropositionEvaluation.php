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
 * Class PositivePropositionEvaluation
 * @package NicMart\Rulez\Condition
 */
interface PropositionEvaluation
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
     * @param callable $callback
     * @return $this
     */
    function onResolved(callable $callback);

    /**
     * @return bool
     */
    function isResolved();

    /**
     * @param bool $inputValue
     *
     * @return $this
     */
    function input($inputValue);

    /**
     * @return PropositionEvaluation[]
     */
    function children();

    /**
     * @param PropositionEvaluation $evaluation
     *
     * @return $this
     */
    function addChild(PropositionEvaluation $evaluation);

    /**
     * @param PropositionEvaluation[] $evaluations
     *
     * @return $this
     */
    function addChildren(array $evaluations);
}