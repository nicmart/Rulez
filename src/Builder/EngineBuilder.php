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
use NicMart\Rulez\Expression\Expression;

class EngineBuilder extends AbstractBuilder
{
    public function __construct(EngineInterface $engine = null)
    {
        return parent::__construct(null, $engine);
    }

    public function ifExpression(Expression $expression)
    {
        $callback = $this->getExpressionCallback();

        return $callback($expression);
    }

    public function ifAll()
    {
        return new AndPropositionBuilder($this->getExpressionCallback());
    }

    public function ifAny()
    {
        return new OrPropositionBuilder($this->getExpressionCallback());
    }

    public function ifNone()
    {
        return new NotPropositionBuilder($this->getExpressionCallback());
    }

    private function getExpressionCallback()
    {
        return function(Expression $expression)
        {
            return new RuleBuilder($this->getRuleCallback(), $expression);
        };
    }

    private function getRuleCallback()
    {
        return function(Rule $rule)
        {
            $this->getEngine()->addRule($rule);

            return $this;
        };
    }

    /**
     * @return EngineInterface
     */
    private function getEngine()
    {
        return $this->building;
    }

} 