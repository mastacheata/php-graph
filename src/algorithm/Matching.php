<?php
/**
 * Author: Benedikt Bauer
 * Date: 23.06.2015
 * Time: 23:07
 */

namespace Xenzilla\Graph\Algorithm;


use Xenzilla\Graph\Graph;

class Matching
{

    private $graph;

    /**
     * Init by setting the graph to work on
     *
     * @param Graph $graph
     */
    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
    }

    public function countMatchingEdges()
    {
        $ff = new FordFulkerson($this->graph);
        $superSource = $this->graph->getVertex(-1);
        $superSink = $this->graph->getVertex(-2);

        return intval($ff->findMaxFlow($superSource, $superSink));
    }
}