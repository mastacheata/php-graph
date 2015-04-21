<?php
/**
 * Author: Benedikt Bauer
 * Date: 31.03.2015
 * Time: 20:40
 */

namespace Xenzilla\Graph;


class Vertex {

    /**
     * List of neighboring Edges
     * @var \SplObjectStorage
     */
    protected $neighbors;

    /**
     * List of neighboring Edges sorted by minimal weight
     * @var \SplPriorityQueue
     */
    protected $priorityNeighbors;

    /**
     * numeric identifier
     * @var int
     */
    protected $id;

    /**
     * Init neighbors "list" as SplObjectStorage
     * Optionally set the numeric identifier
     *
     * @param int $id
     */
    public function __construct($id = -1) {
        $this->neighbors = new \SplObjectStorage();
        $this->priorityNeighbors = new \SplPriorityQueue();
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
    public function connectEdge(Edge $neighbor) {
        $this->neighbors->attach($neighbor);
        // SplPriorityQueue is using a MaxHeap, we want the minimum value first, so order by inverse
        if ($neighbor->getWeight() == 0) {
            $priority = 0;
        }
        else {
            $priority = 1 / $neighbor->getWeight();
        }
        $this->priorityNeighbors->insert($neighbor, $priority);
    }

    /**
     * Get all neighboring vertices (unique)
     *
     * @return \SplObjectStorage
     */
    public function getNeighborVertices() {
        $neighborVertices = new \SplObjectStorage();
        for ($i = 0; $i < $this->neighbors->count(); $i++) {
            $neighborEdge = $this->neighbors->current();
            $this->neighbors->next();
            $neighborVertices->attach($neighborEdge->getB());
        }
        $neighborVertices->rewind();

        return $neighborVertices;
    }

    /**
     * Neighbor edge list
     *
     * @return \SplObjectStorage
     */
    public function getNeighborEdges() {
        $this->neighbors->rewind();
        return $this->neighbors;
    }

    /**
     * Sorted Neighbor edge list
     *
     * @return \SplPriorityQueue
     */
    public function getPriorityNeighborEdges() {
        return $this->priorityNeighbors;
    }

    /**
     * Get the numeric identifier of this vertex
     *
     * @return int
     */
    public function getId() {
        return $this->id;
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