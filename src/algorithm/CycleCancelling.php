<?php
/**
 * Author: Benedikt Bauer
 * Date: 16.06.2015
 * Time: 21:06
 */

namespace Xenzilla\Graph\Algorithm;

use Xenzilla\Graph\Edge;
use Xenzilla\Graph\Vertex;

class CycleCancelling extends AbstractMinimumCostFlow
{

    /**
     * Find a flow through the given graph with minimum cost
     */
    public function findMinimumCostFlow()
    {
        if (!$this->checkTotalBalance()) {
            throw new \Exception('Balances not equalized');
        }

        $this->residualGraph = clone $this->graph;

        /** @var Vertex $superSource */
        /** @var Vertex $superSink */
        list($superSource, $superSink) = $this->addSuperVertices();
        $superSource = $this->residualGraph->getVertex($superSource->getId());
        $superSink = $this->residualGraph->getVertex($superSink->getId());


        $flow = $this->buildCheckFlow($superSource, $superSink);
        if (!$flow) {
            throw new \Exception('Network too small');
        }

        while (!empty($cycle = $this->getNegativeCycle())) {
            $gamma = array_reduce($cycle, function ($carry, Edge $item) {
                return min($carry, $item->getCapacity());
            }, INF);

            foreach ($cycle as $edge) {
                $this->updateResidualEdge($edge, $gamma);
            }
        }

        return $this->calculateMinimumCostFlow();
    }

    /**
     * Add super source/sink to residual graph
     *
     * @return Vertex[]
     */
    private function addSuperVertices()
    {
        $superSource = new Vertex(-1, INF);
        $superSink = new Vertex(-2, -INF);
        $superEdges = [];

        foreach ($this->residualGraph->getVertexList() as $vertex) {
            $balance = $vertex->getBalance();
            if ($balance > 0) {
                $superEdges[] = $superSource->connect($vertex, $balance, 0.0);
            } elseif ($balance < 0) {
                $superEdges[] = $vertex->connect($superSink, ($balance * -1), 0.0);
            }
        }

        $this->residualGraph->addAll($superEdges);

        return [$superSource, $superSink];
    }

    /**
     * Build a maximal b-flow and check whether it matches the super source capacity
     *
     * @param Vertex $superSource
     * @param Vertex $superSink
     * @return bool
     */
    private function buildCheckFlow($superSource, $superSink)
    {
        $ek = new EdmondKarps($this->residualGraph);
        $flow = $ek->findMaxFlow($superSource, $superSink);
        $totalCapacity = array_reduce($superSource->getNeighborEdges(), function ($carry, Edge $item) {
            return $carry + $item->getCapacity();
        }, 0.0);

        $this->residualGraph = $ek->getUpdatedResidualGraph();
        return ($flow == $totalCapacity);
    }

    private function getNegativeCycle()
    {
        $this->residualGraph->resetVertices();
        $cycle = [];

        $mbf = new MooreBellmanFord($this->residualGraph);

        do {
            // Find an unvisited vertex
            $unvisitedVertex = null;
            foreach ($this->residualGraph->getVertexList() as $vertex) {
                if (!$vertex->visited()) {
                    $unvisitedVertex = $vertex;
                    // end search when first unvisited vertex is found
                    break;
                }
            }
            if (is_null($unvisitedVertex)) {
                // break while loop when all vertices were already visited
                break;
            }

            $cycle = $mbf->buildNegativeCycleFrom($unvisitedVertex);
        } while (empty($cycle));

        return $cycle;
    }
}