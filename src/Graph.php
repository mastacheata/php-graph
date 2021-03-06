<?php
/**
 * Author: Benedikt Bauer
 * Date: 05.05.2015
 * Time: 17:39
 */

namespace Xenzilla\Graph;

class Graph {

    const EDGE_LIST = 1;
    const ADJACENCY_MATRIX = 2;
    const WEIGHTED_EDGE_LIST = 3;
    const BALANCED_EDGE_LIST = 4;
    const BIPARTED_EDGE_LIST = 5;

    const DIRECTED = true;
    const UNDIRECTED = false;

    /**
     * List of edges (unique)
     * @var Edge[]
     */
    protected $edgeList = [];
    /**
     * List of vertices (unique)
     * @var Vertex[]
     */
    protected $vertexList = [];

    /**
     * List of edges ordered by weight
     * @var \SplPriorityQueue
     */
    protected $priorityEdgeList;

    /**
     * Build a new Graph
     * if filename is empty / not supplied, this will create an empty graph,
     * otherwise the graph will be filled from the specified file
     *
     * @param string $fileName
     * @param int $fileType
     * @param bool $directed
     * @throws \Exception
     */
    public function __construct($fileName = '', $fileType = self::EDGE_LIST, $directed = self::DIRECTED) {
        if (!empty($fileName)) {
            $handle = fopen($fileName, 'r');

            if ($handle) {
                $vertexCount = intval(trim(fgets($handle)));
                if ($vertexCount === false) {
                    throw new \Exception('no content', 204);
                }

                switch ($fileType) {
                    case self::EDGE_LIST:
                        $this->importEdgeList($handle, $directed);
                        break;
                    case self::ADJACENCY_MATRIX:
                        $this->importAdjacencyMatrix($handle, $directed);
                        break;
                    case self::WEIGHTED_EDGE_LIST:
                        $this->priorityEdgeList = new \SplPriorityQueue();
                        $this->importWeightedEdgeList($handle, $directed);
                        break;
                    case self::BALANCED_EDGE_LIST:
                        $this->importBalancedEdgeList($handle, $vertexCount);
                        break;
                    case self::BIPARTED_EDGE_LIST:
                        $this->importBipartedEdgeList($handle, $vertexCount);
                        $vertexCount += 2;
                        break;
                }

                if (count($this->vertexList) <> $vertexCount) {
                    sort($this->vertexList);
                    sort($this->edgeList);
                    throw new \Exception('imported vertices count doesn\'t match specified vertex count');
                }
            }
        }
    }

    /**
     * Import EdgeList (2xN)
     *
     * @param resource $handle
     * @param bool $directed
     */
    private function importEdgeList($handle, $directed)
    {
        while ($line = trim(fgets($handle))) {
            list($from, $to) = explode("\t", $line);

            if (!array_key_exists($from, $this->vertexList)) {
                $this->vertexList[$from] = new Vertex($from);
            }
            if (!array_key_exists($to, $this->vertexList)) {
                $this->vertexList[$to] = new Vertex($to);
            }

            $edgeFromTo = $this->vertexList[$from]->connect($this->vertexList[$to]);
            $edgeList[$edgeFromTo->getId()] = $edgeFromTo;
            // if undirected add the opposite link implicitly
            if (!$directed) {
                $edgeToFrom = $this->vertexList[$to]->connect($this->vertexList[$from]);
                $edgeList[$edgeToFrom->getId()] = $edgeToFrom;
            }
        }
    }

    /**
     * Import Adjacency Matrix (NxN)
     *
     * @param resource $handle
     * @param bool $directed
     */
    private function importAdjacencyMatrix($handle, $directed)
    {
        $lineNo = 0;
        while ($line = trim(fgets($handle))) {
            $row = explode("\t", $line);
            $row = array_keys(array_filter($row));

            if (!array_key_exists($lineNo, $this->vertexList)) {
                $this->vertexList[$lineNo] = new Vertex($lineNo);
            }

            foreach ($row as $vertexIdx) {
                if (!array_key_exists($vertexIdx, $this->vertexList)) {
                    $this->vertexList[$vertexIdx] = new Vertex($vertexIdx);
                }
                $edgeFromTo = $this->vertexList[$lineNo]->connect($this->vertexList[$vertexIdx]);
                $this->edgeList[] = $edgeFromTo;
                // if undirected add the opposite link implicitly
                if (!$directed) {
                    $edgeToFrom = $this->vertexList[$vertexIdx]->connect($this->vertexList[$lineNo]);
                    $this->edgeList[] = $edgeToFrom;
                }
            }

            $lineNo++;
        }
    }

