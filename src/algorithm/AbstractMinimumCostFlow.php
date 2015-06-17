<?php
/**
 * Author: Benedikt Bauer
 * Date: 09.06.2015
 * Time: 16:56
 */

namespace Xenzilla\Graph\Algorithm;


use Xenzilla\Graph\Edge;
use Xenzilla\Graph\Graph;
use Xenzilla\Graph\Vertex;

abstract class AbstractMinimumCostFlow
{

    /**
     * The Graph on which to perform this algorithm
     * @var Graph
     */
    protected $graph;

    /**
     * The residualGraph corresponding to the current flow
     * @var Graph
     */
    protected $residualGraph;

    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
    }

    abstract public function findMinimumCostFlow();

    /**
     * Check if the node-balances sum up to zero
     * @return bool
     */
    protected function checkTotalBalance()
    {
        $vertices = $this->graph->getVertexList();
        $totalBalance = array_reduce($vertices, function ($carry, Vertex $item) {
            return $carry + $item->getBalance();
        }, 0.0);

        return $totalBalance === 0.0;
    }

    protected function updateResidualEdge(Edge $edge, $capacity)
    {
        $this->updateEdge($edge, $capacity);
        $this->updateReverseEdge($edge, $capacity);
    }

    protected function updateEdge(Edge $edge, $capacity)
    {
        $newCapacity = $edge->getCapacity() - $capacity;
        if ($newCapacity <= 0) {
            $this->residualGraph->removeEdge($edge);
        } else {
            $edge->setCapacity($newCapacity);
        }
    }

    protected function updateReverseEdge(Edge $edge, $capacity)
    {
        $reverseEdge = $this->residualGraph->getEdge($edge->getB()->getId(), $edge->getA()->getId());
        if (!is_null($reverseEdge)) {
            $newCapacity = $reverseEdge->getCapacity() + $capacity;
            $reverseEdge->setCapacity($newCapacity);
        } else {
            $cost = $edge->getCost() != 0.0 ? ($edge->getCost() * -1) : 0.0;
            $reverseEdge = $edge->getB()->connect($edge->getA());
            $reverseEdge->setCapacity($capacity);
            $reverseEdge->setCost($cost);
            $this->residualGraph->addEdge($reverseEdge);
        }
    }

    protected function calculateMinimumCostFlow()
    {
        $cost = 0.0;
        foreach ($this->residualGraph->getEdgeList() as $edge) {
            $source = $this->graph->getVertex($edge->getA()->getId());
            $sink = $this->graph->getVertex($edge->getB()->getId());

            // Attention: simple OR, if either sink or source is false or if both are false, skip this iteration
            if (!$source | !$sink) {
                continue;
            }

            $originalEdge = $this->graph->getEdge($sink->getId(), $source->getId());
            if (!is_null($originalEdge)) {
                $cost += $edge->getCapacity() * $originalEdge->getCost();
            }
        }

        return $cost;
    }
}