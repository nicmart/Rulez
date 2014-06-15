<?php
/**
 * This file is part of library-template
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Nicolò Martini <nicmartnic@gmail.com>
 */

namespace NicMart\Rulez\Builder;


use NicMart\Building\AbstractBuilder;
use NicMart\Rulez\Engine\EngineInterface;
use NicMart\Rulez\Engine\Rule;

class EngineBuilder extends AbstractBuilder
{
    public function __construct(EngineInterface $engine = null)
    {
        return parent::__construct(null, $engine);
    }

    public function rule()
    {
        return new RuleBuilder(function(Rule $rule)
        {
            $this->getEngine()->addRule($rule);
        });
    }

    /**
     * @return EngineInterface
     */
    private function getEngine()
    {
        return $this->building;
    }

} 