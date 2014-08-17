<?php

namespace Pinq\Queries\Requests;

use Pinq\Queries\Functions;

/**
 * Request query for a custom aggregate using the supplied function
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class Aggregate extends Request
{
    /**
     * @var Functions\Aggregator
     */
    private $aggregatorFunction;

    public function __construct(Functions\Aggregator $aggregatorFunction)
    {
        $this->aggregatorFunction = $aggregatorFunction;
    }

    public function getType()
    {
        return self::AGGREGATE;
    }

    /**
     * @return Functions\Aggregator
     */
    public function getAggregatorFunction()
    {
        return $this->aggregatorFunction;
    }

    public function traverse(IRequestVisitor $visitor)
    {
        return $visitor->visitAggregate($this);
    }
}
