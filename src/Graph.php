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
     * Priority Queue with all Edges ordered by 1/weight
     * @var \SplPriorityQueue
     */
    protected $priorityEdgeList;

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
                    $priorityEdgeList = new \SplPriorityQueue();
                    self::importWeightedEdgeList($handle, $graph->getVertexList(), $edgeList, $directed, $priorityEdgeList);
                    $graph->setPriorityEdgeList($priorityEdgeList);
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
     * @param \SplPriorityQueue $edges
     */
    protected static function importWeightedEdgeList($handle, array &$vertices, \SplObjectStorage $edgeList, $directed, \SplPriorityQueue $edges)
    {
        while ($line = trim(fgets($handle))) {
            list($from, $to, $weight) = explode("\t", $line);

            if (!array_key_exists($from, $vertices)) {
                $vertices[$from] = new Vertex($from);
            }
            if (!array_key_exists($to, $vertices)) {
                $vertices[$to] = new Vertex($to);
            }

            if ($weight == 0) {
                $priority = $weight;
            }
            else {
                $priority = 1 / $weight;
            }
            $edge = $vertices[$from]->connect($vertices[$to], $weight);
            $edges->insert($edge, $priority);
            $edgeList->attach($edge);

            // if undirected add the opposite link implicitly
            if (!$directed) {
                $edge = $vertices[$to]->connect($vertices[$from], $weight);
                $edges->insert($edge, $priority);
                $edgeList->attach($edge);
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
        $visited[$v->getId()] = $v;
        $neighbors = $v->getNeighborEdges();

        foreach ($neighbors as $neighborEdge) {
            if (is_null($neighborEdge)) {
                continue;
            }

            /** @var Vertex $b */
            $b = $neighborEdge->getB();
            if (!array_key_exists($b->getId(), $visited)) {
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
            if (array_key_exists($current->getId(), $visited)) {
                continue;
            }

            $visited[$current->getId()] = $current;

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
        $vertices = [];
        $toVisit = new \SplPriorityQueue();
        $startVertex = $this->getVertex();
        $toVisit->insert(new Edge($startVertex, $startVertex, 0), 0);
        $totalWeight = 0;

        while (count($vertices) < $this->vertexCount) {
            /** @var Edge $currentEdge */
            $currentEdge = $toVisit->extract();

            // Skip processing an Edge if we already have the cheapest edge for that endpoint
            if (array_key_exists($currentEdge->getB()->getId(), $vertices)) {
                continue;
            }

            /** @var \SplPriorityQueue $neighborEdges */
            $neighborEdges = $currentEdge->getB()->getPriorityNeighborEdges();
            while ($neighborEdges->valid()) {
                $neighborEdge = $neighborEdges->extract();

                if (!array_key_exists($neighborEdge->getB()->getId(), $vertices)) {
                    if ($neighborEdge->getWeight() == 0) {
                        $priority = 0;
                    }
                    else {
                        $priority = 1 / $neighborEdge->getWeight();
                    }
                    $toVisit->insert($neighborEdge, $priority);
                }
            }

            $totalWeight += $currentEdge->getWeight();
            $vertices[$currentEdge->getB()->getId()] = $currentEdge->getB();
        }

        return $totalWeight;
    }


    public function kruskal() {
        $nodes = [];
        $edgeSet = new \SplObjectStorage();
        $totalWeight = 0;
        $priorityEdgeList = clone $this->priorityEdgeList;

        foreach($this->vertexList as $vertex) {
            $nodes[$vertex->getId()] = new Node($vertex);
        }

        while ($priorityEdgeList->valid()) {
            $currentEdge = $priorityEdgeList->extract();
            $nodeA = $nodes[$currentEdge->getA()->getId()];
            $nodeB = $nodes[$currentEdge->getB()->getId()];
            if (UnionFind::find($nodeA) !== UnionFind::find($nodeB)) {
                $edgeSet->attach($currentEdge);
                $totalWeight += $currentEdge->getWeight();
                UnionFind::union($nodeA, $nodeB);
            }
        }

        return $totalWeight;
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
     * @param \SplObjectStorage $edgeList
     */
    public function setEdgeList(\SplObjectStorage $edgeList) {
        $this->edgeList = $edgeList;
    }

    /**
     * Set the priority edge list from external
     *
     * @param \SplPriorityQueue $priorityEdgeList
     */
    public function setPriorityEdgeList(\SplPriorityQueue $priorityEdgeList) {
        $this->priorityEdgeList = $priorityEdgeList;
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