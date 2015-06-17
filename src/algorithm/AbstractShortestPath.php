<?php
/**
 * Author: Benedikt Bauer
 * Date: 07.05.2015
 * Time: 20:39
 */

namespace Xenzilla\Graph\Algorithm;

use Xenzilla\Graph\Edge;
use Xenzilla\Graph\Graph;
use Xenzilla\Graph\Vertex;


abstract class AbstractShortestPath
{
    /**
     * Distances from start Vertex to every other Vertex in the Graph
     * @var float[]
     */
    protected $distances = [];

    /**
     * Predecessor Tree of shortest paths
     * @var Vertex[]
     */
    protected $predecessors = [];

    /**
     * The Graph on which to perform this algorithm
     * @var Graph
     */
    protected $graph;

    /**
     * Index of the start Vertex
     * @var int
     */
    protected $start;

    /**
     * Index of the target Vertex
     * @var int
     */
    protected $target;

    /**
     * Initialize with the Graph to work on
     *
     * @param Graph $graph
     */
    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
    }

    /**
     * Build a shortest-path between the vertices given by start and target indices
     *
     * @param int $start index for start Vertex
     * @param int $target index for target Vertex
     * @return Edge[]
     */
    public function buildShortestPath($start, $target)
    {
        $this->start = $start;
        $this->target = $target;

        if (empty($this->predecessors) || is_null($this->predecessors[$target])) {
            $this->findShortestPath();
        }

        $currentVertex = $this->graph->getVertex($target);
        /** @var Vertex[] $vertices */
        $vertices[] = $currentVertex;
        /** @var Edge[] $edges */
        $edges = [];

        while (true) {
            $currentVertex = $this->predecessors[$currentVertex->getId()];
            if (is_null($currentVertex)) {
                break;
            }
            array_unshift($vertices, $currentVertex);
            array_unshift($edges, $this->graph->getEdge($vertices[0]->getId(), $vertices[1]->getId()));
        }

        return $edges;
    }

    /**
     * Find shortest paths
     */
    abstract protected function findShortestPath();

    /**
     * Get the shortest total distance between two given Vertices according to Dijkstra's Algorithm
     *
     * @param int $start index for start Vertex
     * @param int $target index for target Vertex
     * @return float Total cost for the shortest path
     */
    public function getDistance($start, $target)
    {
        $this->start = $start;
        $this->target = $target;

        if (empty($this->predecessors) || $this->distances[$target] === INF) {
            $this->findShortestPath($start, $target);
        }

        return $this->distances[$target];
    }

    /**
     * Reset / Initialize data structure
     */
    protected function init()
    {
        foreach ($this->graph->getVertexList() as $vertex) {
            $this->distances[$vertex->getId()] = INF;
            $this->predecessors[$vertex->getId()] = null;
        }
        $this->distances[$this->start] = 0;
        $this->predecessors[$this->start] = $this->graph->getVertex($this->start);
    }
}