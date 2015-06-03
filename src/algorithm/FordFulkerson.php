<?php
/**
 * Author: Benedikt Bauer
 * Date: 02.06.2015
 * Time: 21:12
 */

namespace Xenzilla\Graph\Algorithm;

use Xenzilla\Graph\Vertex;

class FordFulkerson extends AbstractMaximumFlow
{

    protected function buildSTPath(Vertex $start, Vertex $target)
    {
        $dfs = new DepthFirstSearch();
        $this->residualGraph->resetVertices();

        $visited = $dfs->findPathToVertex($start, $target);
        if ($visited === false) {
            return false;
        }

        if ($visited[0] !== $start) {
            array_unshift($visited, $start);
        }
        $visitedEdges = [];

        for ($i = 0; $i < count($visited) - 1; $i++) {
            $visitedEdges[] = $this->residualGraph->getEdge($visited[$i]->getId(), $visited[$i + 1]->getId());
        }

        return $visitedEdges;
    }
}