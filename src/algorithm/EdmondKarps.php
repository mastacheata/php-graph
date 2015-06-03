<?php
/**
 * Author: Benedikt Bauer
 * Date: 02.06.2015
 * Time: 21:25
 */

namespace Xenzilla\Graph\Algorithm;


use Xenzilla\Graph\Vertex;

class EdmondKarps extends AbstractMaximumFlow
{

    protected function buildSTPath(Vertex $start, Vertex $target)
    {
        $bfs = new BreadthFirstSearch();
        $this->residualGraph->resetVertices();

        $visited = $bfs->findPathToVertex($start, $target);
        $visitedEdges = [];

        for ($i = 0; $i < count($visited) - 1; $i++) {
            $visitedEdges[] = $this->residualGraph->getEdge($visited[$i]->getId(), $visited[$i + 1]->getId());
        }

        return $visitedEdges;
    }
}