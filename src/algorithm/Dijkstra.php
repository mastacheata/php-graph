<?php
/**
 * Created by PhpStorm.
 * User: benedikt
 * Date: 06.05.2015
 * Time: 11:15
 */

namespace Xenzilla\Graph\Algorithm;

use Xenzilla\Graph\Edge;

class Dijkstra extends AbstractShortestPath
{

    /**
     * Distances from start Vertex to unvisited Vertices, ordered by distance ascending
     * @var float[]
     */
    protected $vertices = [];

    /**
     * Find shortest path
     */
    protected function findShortestPath()
    {
        $this->init();

        while (!empty($this->vertices)) {
            $currentVertex = $this->graph->getVertex(key($this->vertices));
            unset($this->vertices[key($this->vertices)]);

            if ($this->target >= 0 && $currentVertex->getId() === $this->target) {
                break;
            }

            $neighborEdges = array_filter($currentVertex->getNeighborEdges(), function (Edge $edge) {
                return array_key_exists($edge->getB()->getId(), $this->vertices);
            });

            foreach ($neighborEdges as $currentNeighborEdge) {
                $this->distanceUpdate($currentNeighborEdge);
            }
        }
    }

    /**
     * Reset / Initialize data structure
     */
    protected function init()
    {
        parent::init();
        $this->vertices = $this->distances;
        asort($this->vertices);
    }

    /**
     * Update distance to this Edge's endpoint and reorder the vertices list
     *
     * @param Edge $edge
     */
    private function distanceUpdate(Edge $edge)
    {
        $edgeA = $edge->getA();
        $newDistance = $this->distances[$edgeA->getId()] + $edge->getWeight();
        $edgeB = $edge->getB();

        if ($newDistance < $this->distances[$edgeB->getId()]) {
            $this->predecessors[$edgeB->getId()] = $edgeA;
            $this->distances[$edgeB->getId()] = $newDistance;
            $this->vertices[$edgeB->getId()] = $newDistance;
            asort($this->vertices);
        }
    }
}