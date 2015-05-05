<?php
/**
 * Author: Benedikt Bauer
 * Date: 31.03.2015
 * Time: 20:44
 */

namespace Xenzilla\Graph;


class Edge {

    /**
     * Identifier for this edge (A->id_B->id)
     * @var string
     */
    protected $id;

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
     * Weight of this edge
     * @var int
     */
    protected $weight;

    /**
     * Create a new edge between two vertices A and B
     * optionally set the weight of the new edge
     *
     * @param Vertex $a
     * @param Vertex $b
     * @param int $weight
     */
    public function __construct(Vertex $a, Vertex $b, $weight = -1) {
        $this->vertexA = $a;
        $this->vertexB = $b;
        $this->weight  = $weight;
        $this->id = $a->getId().'_'.$b->getId();
    }

    public function getA() {
        return $this->vertexA;
    }

    public function getB() {
        return $this->vertexB;
    }

    public function getWeight() {
        return $this->weight;
    }

    public function getId() {
        return $this->id;
    }
}