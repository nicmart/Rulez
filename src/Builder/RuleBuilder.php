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
use NicMart\Rulez\Engine\Rule;
use NicMart\Rulez\Expression\Expression;

class RuleBuilder extends AbstractBuilder
{
    /**
     * @var Expression;
     */
    private $expression;

    /**
     * @var mixed
     */
    private $production;

    public function ifExpression(Expression $expression)
    {
        $this->expression = $expression;

        return $this;
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

    public function then($production)
    {
        $this->production = $production;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function end()
    {
        $this->building = new Rule($this->expression, $this->production);

        return parent::end();
    }


    private function getExpressionCallback()
    {
        return function(Expression $expression)
        {
            $this->expression = $expression;

            return $this;
        };
    }
} 