    /**
     * Import weighted EdgeList (2xN)
     *
     * @param resource $handle
     * @param bool $directed
     */
    private function importWeightedEdgeList($handle, $directed)
    {
        while ($line = trim(fgets($handle))) {
            list($from, $to, $weight) = explode("\t", $line);

            if (!array_key_exists($from, $this->vertexList)) {
                $this->vertexList[$from] = new Vertex($from);
            }
            if (!array_key_exists($to, $this->vertexList)) {
                $this->vertexList[$to] = new Vertex($to);
            }

            $edgeFromTo = $this->vertexList[$from]->connect($this->vertexList[$to], $weight);
            $this->edgeList[$edgeFromTo->getId()] = $edgeFromTo;

            // if undirected add the opposite link implicitly
            if (!$directed) {
                $edgeToFrom = $this->vertexList[$to]->connect($this->vertexList[$from], $weight);
                $this->edgeList[$edgeToFrom->getId()] = $edgeToFrom;
            }
        }
    }

    /**
     * Import weighted EdgeList with costs and vertex-balances
     *
     * @param resource $handle
     * @param int $vertexCount
     */
    private function importBalancedEdgeList($handle, $vertexCount)
    {
        // Create Vertices with balances
        for ($i = 0; $i < $vertexCount; $i++) {
            $balance = trim(fgets($handle));
            $vertex = new Vertex($i);
            $vertex->setBalance($balance);
            $this->vertexList[$vertex->getId()] = $vertex;
        }

        // Create edges from remaining lines
        while ($line = trim(fgets($handle))) {
            list($from, $to, $cost, $capacity) = explode("\t", $line);
            $edgeFromTo = $this->vertexList[$from]->connect($this->vertexList[$to], $capacity);
            $edgeFromTo->setCost($cost);
            $this->edgeList[$edgeFromTo->getId()] = $edgeFromTo;
        }
    }

    /**
     * Import EdgeList and group vertices in two halves
     *
     * @param $handle
     * @param $vertexCount
     */
    private function importBipartedEdgeList($handle, $vertexCount)
    {
        $groupVertexCount = intval(trim(fgets($handle)));

        $superSource = new Vertex(-1);
        $this->vertexList[$superSource->getId()] = $superSource;
        $superSink = new Vertex(-2);
        $this->vertexList[$superSink->getId()] = $superSink;

        while ($line = trim(fgets($handle))) {
            list($from, $to) = explode("\t", $line);
            /**
             * @var Vertex $vertexA ,
             * @var Vertex $vertexB
             */
            list($vertexA, $vertexB) = $this->importEdge($from, $to);
            if ($vertexA->getId() < $groupVertexCount) {
                $superEdgeA = $superSource->connect($vertexA, 1.0);
            } else {
                $superEdgeA = $vertexA->connect($superSink, 1.0);
            }
            $this->edgeList[$superEdgeA->getId()] = $superEdgeA;

            if ($vertexB->getId() < $groupVertexCount) {
                $superEdgeB = $superSource->connect($vertexB, 1.0);
            } else {
                $superEdgeB = $vertexB->connect($superSink, 1.0);
            }
            $this->edgeList[$superEdgeB->getId()] = $superEdgeB;
        }
    }

    /**
     * Import a simple Edge and return the first vertex of that edge
     *
     * @param $from
     * @param $to
     * @return Vertex
     */
    private function importEdge($from, $to)
    {
        if (!array_key_exists($from, $this->vertexList)) {
            $vertexA = new Vertex($from);
            $this->vertexList[$vertexA->getId()] = $vertexA;
        } else {
            $vertexA = $this->vertexList[$from];
        }

        if (!array_key_exists($to, $this->vertexList)) {
            $vertexB = new Vertex($to);
            $this->vertexList[$vertexB->getId()] = $vertexB;
        } else {
            $vertexB = $this->vertexList[$to];
        }

        $edgeFromTo = $vertexA->connect($vertexB, 1.0);
        $this->edgeList[$edgeFromTo->getId()] = $edgeFromTo;
        return [$vertexA, $vertexB];
    }

