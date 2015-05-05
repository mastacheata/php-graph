<?php
/**
 * Author: Benedikt Bauer
 * Date: 05.05.2015
 * Time: 23:37
 */

namespace Xenzilla\Graph\Algorithm;
use Xenzilla\Graph\Vertex;

class Node {
    public $parent;
    public $rank = 0;
    public $vertex;

    public function __construct(Vertex $v) {
        $this->parent = $this;
        $this->vertex = $v;
    }
}