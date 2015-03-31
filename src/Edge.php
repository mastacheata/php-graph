<?php
/**
 * Author: Benedikt Bauer
 * Date: 31.03.2015
 * Time: 20:44
 */

namespace Xenzilla\Graph;


class Edge {

    /**
     * First adjacent vertex
     * @var Vertex
     */
    protected $vertexA;

    /**
     * Second adjacent vertex
     * @var Vertex
     */
    protected $vertexB;

    /**
     * Create a new edge between two vertices A and B
     * @param Vertex $a
     * @param Vertex $b
     */
    public function __construct(Vertex $a, Vertex $b) {
        $this->vertexA = $a;
        $this->vertexB = $b;
    }

    public function getA() {
        return $this->vertexA;
    }

    public function getB() {
        return $this->vertexB;
    }
}