<?php
/**
 * Author: Benedikt Bauer
 * Date: 05.05.2015
 * Time: 22:27
 */

namespace Xenzilla\Graph\Algorithm;


use Xenzilla\Graph\Graph;

class Kruskal {

    /**
     * Total weight of the found MST
     * @var int
     */
    protected $totalWeight = 0;
    /**
     * List of Edges composing this MST
     * @var \Xenzilla\Graph\Edge[]
     */
    protected $edges = [];
    /**
     * List of Vertices composing this MST
     * @var \Xenzilla\Graph\Vertex[]
     */
    protected $vertices = [];


    /**
     * Find an MST for a given Graph using the algorithm of Kruskal
     * Makes use of a UnionFind structure @see UnionFind
     * returns the total weight of the mst
     *
     * @param Graph $graph
     */
    public function findMST(Graph $graph) {
        $nodes = [];
        $priorityEdgeList = $graph->getPriorityEdgeList();

        foreach($graph->getVertexList() as $vertex) {
            $nodes[$vertex->getId()] = new Node($vertex);
        }

        while ($priorityEdgeList->valid()) {
            $currentEdge = $priorityEdgeList->extract();
            $nodeA = $nodes[$currentEdge->getA()->getId()];
            $nodeB = $nodes[$currentEdge->getB()->getId()];
            if (UnionFind::find($nodeA) !== UnionFind::find($nodeB)) {
                $this->edges[$currentEdge->getId()] = $currentEdge;
                $this->vertices[$currentEdge->getA()->getId()] = $currentEdge->getA();
                $this->vertices[$currentEdge->getB()->getId()] = $currentEdge->getB();
                $this->totalWeight += $currentEdge->getWeight();
                UnionFind::union($nodeA, $nodeB);
            }
        }
    }

    public function getWeight() {
        return $this->totalWeight;
    }

    public function getEdgeList() {
        return $this->edges;
    }

    public function getVertexList() {
        return $this->vertices;
    }

    public function getGraph() {
        $mstGraph = new Graph();
        $mstGraph->addAll($this->edges);

        return $mstGraph;
    }
}