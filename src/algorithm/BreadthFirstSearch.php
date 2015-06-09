<?php
/**
 * Author: Benedikt Bauer
 * Date: 02.06.2015
 * Time: 21:27
 */

namespace Xenzilla\Graph\Algorithm;

use Xenzilla\Graph\Vertex;

class BreadthFirstSearch
{

    private $queue = [];

    private $visited = [];

    private $parent = [];

    /**
     * Get a list of vertices reachable from the supplied startVertex
     *
     * @param Vertex $startVertex
     * @return Vertex[]|boolean
     */
    public function findReachableVertices(Vertex $startVertex)
    {
        $this->bfs($startVertex);

        return $this->visited;
    }

    /**
     * @param Vertex $startVertex
     * @param Vertex $endVertex
     * @return bool
     */
    private function bfs(Vertex $startVertex, Vertex $endVertex = null)
    {
        array_unshift($this->queue, $startVertex);
        $startVertex->visit();
        $this->visited[] = $startVertex;

        while (!empty($this->queue)) {
            /** @var Vertex $vertex */
            $vertex = array_pop($this->queue);

            if ($vertex === $endVertex) {
                return true;
            }

            foreach ($vertex->getNeighborVertices() as $neighbor) {
                if (!$neighbor->visited()) {
                    $this->parent[$neighbor->getId()] = $vertex;
                    array_unshift($this->queue, $neighbor);
                    $neighbor->visit();
                    $this->visited[] = $neighbor;
                }
            }
        }

        return false;
    }

    /**
     * Get a list of vertices visited on the way from startVertex to endVertex
     *
     * @param Vertex $startVertex
     * @param Vertex $endVertex
     * @return Vertex[]|boolean
     */
    public function findPathToVertex(Vertex $startVertex, Vertex $endVertex)
    {
        if ($this->bfs($startVertex, $endVertex)) {
            $current = $endVertex;
            $path = [$current];
            while (array_key_exists($current->getId(), $this->parent) && $current !== $startVertex) {
                array_unshift($path, $this->parent[$current->getId()]);
                $current = $this->parent[$current->getId()];
            }
            return $path;
        } else {
            return false;
        }
    }

}