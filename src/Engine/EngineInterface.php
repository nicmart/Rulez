<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */
namespace NicMart\Rulez\Engine;

interface EngineInterface
{
    /**
     * @param Rule $rule
     *
     * @return $this
     */
    function addRule(Rule $rule);

    /**
     * @param mixed $x
     *
     * @return array
     */
    function run($x);
}