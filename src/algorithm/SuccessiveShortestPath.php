<?php
/**
 * Author: Benedikt Bauer
 * Date: 09.06.2015
 * Time: 16:56
 */

namespace Xenzilla\Graph\Algorithm;


use Xenzilla\Graph\Edge;
use Xenzilla\Graph\Vertex;

class SuccessiveShortestPath extends AbstractMinimumCostFlow
{
    private $inOutBalances = [];

    public function findMinimumCostFlow()
    {
        if (!$this->checkTotalBalance()) {
            throw new \Exception('Balances not equalized');
        }

        $this->residualGraph = clone $this->graph;
        $this->init();

        while ($source = $this->findSource()) {
            // Reset visited state
            $this->residualGraph->resetVertices();

            if (!$sink = $this->findSink($source)) {
                throw new \Exception('Network too small');
            }

            $mbf = new MooreBellmanFord($this->residualGraph);
            $path = $mbf->buildShortestPath($source->getId(), $sink->getId());

            // reduce path array by walking over and returning the smallest capacity found
            $minCapacity = array_reduce($path, function ($carry, Edge $item) {
                return min($carry, $item->getCapacity());
            }, INF);
            $minSourceBalance = $source->getBalance() - $this->inOutBalances[$source->getId()];
            $minSinkBalance = $this->inOutBalances[$sink->getId()] - $sink->getBalance();

            $gamma = min($minCapacity, $minSourceBalance, $minSinkBalance);

            $this->inOutBalances[$source->getId()] += $gamma;
            $this->inOutBalances[$sink->getId()] -= $gamma;

            foreach ($path as $edge) {
                $this->updateResidualEdge($edge, $gamma);
            }
        }

        return $this->calculateMinimumCostFlow();
    }

    private function init()
    {
        foreach ($this->residualGraph->getVertexList() as $vertex) {
            $this->inOutBalances[$vertex->getId()] = 0.0;
        }

        foreach ($this->residualGraph->getEdgeList() as $edge) {
            if ($edge->getCost() < 0.0) {
                $this->updateResidualEdge($edge, $edge->getCapacity());
                $this->updateBalances($edge, $edge->getCapacity());
            }
        }
    }

    private function updateBalances(Edge $edge, $capacity)
    {
        $this->inOutBalances[$edge->getA()->getId()] += $capacity;
        $this->inOutBalances[$edge->getB()->getId()] -= $capacity;
    }

    /**
     * Find source in residual graph
     * Source has a positive sum of balance and inOutBalance
     *
     * @return bool|Vertex
     */
    private function findSource()
    {
        foreach ($this->residualGraph->getVertexList() as $vertex) {
            if (($vertex->getBalance() - $this->inOutBalances[$vertex->getId()]) > 0.0) {
                return $vertex;
            }
        }

        return false;
    }

    /**
     * Find sink in residual graph
     * Sink has a negative sum of balance and inOutBalance
     *
     * @param Vertex $source
     * @return bool|Vertex
     */
    private function findSink(Vertex $source)
    {
        $bfs = new BreadthFirstSearch();
        $vertices = $bfs->findReachableVertices($source);

        foreach ($vertices as $vertex) {
            if ($vertex === $source) {
                continue;
            }

            if ($vertex->getBalance() - $this->inOutBalances[$vertex->getId()] < 0.0) {
                return $vertex;
            }
        }

        return false;
    }
}