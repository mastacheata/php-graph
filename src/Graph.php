<?php
/**
 * Author: Benedikt Bauer
 * Date: 31.03.2015
 * Time: 21:23
 */

namespace Xenzilla\Graph;


class Graph {

    const EDGE_LIST = 1;
    const ADJACENCY_MATRIX = 2;
    const WEIGHTED_EDGE_LIST = 3;

    const DIRECTED = true;
    const UNDIRECTED = false;

    /**
     * List of edges (unique)
     * @var \SplObjectStorage
     */
    protected $edgeList;

    /**
     * List of vertices
     * @var Vertex[]
     */
    protected $vertexList = [];

    /**
     * Number of vertices in the graph
     * @var int
     */
    protected $vertexCount = 0;

    /**
     * Init Graph
     * Setup edge list SplObjectStorage,
     * set vertex counter
     *
     * @param int $vertexCount
     */
    public function __construct($vertexCount = 0) {
        $this->edgeList = new \SplObjectStorage();
        $this->vertexCount = $vertexCount;
    }

    /**
     * Import Graph from a text file
     *
     * @param string $filename file to import
     * @param int $type file structure (either edge list or adjacency matrix)
     * @param bool $directed
     * @return bool|Graph
     */
    public static function import($filename, $type, $directed = true) {
        $handle = fopen($filename, 'r');
        $graph = false;
        $edgeList = new \SplObjectStorage();

        if ($handle) {
            $line = fgets($handle);
            if ($line === false) {
                return false;
            }
            $graph = new Graph(intval($line));

            switch ($type) {
                case self::EDGE_LIST:
                    self::importEdgeList($handle, $graph->getVertexList(), $edgeList, $directed);
                    break;
                case self::ADJACENCY_MATRIX:
                    self::importAdjacencyMatrix($handle, $graph->getVertexList(), $edgeList, $directed);
                    break;
                case self::WEIGHTED_EDGE_LIST:
                    self::importWeightedEdgeList($handle, $graph->getVertexList(), $edgeList, $directed);
                    break;
            }

            fclose($handle);
            $graph->setEdgeList($edgeList);
            ksort($graph->getVertexList(), SORT_NUMERIC) ;
        }

        return $graph;
    }

    /**
     * Import EdgeList (2xN)
     *
     * @param resource $handle
     * @param Vertex[] $vertices
     * @param \SplObjectStorage $edgeList
     * @param bool $directed
     */
    protected static function importEdgeList($handle, array &$vertices, \SplObjectStorage $edgeList, $directed)
    {
        while ($line = trim(fgets($handle))) {
            list($from, $to) = explode("\t", $line);

            if (!array_key_exists($from, $vertices)) {
                $vertices[$from] = new Vertex($from);
            }
            if (!array_key_exists($to, $vertices)) {
                $vertices[$to] = new Vertex($to);
            }

            $edgeList->attach($vertices[$from]->connect($vertices[$to]));
            // if undirected add the opposite link implicitly
            if (!$directed) {
                $edgeList->attach($vertices[$to]->connect($vertices[$from]));
            }
        }
    }

    /**
     * Import Adjacency Matrix (NxN)
     *
     * @param resource $handle
     * @param Vertex[] $vertices
     * @param \SplObjectStorage $edgeList
     * @param bool $directed
     */
    protected static function importAdjacencyMatrix($handle, array &$vertices, \SplObjectStorage $edgeList, $directed)
    {
        $lineNo = 0;
        while ($line = trim(fgets($handle))) {
            $row = explode("\t", $line);
            $row = array_keys(array_filter($row));

            if (!array_key_exists($lineNo, $vertices)) {
                $vertices[$lineNo] = new Vertex($lineNo);
            }

            foreach ($row as $vertexIdx) {
                if (!array_key_exists($vertexIdx, $vertices)) {
                    $vertices[$vertexIdx] = new Vertex($vertexIdx);
                }
                $edgeList->attach($vertices[$lineNo]->connect($vertices[$vertexIdx]));
                // if undirected add the opposite link implicitly
                if (!$directed) {
                    $edgeList->attach($vertices[$vertexIdx]->connect($vertices[$lineNo]));
                }
            }

            $lineNo++;
        }
    }