    /**
     * Add all Edges from the supplied array to this graph
     *
     * @param Edge[] $edgeList
     * @param bool $directed
     */
    public function addAll(array $edgeList, $directed = self::DIRECTED) {
        foreach ($edgeList as $edge) {
            $aID = $edge->getA()->getId();
            $bID = $edge->getB()->getId();

            if (array_key_exists($aID, $this->vertexList)) {
                $edgeA = $this->vertexList[$aID];
            } else {
                $edgeA = new Vertex($edge->getA()->getId());
            }
            $this->vertexList[$edgeA->getId()] = $edgeA;


            if (array_key_exists($bID, $this->vertexList)) {
                $edgeB = $this->vertexList[$bID];
            } else {
                $edgeB = new Vertex($edge->getB()->getId());
            }
            $this->vertexList[$edgeB->getId()] = $edgeB;

            $newEdge = $edgeA->connect($edgeB, $edge->getCapacity());
            $this->edgeList[$newEdge->getId()] = $newEdge;

            if (!$directed) {
                $edgeBtoA = $edgeB->connect($edgeA, $edge->getCapacity());
                $this->edgeList[$edgeBtoA->getId()] = $edgeBtoA;
            }
        }
    }

    /**
     * Get an ordered-by-weight list of edges in this graph
     *
     * @return \SplPriorityQueue
     */
    public function getPriorityEdgeList() {
        $priorityList = new \SplPriorityQueue();
        foreach ($this->edgeList as $edge) {
            $priority = ($edge->getCapacity() == 0) ? 0 : 1 / $edge->getCapacity();
            $priorityList->insert($edge, $priority);
        }
        return $priorityList;
    }

    /**
     * Get the number of vertices belonging to this Graph
     *
     * @return int
     */
    public function getVertexCount() {
        return count($this->vertexList);
    }

    /**
     * Reset the visited state of all vertices belonging to this Graph
     *
     * @throws \Exception
     */
    public function resetVertices() {
        if (!array_walk($this->vertexList, function(Vertex $vertex) {$vertex->unvisit();})) {
            throw new \Exception('Resetting visited state failed');
        }
    }

    /**
     * Get the list of vertices belonging to this Graph
     *
     * @return Vertex[]
     */
    public function getVertexList() {
        return $this->vertexList;
    }

    /**
     * Get the list of edges composing this Graph
     *
     * @return Edge[]
     */
    public function getEdgeList()
    {
        return $this->edgeList;
    }

    /**
     * Find the Edge between two given Vertices in this Graph
     *
     * @param int $from from-Vertex Id
     * @param int $to to-Vertex Id
     * @return null|Edge
     */
    public function getEdge($from, $to)
    {
        $from = $this->getVertex($from);
        $to = $this->getVertex($to);
        $fromNeighbors = $from->getNeighborEdges();
        foreach($fromNeighbors as $neighborEdge) {
            if ($neighborEdge->getB()->getId() === $to->getId()) {
                return $neighborEdge;
            }
        }

        return null;
    }

    /**
     * Get either the specified vertex or a random vertex from this graph
     *
     * @param int|boolean $id
     * @return Vertex|boolean
     */
    public function getVertex($id = false)
    {
        if ($id === false) {
            return $this->vertexList[array_rand($this->vertexList)];
        } else {
            return array_key_exists($id, $this->vertexList) ? $this->vertexList[$id] : false;
        }
    }

    public function addEdge(Edge $edge)
    {
        $this->edgeList[$edge->getId()] = $edge;
    }

    public function removeEdge(Edge $edge)
    {
        $edge = $this->edgeList[$edge->getId()];
        $edge->getA()->removeNeighborEdge($edge);
        $edge->getB()->removeNeighborEdge($edge);

        unset($this->edgeList[$edge->getId()]);
    }

    public function __clone()
    {
        $this->vertexList = [];
        foreach ($this->edgeList as $edge) {
            $a = $edge->getA();
            $b = $edge->getB();

            $vertexA = $this->getVertex($a->getId()) ?: new Vertex($a->getId(), $a->getBalance());
            $vertexB = $this->getVertex($b->getId()) ?: new Vertex($b->getId(), $b->getBalance());

            $edgeAB = $vertexA->connect($vertexB);
            $edgeAB->setFlow($edge->getFlow());
            $edgeAB->setCapacity($edge->getCapacity());
            $edgeAB->setCost($edge->getCost());

            $this->edgeList[$edgeAB->getId()] = $edgeAB;
            $this->vertexList[$a->getId()] = $vertexA;
            $this->vertexList[$b->getId()] = $vertexB;
        }
    }
}