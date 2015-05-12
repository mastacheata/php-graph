<?php
/**
 * Author: Benedikt Bauer
 * Date: 07.05.2015
 * Time: 20:15
 */

namespace Xenzilla\Graph\Algorithm;

class MooreBellmanFord extends AbstractShortestPath
{

    public function findShortestPath()
    {
        $this->init();

        for ($i = 0; $i < count($this->graph->getVertexList()) - 1; $i++) {
            foreach ($this->graph->getEdgeList() as $edge) {
                $edgeA = $edge->getA();
                $newDistance = $this->distances[$edgeA->getId()] + $edge->getWeight();
                $edgeB = $edge->getB();
                if ($newDistance < $this->distances[$edgeB->getId()]) {
                    $this->distances[$edgeB->getId()] = $newDistance;
                    $this->predecessors[$edgeB->getId()] = $edgeA;
                }
            }
        }

        foreach ($this->graph->getEdgeList() as $edge) {
            $edgeA = $edge->getA();
            $newDistance = $this->distances[$edgeA->getId()] + $edge->getWeight();
            $edgeB = $edge->getB();
            if ($newDistance < $this->distances[$edgeB->getId()]) {
                throw new \Exception('Negative Cycle found');
            }
        }
    }
}