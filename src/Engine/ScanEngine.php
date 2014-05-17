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


use NicMart\Rulez\Maps\MapsCollection;
use NicMart\Rulez\Test\Condition\PropositionToCallback;

class ScanEngine implements EngineInterface
{
    private $callbacksToProductions = array();

    /**
     * @var MapsCollection
     */
    private $mapsCollection;

    /**
     * @var PropositionToCallback
     */
    private $propositionToCallback;

    function __construct(MapsCollection $mapsCollection)
    {
        $this->setMapsCollection($mapsCollection);
    }

    /**
     * {@inheritdoc}
     */
    function setMapsCollection(MapsCollection $maps)
    {
        $this->mapsCollection = $maps;
        $this->propositionToCallback = new PropositionToCallback($maps);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    function addRule(Rule $rule)
    {
        $this->callbacksToProductions[] = [
            $this->propositionToCallback->getCallback($rule->proposition()),
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