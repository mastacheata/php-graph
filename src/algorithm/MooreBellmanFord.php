<?php
/**
 * Author: Benedikt Bauer
 * Date: 07.05.2015
 * Time: 20:15
 */

namespace Xenzilla\Graph\Algorithm;

use Xenzilla\Graph\Edge;
use Xenzilla\Graph\Vertex;

class MooreBellmanFord extends AbstractShortestPath
{

    public function findShortestPath()
    {
        $this->init();
        $this->calculateDistances();

        foreach ($this->graph->getEdgeList() as $edge) {
            if ($this->checkForCycle($edge)) {
                throw new \Exception('Graph contains negative cycle');
            }
        }
    }

    /**
     * Calculate distances and predecessors (for path generation)
     */
    private function calculateDistances()
    {
        for ($i = 0; $i < count($this->graph->getVertexList()) - 1; $i++) {
            foreach ($this->graph->getEdgeList() as $edge) {
                $edgeA = $edge->getA();
                $newDistance = $this->distances[$edgeA->getId()] + $edge->getCost();
                $edgeB = $edge->getB();
                if ($newDistance < $this->distances[$edgeB->getId()]) {
                    $this->distances[$edgeB->getId()] = $newDistance;
                    $this->predecessors[$edgeB->getId()] = $edgeA;
                    $edgeB->visit();
                }
            }
        }
    }

    /**
     * Check if graph contains a negative cycle
     *
     * @param Edge $edge
     * @return bool
     */
    private function checkForCycle(Edge $edge)
    {
        $edgeA = $edge->getA();
        $newDistance = $this->distances[$edgeA->getId()] + $edge->getCost();
        $edgeB = $edge->getB();
        if ($newDistance < $this->distances[$edgeB->getId()]) {
            return true;
        }

        return false;
    }

    /**
     * Find a negative cycle and return the path
     *
     * @param Vertex $start
     * @return \Xenzilla\Graph\Edge[]
     */
    public function buildNegativeCycleFrom(Vertex $start)
    {
        $this->start = $start->getId();
        $this->init();
        $this->graph->getVertex($this->start)->visit();
        $this->calculateDistances();

        foreach ($this->graph->getEdgeList() as $edge) {
            if ($this->checkForCycle($edge)) {
                return $this->getCyclePath($edge);
            }
        }

        return [];
    }

    /**
     * Get path of found cycle
     *
     * @param Edge $edge
     * @return Edge[]
     */
    private function getCyclePath(Edge $edge)
    {
        $start = $edge->getA();
        for ($i = 0; $i < $this->graph->getVertexCount(); $i++) {
            $predecessor = $this->predecessors[$start->getId()];
            if ($predecessor != $start && !is_null($predecessor)) {
                $start = $predecessor;
            } else {
                break;
            }
        }

        $cycle = [];
        $predecessor = null;
        $current = $start;

        while (true) {
            $predecessor = $this->predecessors[$current->getId()];
            $cycle[] = $this->graph->getEdge($predecessor->getId(), $current->getId());

            if ($predecessor == $start) {
                return $cycle;
            }

            $current = $predecessor;
        }
    }
}