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

    const DIRECTED = true;
    const UNDIRECTED = false;

    /**
     * List of edges (unique)
     * @var \SplObjectStorage
     */
    protected $edgeList;

    /**
     * List of vertices
     * @var array
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
     * @param array $vertices
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
     * @param array $vertices
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
            $a = $neighborEdge->getA();
            if (!array_key_exists((string) $a, $visited)) {
                $visited = $this->dfs($a, $visited);
            }

            $b = $neighborEdge->getB();
            if (!array_key_exists((string) $b, $visited)) {
                $visited = $this->dfs($b, $visited);
            }
        }
        return $visited;
    }

    public function findComponents(Vertex $v, &$s = [], &$p = [], &$c = 0, &$componentVertices = []) {
        $components = [];
        $v->preorder = $c++;
        $s[] = $v;
        $p[] = $v;

        foreach($v->getNeighborEdges() as $neighbor) {
            $w = $neighbor->getA();
            if ($w->preorder < 0) {
                $components = array_merge($components, $this->findComponents($w, $s, $p, $c, $componentVertices));
            }
            elseif (!in_array($w, $componentVertices)) {
                while ((($top = end($p)) !== null) && ($top->preorder > $w->preorder)) {
                    array_pop($p);
                }
            }

            $w = $neighbor->getB();
            if ($w->preorder < 0) {
                $components = array_merge($components, $this->findComponents($w, $s, $p, $c, $componentVertices));
            }
            elseif (!in_array($w, $componentVertices)) {
                while ((($top = end($p)) !== null) && ($top->preorder > $w->preorder)) {
                    array_pop($p);
                }
            }
        }

        if ($v === end($p)) {
            $component = [];
            do {
                $pop = array_pop($s);
                if (!is_null($pop)) {
                    $component[] = $pop;
                }
            }
            while (end($component) !== $v);
            array_pop($p);
            $componentVertices = array_merge($componentVertices, $component);
            $components[] = $component;
        }

        return $components;
    }

    /**
     * Return an Edge list representation as String
     * @return string
     */
    public function __toString() {
        $edgeList = '';
        foreach($this->edgeList as $edge) {
            $edgeList .= print_r($edge->getA() . ' -> ' . $edge->getB(), true). "\n";
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