    /**
     * Import EdgeList (2xN)
     *
     * @param resource $handle
     * @param Vertex[] $vertices
     * @param \SplObjectStorage $edgeList
     * @param bool $directed
     */
    protected static function importWeightedEdgeList($handle, array &$vertices, \SplObjectStorage $edgeList, $directed)
    {
        while ($line = trim(fgets($handle))) {
            list($from, $to, $weight) = explode("\t", $line);

            if (!array_key_exists($from, $vertices)) {
                $vertices[$from] = new Vertex($from);
            }
            if (!array_key_exists($to, $vertices)) {
                $vertices[$to] = new Vertex($to);
            }

            $edgeList->attach($vertices[$from]->connect($vertices[$to], $weight));
            // if undirected add the opposite link implicitly
            if (!$directed) {
                $edgeList->attach($vertices[$to]->connect($vertices[$from], $weight));
            }
        }
    }

    /**
     * Perform a depth-first-search starting at the supplied vertex
     *
     * @param Vertex $v
     * @param array $visited
     * @return array
     */
    public function dfs(Vertex $v, array $visited = []) {
        $visited[(string) $v] = $v;
        $neighbors = $v->getNeighborEdges();

        foreach ($neighbors as $neighborEdge) {
            if (is_null($neighborEdge)) {
                continue;
            }

            $b = $neighborEdge->getB();
            if (!array_key_exists((string) $b, $visited)) {
                $visited = $this->dfs($b, $visited);
            }
        }
        return $visited;
    }

    public function bfs(Vertex $v) {
        $queue = new \SplQueue();
        $queue->enqueue($v);
        $visited = array();
        do {
            $current = $queue->dequeue();
            if (array_key_exists((string) $current, $visited)) {
                continue;
            }

            $visited[(string) $current] = $current;

            /** @var \SplObjectStorage $neighbors */
            $neighbors = $current->getNeighborEdges();
            for ($i = 0; $i < $neighbors->count(); $i++) {
                $neighborEdge = $neighbors->current();
                $neighbors->next();

                $queue->enqueue($neighborEdge->getB());
            }
        } while ($queue->valid());

        return $visited;
    }

    /**
     * Find an MST for a given graph using algorithm of Prim
     * returns an array with the MST and the total weight
     *
     * @return array
     */
    public function prim() {
        $mst = [];
        $totalWeight = 0;
        $remainingNodes = new \SplPriorityQueue;

        // Build a Priority Queue of all Nodes not yet in MST
        // Weights are set to PHP_INT_MAX, which should be more than every "real" weight
        /** @var Vertex $vertex */
        foreach ($this->vertexList as $vertex) {
            $remainingNodes->insert($vertex, PHP_INT_MAX);
        }

//        $totalWeight -= $remainingNodes->top()->getWeight();
        do {
            $v = $remainingNodes->extract();
            $mst[(string) $v] = $v;
//            $totalWeight += $v->getWeight();
            /** @var \SplPriorityQueue $leavingEdges */
            $leavingEdges = $v->getPriorityNeighborEdges();
            $minEdge = null;
            $element = null;
            $found = true;
            do {
                if (!$leavingEdges->valid()) {
                    $found = false;
                    break;
                }
                $element = $leavingEdges->extract();

                /** @var Edge $minEdge */
                $minEdge = $element['data'];
            } while (array_key_exists((string) $minEdge->getB(), $mst) || !empty(array_intersect_key($this->dfs($minEdge->getB()), $mst)));

            if ($found) {
                $mst[(string) $minEdge->getB()] = $minEdge->getB();
                $totalWeight += $element['priority'];
            }
        } while (count($mst) < $remainingNodes->count());

        return [$mst, $totalWeight];
    }

    /**
     * Return an Edge list representation as String
     * @return string
     */
    public function __toString() {
        $edgeList = '';
        foreach($this->edgeList as $edge) {
            $edgeList .= print_r($edge->getA() . ' -> ' . $edge->getB() . ' # ' . $edge->getWeight(), true). "\n";
        }

        return $edgeList;
    }

    /**
     * Set the edge list from external
     *
     * @param $edgeList
     */
    public function setEdgeList($edgeList) {
        $this->edgeList = $edgeList;
    }

    /**
     * Getter for Vertex list
     *
     * @return array
     */
    public function &getVertexList() {
        return $this->vertexList;
    }

    /**
     * Getter for vertex count
     *
     * @return int
     */
    public function getVertexCount() {
        return $this->vertexCount;
    }

    /**
     * Get a vertex
     * If an id is specified, get the vertex with that id
     * otherwise return a random vertex
     *
     * @param int $id
     * @return Vertex
     */
    public function getVertex($id = -1) {

        if ($id < 0) {
            return $this->vertexList[rand(0, $this->vertexCount-1)];
        }

        return $this->vertexList[$id];
    }
}