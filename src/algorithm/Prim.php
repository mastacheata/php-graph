<?php
/**
 * Created by PhpStorm.
 * User: benedikt
 * Date: 06.05.2015
 * Time: 12:35
 */

namespace Xenzilla\Graph\Algorithm;

use Xenzilla\Graph\Edge;
use Xenzilla\Graph\Graph;
use Xenzilla\Graph\Vertex;

class Prim
{

    public $mst = null;

    /**
     * Find an MST for a given graph using algorithm of Prim
     * Uses a priority Queue to store the remaining Edges
     * returns the total weight of the MST
     *
     * @param Vertex $start
     * @return Graph
     */
    public function prim(Vertex $start, Graph $graph)
    {
        $vertices = [];
        $edges = [];
        $toVisit = new \SplPriorityQueue();
        $startVertex = $start;
        $toVisit->insert(new Edge($startVertex, $startVertex, 0), 0);
        $totalWeight = 0;

        while (count($vertices) < $graph->getVertexCount()) {
            /** @var Edge $currentEdge */
            $currentEdge = $toVisit->extract();

            // Skip processing an Edge if we already have the cheapest edge for that endpoint
            if (array_key_exists($currentEdge->getB()->getId(), $vertices)) {
                continue;
            }

            /** @var \SplPriorityQueue $neighborEdges */
            $neighborEdges = $currentEdge->getB()->getPriorityNeighborEdges();
            while ($neighborEdges->valid()) {
                $neighborEdge = $neighborEdges->extract();

                if (!array_key_exists($neighborEdge->getB()->getId(), $vertices)) {
                    // Handle "0 weight" (priority = 1/weight)
                    if ($neighborEdge->getWeight() == 0) {
                        $priority = 0;
                    } else {
                        $priority = 1 / $neighborEdge->getWeight();
                    }
                    // priority queue returns the element with the max priority first
                    $toVisit->insert($neighborEdge, $priority);
                }
            }
            $edges[$currentEdge->getId()] = $currentEdge;
//            array_shift($edges);
            $totalWeight += $currentEdge->getCapacity();
            $vertices[$currentEdge->getB()->getId()] = $currentEdge->getB();
        }

        $mst = new Graph();
        $mst->addAll($edges, true);

        return $mst;
    }
}