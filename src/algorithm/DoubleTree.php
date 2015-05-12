<?php
/**
 * Author: Benedikt Bauer
 * Date: 05.05.2015
 * Time: 22:45
 */

namespace Xenzilla\Graph\Algorithm;

use Xenzilla\Graph\Edge;
use Xenzilla\Graph\Graph;

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
//        $kruskal = new Kruskal();
//        $kruskal->findMST($graph);
//        $mst = $kruskal->getGraph();

        $prim = new Prim();
        $mst = $prim->prim($graph->getVertex(0), $graph);

        $dfs = new DepthFirstSearch();
        $reachableVertices = $dfs->findReachableVertices($mst->getVertex(0));

        for ($key = 0; $key < count($reachableVertices)-1; $key++) {
            $edge = $graph->getEdge($reachableVertices[$key]->getId(), $reachableVertices[$key + 1]->getId());
            $this->tour[] = $edge;
            $this->cost += $edge->getWeight();
        }

        $lastEdge = $graph->getEdge(end($reachableVertices)->getId(), reset($reachableVertices)->getId());
        $this->tour[] = $lastEdge;
        $this->cost += $lastEdge->getWeight();
    }

    /**
     * Get the Edges part of this tour
     *
     * @return Edge[]
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