<?php
/**
 * Author: Benedikt Bauer
 * Date: 31.03.2015
 * Time: 21:23
 */

namespace Xenzilla\Graph;


class Graph {

    const EDGE_LIST = 1;
    const ADJACENT_MATRIX = 2;

    /**
     * List of edges (unique)
     * @var \SplObjectStorage
     */
    protected $edgeList;

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
     * @return bool|Graph
     */
    public static function import($filename, $type) {
        $handle = fopen($filename, 'r');
        $graph = false;
        $edgeList = new \SplObjectStorage();

        if ($handle) {
            $line = fgets($handle);
            if ($line === false) {
                return false;
            }
            $graph = new Graph(intval($line));
            $vertices = [];

            switch ($type) {
                case self::EDGE_LIST:
                    self::importEdgeList($handle, $vertices, $edgeList);
                    break;
                case self::ADJACENT_MATRIX:
                    self::importAdjacencyMatrix($handle, $vertices, $edgeList);
                    break;
            }

            fclose($handle);
            $graph->setEdgeList($edgeList);
        }

        return $graph;
    }

    /**
     * Import EdgeList (2xN)
     *
     * @param $handle resource
     * @param $vertices array
     * @param $edgeList \SplObjectStorage
     */
    protected static function importEdgeList($handle, array $vertices, \SplObjectStorage $edgeList)
    {
        while ($line = fgets($handle)) {
            list($from, $to) = explode("\t", $line);

            if (!array_key_exists($from, $vertices)) {
                $vertices[$from] = new Vertex($from);
            }
            if (!array_key_exists($to, $vertices)) {
                $vertices[$to] = new Vertex($to);
            }

            $edgeList->attach($vertices[$from]->connect($vertices[$to])/*, [(string) $from, (string) $to]*/);
        }
    }

    /**
     * Import Adjacency Matrix (NxN)
     *
     * @param $handle resource
     * @param $vertices array
     * @param $edgeList \SplObjectStorage
     */
    protected static function importAdjacencyMatrix($handle, array $vertices, \SplObjectStorage $edgeList)
    {
        $lineNo = 0;
        while ($line = fgets($handle)) {
            $row = explode("\t", $line);
            $row = array_flip(array_filter($row));

            if (!array_key_exists($lineNo, $vertices)) {
                $vertices[$lineNo] = new Vertex($lineNo);
            }

            foreach ($row as $vertexIdx) {
                if (!array_key_exists($vertexIdx, $vertices)) {
                    $vertices[$vertexIdx] = new Vertex($vertexIdx);
                }
                $edgeList->attach($vertices[$lineNo]->connect($vertices[$vertexIdx])/*, [(string) $vertices[$lineNo], (string) $vertices[$vertexIdx]]*/);
            }

            $lineNo++;
        }
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
}