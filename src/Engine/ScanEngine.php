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

class ScanEngine implements EngineInterface
{
    private $callbacksToProductions = array();

    /**
     * {@inheritdoc}
     */
    function addRule(Rule $rule)
    {
        $this->callbacksToProductions[] = [
            $rule->expression()->predicate(),
            $rule
        ];

        return $this;
    }

    /**
     * @param mixed $x
     *
     * @return array
     */
    function run($x)
    {
        $results = new \SplObjectStorage;

        foreach ($this->callbacksToProductions as $callAndRule) {
            list($callback, $rule) = $callAndRule;

            if ($callback($x))
                $results->attach($rule);
        }

        return $results;
    }
}