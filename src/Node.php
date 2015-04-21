<?php
/**
 * Author: Benedikt Bauer
 * Date: 22.04.2015
 * Time: 00:04
 */

namespace Xenzilla\Graph;


class Node {
    public $parent;
    public $rank = 0;
    public $vertex;

    public function __construct(Vertex $v) {
        $this->parent = $this;
        $this->vertex = $v;
    }
}