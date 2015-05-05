<?php
/**
 * Author: Benedikt Bauer
 * Date: 05.05.2015
 * Time: 17:42
 */

namespace Xenzilla\Graph\Algorithm;
use Xenzilla\Graph\Graph, Xenzilla\Graph\Vertex;

class BruteForce {
    /**
     * List of Edges visited during the minimal iteration (so far)
     * @var array
     */
    protected $minimalTour = [];

    /**
     * Total cost of the current Tour
     * @var float
     */
    protected $currentCost = 0;
    /**
     * Total cost of the minimal Tour (so far)
     * @var float
     */
    protected $minimalCost = INF;

    /**
     * Start Vertex from where to search this Graph
     * @var Vertex
     */
    protected $startVertex;
    /**
     * Number of vertices in this Graph
     * @var int
     */
    protected $vertexCount;


    /**
     * Initialize the brute force TSP solver
     *
     * @param Graph $graph the graph on which to solve the TSP problem
     * @param int $startVertex id of the startVertex, when empty pick a random start
     * @throws \Exception if resetting the vertices' visited state fails
     */
    public function __construct(Graph $graph, $startVertex = -1) {
        $this->startVertex = $graph->getVertex($startVertex);
        $this->vertexCount = $graph->getVertexCount();
        $graph->resetVertices();
    }

    /**
     * Calculate the minimal tour
     *
     * @param Vertex $currentVertex
     * @param array $currentTour
     */
    public function findMinimalTour(Vertex $currentVertex = null, array $currentTour = []) {
        if (is_null($currentVertex)) {
            $currentVertex = $this->startVertex;
        }

        $currentVertex->visit();
        foreach ($currentVertex->getNeighborEdges() as $edge) {
            $currentNeighbor = $edge->getB();
            $currentTour[$edge->getId()] = $edge;
            $this->currentCost += $edge->getWeight();

            if ($currentNeighbor === $this->startVertex && count($currentTour) === $this->vertexCount && $this->currentCost < $this->minimalCost) {
                $this->minimalCost = $this->currentCost;
                $this->minimalTour = $currentTour;
            }

            if (!$this->isVisited($currentNeighbor)) {
                $this->findMinimalTour($currentNeighbor, $currentTour);
            }

            $lastEdge = array_pop($currentTour);
            $this->currentCost -= $lastEdge->getWeight();
        }
        $currentVertex->unvisit();
    }

    public function getMinimalTour() {
        return $this->minimalTour;
    }

    public function getMinimalCost() {
        return $this->minimalCost;
    }

    protected function isVisited(Vertex $vertex) {
        return $vertex->visited();
    }
}