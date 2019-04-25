<?php

namespace Mongator\Pagerfanta;

use Mongator\Query\Query;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @author nvb <nvb@aproxima.ru>
 *
 */
class Adapter implements AdapterInterface
{
    /**
     * @var Query
     */
    private $query;

    /**
     * Constructor.
     *
     * @param Query $query The query.
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * Returns the query.
     *
     * @return Query The query.
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        return $this->query->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        return $this->query->limit($length)->skip($offset)->all();
    }
}
