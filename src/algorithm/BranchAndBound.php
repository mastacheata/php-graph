<?php
/**
 * Author: Benedikt Bauer
 * Date: 05.05.2015
 * Time: 17:41
 */

namespace Xenzilla\Graph\Algorithm;
use Xenzilla\Graph\Vertex;

class BranchAndBound extends BruteForce{

    protected function isVisited(Vertex $vertex) {
        return ($this->currentCost > $this->minimalCost) || $vertex->visited();
    }
}