<?php
/**
 * Author: Benedikt Bauer
 * Date: 05.05.2015
 * Time: 22:54
 */

namespace Xenzilla\Graph\Algorithm;
use Xenzilla\Graph\Vertex;

class DepthFirstSearch {

    protected $vertexFound = false;


    /**
     * Get a list of vertices reachable from the supplied startVertex
     *
     * @param Vertex $startVertex
     * @return Vertex[]
     */
    public function findReachableVertices(Vertex $startVertex) {
        $this->vertexFound = false;
        $accessibleList = [];
        $this->dfs($accessibleList, $startVertex);

        return array_values($accessibleList);
    }

    /**
     * Perform a Depth-First-Search looking for endVertex and beginning at startVertex
     *
     * @param Vertex[] $visitList visited Vertices call-by-reference
     * @param Vertex $startVertex start at this Vertex
     * @param Vertex $endVertex look for this Vertex
     */
    private function dfs(array &$visitList, Vertex $startVertex, Vertex $endVertex = null) {
        if ($this->vertexFound) {
            return;
        }

        $visitList[$startVertex->getId()] = $startVertex;
        if ($startVertex === $endVertex) {
            $this->vertexFound = true;
            return;
        }

        $startVertex->visit();

        foreach($startVertex->getNeighborVertices() as $vertex) {
            if (!$vertex->visited()) {
                $this->dfs($visitList, $vertex, $endVertex);
            }
        }
    }
}