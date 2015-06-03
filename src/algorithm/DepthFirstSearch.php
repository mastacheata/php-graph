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

    protected $parent = [];


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
     * @return boolean
     */
    private function dfs(array &$visitList, Vertex $startVertex, Vertex $endVertex = null)
    {
        if ($this->vertexFound) {
            return true;
        }

        $visitList[] = $startVertex;
        if ($startVertex === $endVertex) {
            $this->vertexFound = true;
            return true;
        }

        $startVertex->visit();

        foreach ($startVertex->getNeighborVertices() as $vertex) {
            if (!array_key_exists($vertex->getId(), $this->parent)) {
                $this->parent[$vertex->getId()] = $startVertex;
            }

            if (!$vertex->visited()) {
                if ($this->dfs($visitList, $vertex, $endVertex))
                    return true;
            }
        }

        return false;
    }

    /**
     * Get a list of vertices visited on the way from startVertex to endVertex
     *
     * @param Vertex $startVertex
     * @param Vertex $endVertex
     * @return Vertex[]
     */
    public function findPathToVertex(Vertex $startVertex, Vertex $endVertex)
    {
        $this->vertexFound = false;
        $visitedList = [];
        $this->parent = [];
        if (!$this->dfs($visitedList, $startVertex, $endVertex)) {
            return false;
        }

        $current = $endVertex;
        $path = [$current];
        while (array_key_exists($current->getId(), $this->parent) && $current !== $startVertex) {
            array_unshift($path, $this->parent[$current->getId()]);
            $current = $this->parent[$current->getId()];
        }

        return $path;
    }
}