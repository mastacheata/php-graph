<?php
/**
 * Author: Benedikt Bauer
 * Date: 02.06.2015
 * Time: 18:58
 */

namespace Xenzilla\Graph\Algorithm;

use Xenzilla\Graph\Edge;
use Xenzilla\Graph\Graph;
use Xenzilla\Graph\Vertex;


abstract class AbstractMaximumFlow
{

    /**
     * The Graph on which to perform this algorithm
     * @var Graph
     */
    protected $graph;

    /**
     * Current Residual Graph
     * @var Graph
     */
    protected $residualGraph = null;

    public function __construct(Graph $graph)
    {
        $this->graph = $graph;
    }

    public function findMaxFlow(Vertex $start, Vertex $target)
    {
        $maxFlow = 0.0;

        if ($start === $target) {
            return $maxFlow;
        }

        $this->residualGraph = clone $this->graph;
        $residualStart = $this->residualGraph->getVertex($start->getId());
        $residualTarget = $this->residualGraph->getVertex($target->getId());

        while (!empty($path = $this->buildSTPath($residualStart, $residualTarget))) {
            $minCapacity = array_reduce($path, function ($minCapacity, Edge $edge) {
                return min($minCapacity, $edge->getCapacity());
            }, INF);
            $this->updateResidualGraph($path, $minCapacity);
            $maxFlow += $minCapacity;
        }

        return $maxFlow;
    }

    abstract protected function buildSTPath(Vertex $start, Vertex $target);

    protected function updateResidualGraph(array $path, $minCapacity)
    {
        foreach ($path as $edge) {
            $this->updateEdge($edge, $minCapacity);
            $this->updateResidualEdge($edge, $minCapacity);
        }
    }

    protected function updateEdge(Edge $edge, $minCapacity)
    {
        $newCapacity = $edge->getCapacity() - $minCapacity;
        if ($newCapacity <= 0) {
            $this->residualGraph->removeEdge($edge);
        } else {
            $edge->setCapacity($newCapacity);
        }
    }

    protected function updateResidualEdge(Edge $edge, $minCapacity)
    {
        $reverseEdge = $this->residualGraph->getEdge($edge->getB()->getId(), $edge->getA()->getId());
        if (!is_null($reverseEdge)) {
            $reverseEdge->setCapacity($reverseEdge->getCapacity() + $minCapacity);
        } else {
            $reverseEdge = $edge->getB()->connect($edge->getA(), $minCapacity);
            $reverseEdge->setFlow($edge->getFlow() * -1);
            $this->residualGraph->addEdge($reverseEdge);
        }
    }
}