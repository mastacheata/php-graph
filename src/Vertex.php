<?php
/**
 * Author: Benedikt Bauer
 * Date: 31.03.2015
 * Time: 20:40
 */

namespace Xenzilla\Graph;


class Vertex {

    /**
     * Array of neighboring Edges
     * @var \SplObjectStorage
     */
    protected $neighbors;

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
        $this->id = intval($id);
    }

    /**
     * Connect this vertex to another one
     * create a new edge and add it to the neighbor list of both vertices
     *
     * @param Vertex $to
     * @return Edge
     */
    public function connect(Vertex $to) {
        $edge = new Edge($this, $to);
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
    }

    /**
     * Get all neighboring vertices (unique)
     *
     * @return \SplObjectStorage
     */
    public function getNeighborVertices() {
        $neighborVertices = new \SplObjectStorage();
        for ($i = 0; $i < count($this->neighbors); $i++) {
            $neighborEdge = $this->neighbors->current();
            $this->neighbors->next();

            $a = $neighborEdge->getA();
            $b = $neighborEdge->getB();

            $neighborVertices->attach($a/*, (string) $a*/);
            $neighborVertices->attach($b/*, (string) $b*/);
        }

        return $neighborVertices;
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