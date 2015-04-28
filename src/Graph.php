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

    const RETURN_EDGES = 1;
    const RETURN_VERTICES = 2;
    const RETURN_WEIGHT = 3;
    const RETURN_ALL = 4;
    const RETURN_GRAPH = 5;


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
     * Build a new Graph from an Edge set
     *
     * @param Edge[] $edgeSet
     * @param int $count
     * @param bool $directed
     * @return Graph
     */
    public static function build_from_edges(array $edgeSet, $count, $directed = TRUE) {
        $graph = new Graph($count);
        $edgeList = $graph->getEdgeList();
        /** @var Vertex[] $vertices */
        $vertices = $graph->getVertexList();

        foreach($edgeSet as $edge) {
            $from = $edge->getA()->getId();
            if (!array_key_exists($from, $vertices)) {
                $vertices[$from] = new Vertex($from);
            }
            $to = $edge->getB()->getId();
            if (!array_key_exists($to, $vertices)) {
                $vertices[$to] = new Vertex($to);
            }

            $weight = $edge->getWeight();

            $edgeA = $vertices[$from]->connect($vertices[$to], $weight);
            $edgeList->attach($edgeA);

            // if undirected add the opposite link implicitly
            if (!$directed) {
                $edgeB = $vertices[$to]->connect($vertices[$from], $weight);
                $edgeList->attach($edgeB);
            }
        }

        ksort($vertices);
        $graph->setVertexList($vertices);

        return $graph;
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
     * @return Vertex[]
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

    /**
     * Perform a breadth-first-search starting at the supplied vertex
     *
     * @param Vertex $start
     * @return Vertex[]
     */
    public function bfs(Vertex $start) {
        $queue = new \SplQueue();
        $queue->enqueue($start);
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
     * Uses a priority Queue to store the remaining Edges
     * returns the total weight of the MST
     *
     * @param Vertex $start
     * @param int $return
     * @return float|\SplObjectStorage
     */
    public function prim(Vertex $start = null, $return = self::RETURN_WEIGHT) {
        $vertices = [];
        $edgeSet = new \SplObjectStorage();
        $toVisit = new \SplPriorityQueue();
        $startVertex = $start === null ? $start : $this->getVertex();
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
                    // Handle "0 weight" (priority = 1/weight)
                    if ($neighborEdge->getWeight() == 0) {
                        $priority = 0;
                    }
                    else {
                        $priority = 1 / $neighborEdge->getWeight();
                    }
                    // priority queue returns the element with the max priority first
                    $toVisit->insert($neighborEdge, $priority);
                }
            }
            $edgeSet->attach($currentEdge);
            $totalWeight += $currentEdge->getWeight();
            $vertices[$currentEdge->getB()->getId()] = $currentEdge->getB();
        }

        switch ($return) {
            case self::RETURN_WEIGHT:
                return $totalWeight;
            case self::RETURN_EDGES:
                return $edgeSet;
            case self::RETURN_VERTICES:
                return $vertices;
        }

        return $totalWeight;
    }


    /**
     * Find an MST for a given Graph using the algorithm of Kruskal
     * Makes use of a UnionFind structure @see UnionFind
     * returns the total weight of the mst
     *
     * @param int $return
     * @return float|\SplObjectStorage
     * @throws \ErrorException
     */
    public function kruskal($return = self::RETURN_WEIGHT) {
        $nodes = [];
        $vertices = [];
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
                $vertices[$currentEdge->getA()->getId()] = $currentEdge->getA();
                $vertices[$currentEdge->getB()->getId()] = $currentEdge->getB();
                $totalWeight += $currentEdge->getWeight();
                UnionFind::union($nodeA, $nodeB);
            }
        }

        switch($return) {
            case self::RETURN_WEIGHT:
                return $totalWeight;
            case self::RETURN_EDGES:
                return $edgeSet;
            case self::RETURN_VERTICES:
                return $vertices;
            case self::RETURN_ALL:
                return ['edges' => $edgeSet, 'vertices' => $vertices, 'weight' => $totalWeight];
        }

        return $totalWeight;
    }

    /**
     * Find a TSP route using the nearest neighbor heuristic
     *
     * @param int $start
     * @param int $return
     * @return float|\SplObjectStorage
     */
    public function nearestNeighbor($start = -1, $return = self::RETURN_WEIGHT) {
        $vertices = [];
        $visited = new \SplObjectStorage();
        $current = $startV = $this->getVertex($start);
        $visited->attach($current, 0);
        $vertices[$current->getId()] = $current;
        $totalWeight = 0;

        do {
            $neighbors = $current->getPriorityNeighborEdges();
            $currentEdge = null;
            do {
                /** @var Edge $currentEdge **/
                $currentEdge = $neighbors->extract();
                $current = $currentEdge->getB();
            }
            while (array_key_exists($current->getId(), $vertices));

            $visited->attach($current, $currentEdge->getWeight());
            $totalWeight += $currentEdge->getWeight();
            $vertices[$current->getId()] = $current;
        }
        while ($visited->count() < $this->getVertexCount());

        // connect last node to first node
        $lastNeighbors = $current->getNeighborEdges();
        foreach($lastNeighbors as $neighborEdge) {
            if ($neighborEdge->getB()->getId() === $startV->getId()) {
                $totalWeight += $neighborEdge->getWeight();
                break;
            }
        }

        switch($return) {
            case self::RETURN_WEIGHT:
                return $totalWeight;
            case self::RETURN_EDGES:
                return $visited;
            case self::RETURN_VERTICES:
                return $vertices;
        }

        return $totalWeight;
    }

    public function doubleTree($return = self::RETURN_WEIGHT) {
        // Create MST
        $kruskal = $this->kruskal(self::RETURN_ALL);
        /** @var \SplObjectStorage $mst */
        $mst = $kruskal['edges'];

        $mst_double = [];
        // Double Edges / Euler Graph
        /** @var Edge $edge */
        foreach($mst as $edge) {
            $mst_double[$edge->getId()] = $edge;
            $reversedEdge = $edge->getB()->connect($edge->getA(), $edge->getWeight());
            $mst_double[$reversedEdge->getId()] = $reversedEdge;
        }
        $eulergraph = self::build_from_edges($mst_double, $this->getVertexCount());
        $eulergraphEdges = $eulergraph->getEdgeList();

        // Build Euler Tour
        /** @var Edge[] $tour */
        $tour = [];
        $currentEdge = $eulergraphEdges->current();
        $tour[$currentEdge->getId()] = $currentEdge;

        while (count($tour) < count($mst_double)) {
            /** @var \SplObjectStorage $neighbors */
            $neighbors = $currentEdge->getB()->getNeighborEdges();
            $neighbors->addAll($currentEdge->getA()->getNeighborEdges());

            foreach ($neighbors as $neighborEdge) {
                $graphWithoutNeighborEdge = self::build_from_edges(array_diff_key($mst_double, [$neighborEdge]), $this->getVertexCount());
                $dfs = $graphWithoutNeighborEdge->dfs($graphWithoutNeighborEdge->getVertex());
                // If A and B are in the DFS wood, we can select this edge
                if ((array_key_exists($neighborEdge->getA()->getId(), $dfs) && array_key_exists($neighborEdge->getB()->getId(), $dfs)) && !array_key_exists($neighborEdge->getId(), $tour)) {
                    $tour[$neighborEdge->getId()] = $neighborEdge;
                    $currentEdge = $neighborEdge;
                    break;
                }
            }
        }

        // Go through Euler Tour and remove Edges that contain already visited nodes
        $visited = [];
        $tspEdges = [];
        $totalWeight = 0;
        $first = reset($tour);
        $shortcut = false;

        $visited[$first->getA()->getId()] = $first->getA();
        while (list(, $edge) = each($tour)) {
            $a = $edge->getA();
            while (array_key_exists($edge->getB()->getId(), $visited)) {
                list(,$edge) = each($tour);
                if (is_null($edge)) {
                    break 2;
                }
                $shortcut = true;
            }
            if ($shortcut) {
                // create a shortcut edge not yet present in the current graph, using the weight from the "original full" graph
                $shortcutEdge = $a->connect($edge->getB(), $this->getVertex($a->getId())->getIndexedNeighbors()[$a->getId().'_'.$edge->getB()->getId()]->getWeight());
                $tspEdges[$shortcutEdge->getId()] = $shortcutEdge;
                $visited[$shortcutEdge->getA()->getId()] = $shortcutEdge->getA();
                $totalWeight += $shortcutEdge->getWeight();
                $shortcut = false;
            }

            $tspEdges[$edge->getId()] = $edge;
            $visited[$edge->getB()->getId()] = $edge->getB();
            $totalWeight += $edge->getWeight();
        }

        switch($return) {
            case self::RETURN_WEIGHT:
                return $totalWeight;
            case self::RETURN_EDGES:
                return $tspEdges;
            case self::RETURN_VERTICES:
                return $visited;
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
     * Getter for Edge List
     *
     * @return \SplObjectStorage
     */
    public function &getEdgeList() {
        $this->edgeList->rewind();
        return $this->edgeList;
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
     * Set the vertex list from external
     *
     * @param array $vertices
     */
    public function setVertexList(array $vertices) {
        $this->vertexList = $vertices;
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