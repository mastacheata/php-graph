<?php
/**
 * Author: Benedikt Bauer
 * Date: 05.05.2015
 * Time: 22:45
 */

namespace Xenzilla\Graph\Algorithm;
use Xenzilla\Graph\Graph, Xenzilla\Graph\Edge;

class DoubleTree {

    /**
     * Edges part of this tour
     * @var Edge[]
     */
    protected $tour = [];

    /**
     * Total cost of this tour
     * @var float
     */
    protected $cost = 0.0;

    /**
     * Find a TSP tour using the DoubleTree algorithm
     *
     * @param Graph $graph
     */
    public function findTour(Graph $graph) {
        $kruskal = new Kruskal();
        $kruskal->findMST($graph);
        $mst = $kruskal->getGraph();

        $startVertex = $mst->getVertex(0);
        $dfs = new DepthFirstSearch();
        $reachableVertices = $dfs->findReachableVertices($startVertex);

        for ($key = 0; $key < count($reachableVertices)-1; $key++) {
            $edge = $graph->getEdge($reachableVertices[$key], $reachableVertices[$key+1]);
            $this->tour[] = $edge;
            $this->cost += $edge->getWeight();
        }

        $lastEdge = $graph->getEdge(end($reachableVertices), reset($reachableVertices));
        $this->tour[] = $lastEdge;
        $this->cost += $lastEdge->getWeight();
    }

    /**
     * Get the Edges part of this tour
     *
     * @return \Xenzilla\Graph\Edge[]
     */
    public function getTour() {
        return $this->tour;
    }

    /**
     * Get the total weight of this tour
     *
     * @return float
     */
    public function getCost() {
        return $this->cost;
    }
}