<?php
/**
 * Author: Benedikt Bauer
 * Date: 31.03.2015
 * Time: 20:40
 */

namespace Xenzilla\Graph;


class Vertex {

     /**
     * Array of indexed neighbors
     * @var Edge[]
     */
    protected $neighbors = [];

    /**
     * numeric identifier
     * @var int
     */
    protected $id;

    /**
     * visited flag
     * @var bool
     */
    protected $visited = false;

    /**
     * Parent Vertex in Graph
     * @var Vertex
     */
    protected $parent = null;

    /**
     * Set the numeric identifier and create a new Vertex
     *
     * @param int $id
     */
    public function __construct($id) {
        $this->id = intval($id);
    }

    /**
     * Connect this vertex to another one
     * create a new edge and add it to the neighbor list of both vertices
     * optionally set the weight of this edge
     *
     * @param Vertex $to
     * @param int $weight
     * @return Edge
     */
    public function connect(Vertex $to, $weight = 0) {
        $edge = new Edge($this, $to, $weight);
        $this->connectEdge($edge);

        return $edge;
    }

    /**
     * Connect this vertex to another one by adding the connecting edge
     *
     * @param Edge $neighbor
     */
    private function connectEdge(Edge $neighbor) {
        $this->neighbors[$neighbor->getId()] = $neighbor;
    }

    /**
     * Get all neighboring vertices (unique)
     *
     * @return Vertex[]
     */
    public function getNeighborVertices() {
        $neighborVertices = [];
        foreach ($this->neighbors as $edge) {
            $neighborVertices[$edge->getB()->getId()] = $edge->getB();
        }

        return $neighborVertices;
    }

    /**
     * Get the numeric identifier of this vertex
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get an unordered list of neighbors
     *
     * @return Edge[]
     */
    public function getNeighborEdges() {
         return $this->neighbors;
    }

    /**
     * Get an ordered-by-weight list of neighbors
     *
     * @return \SplPriorityQueue
     */
    public function getPriorityNeighborEdges() {
        $priorityList = new \SplPriorityQueue();
        foreach ($this->neighbors as $edge) {
            $priority = ($edge->getWeight() == 0) ? 0 : 1/$edge->getWeight();
            $priorityList->insert($edge, $priority);
        }
        return $priorityList;
    }

    /**
     * Get the visited state of this vertex
     *
     * @return bool
     */
    public function visited() {
        return $this->visited;
    }

    /**
     * Set the visited state and return the vertex
     *
     * @return $this
     */
    public function visit() {
        $this->visited = true;
        return $this;
    }

    /**
     * Set the visited state to false and return the vertex
     *
     * @return $this
     */
    public function unvisit() {
        $this->visited = false;
        return $this;
    }

    /**
     * Get the parent Vertex in the Shortest-Path-Tree
     *
     * @return Vertex
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set the parent Vertex in the Shortest-Path-Tree
     *
     * @param Vertex $parent
     */
    public function setParent(Vertex $parent)
    {
        $this->parent = $parent;
    }

    /**
     * String representation uses the id (if available) or the spl_object_hash otherwise
     *
     * @return string
     */
    public function __toString() {
        return $this->id >= 0 ? (string) $this->id : spl_object_hash($this);
    }
}