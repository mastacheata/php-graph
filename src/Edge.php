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
     * Capacity of this edge
     * @var float
     */
    protected $capacity;

    /**
     * Current flow over this edge
     * @var float
     */
    protected $flow = 0.0;

    /**
     * @var float
     */
    protected $cost = 0.0;

    /**
     * Create a new edge between two vertices A and B
     * optionally set the capacity and cost of the new edge
     *
     * @param Vertex $a
     * @param Vertex $b
     * @param float $capacity
     * @param float $cost
     */
    public function __construct(Vertex $a, Vertex $b, $capacity = 0.0, $cost = 0.0)
    {
        $this->vertexA = $a;
        $this->vertexB = $b;
        $this->cost = $cost;
        $this->capacity = $capacity;
        $this->id = $a->getId().'_'.$b->getId();
    }

    public function getA() {
        return $this->vertexA;
    }

    public function getB() {
        return $this->vertexB;
    }

    public function getCost()
    {
        return $this->cost;
    }

    public function setCost($cost)
    {
        $this->cost = $cost;
    }

    public function getCapacity()
    {
        return $this->capacity;
    }

    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;
    }

    public function getFlow()
    {
        return $this->flow;
    }

    public function setFlow($flow)
    {
        if ($flow > $this->capacity) {
            throw new \Exception('New flow exceeds capacity (new flow: ' . $flow . ', capacity: ' . $this->capacity);
        } else {
            $this->flow = $flow;
        }
    }

    public function adjustFlow($flow)
    {
        $this->setFlow($flow + $this->flow);
    }

    public function getId() {
        return $this->id;
    }
}