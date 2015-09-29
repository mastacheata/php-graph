# php-graph
Graph (datastructure) library in PHP
Contains generic datastructures as well as implementations of a few common algorithms


This is my solution to the practice in SS2015 MMI lecture by Prof. Hoever at FH Aachen
See: http://www.hoever.fh-aachen.de/index.php?option=com_content&view=article&id=11&Itemid=5

# Algorithms
* DFS / Tiefensuche
* BFS / Breitensuche
* Union-Find
* MST
  * Prim
  * Kruskal
* Traveling Salesman & Hamilton Graphs
  * Nearest-Neighbor
  * DoubleTree / Doppelter Baum
* Kürzeste Wege
  * Dijkstra
  * Moore-Bellman-Ford
* Maximum Flow / Maximaler Fluss
  * Ford-Fulkerson
  * Edmond-Karps
* Cost-minimal Flow / Kostenminimaler Fluss
  * Cycle-Cancelling
  * Successive-shortest-path
* Matchings

# Graph files / Resources
* Graph1.txt
  * Adjacency Matrix
  * Line 1: Nodecount, following lines: Adjacency matrix
* Graph2.txt (small), Graph3.txt (huge, 4 connectivity components), Graph4.txt (huge, 4 connectivity components)
  * Edgelist
  * Line 1: Nodecount, following lines: Edges (i->j) with Numbering scheme: 0..Nodecount-1
* G_1_2.txt, G_1_20.txt, G_1_200.txt, G_10_20.txt, G_10_200.txt, G_100_200.txt
  * Weighted Graphs as Edgelist
  * G_x_y, where x is number of nodes in multiples of 1,000 and y is the number of edges in multiples of 1,000
  * Line 1: Nodecount, following lines: Edges (i->j) and weight with Numbering scheme 0..Nodecount-1
* K_10.txt (38.41), K_10e.txt (27.26), K_12.txt (45.19), K_12e.txt (36.13), K_15.txt, K_15e.txt, K_20.txt, K_30.txt, K_50.txt, K_70.txt, K_100.txt
  * Complete weighted Graphs as Edgelist
  * K_z, where z is the nodecount, in K_ze the 3rd column is the distance from points in the plane
  * Line 1: Nodecount, following lines: Edges (i->j) and weight with Numbering scheme 0..Nodecount-1
  * Numbers in brackets are the optimal round trips
* Wege1.txt, Wege2.txt, Wege3.txt
  * same format as K_z
  * Shortest directed paths:
    * Wege1, 2->0: 6
    * Wege2, 2->0: 2
    * Wege3, 2->0: NEGATIVE CYCLE!
    * G_1_2, 0->1: 5.54417 (directed) vs. 2.36796 (undirected)
* Fluss.txt
  * Edgelist with capacities
  * Line 1: Nodecount, following lines: Edges (i->j) and capacity with Numbering scheme 0..Nodecount-1
  * Maximum Flows:
    * Fluss, 0->7: 4
    * G_1_2, use 3rd column as capacity, 0->7: 0.735802
* Kostenminimal1.txt (3), Kostenminimal2.txt (no b-Flow possible), Kostenminimal3.txt (no b-Flow possible), Kostenminimal4.txt (1537), Kostenminimal5.txt (0)
  * Balances and Edgelist with cost and flow
  * Line 1: Nodecount n, following n lines: balances, following lines: Edges (i->j) and capacity and flow with Numbering scheme 0..Nodecount-1
  * Numbers in brackets are the costs of the cost minimal flow
* Matching_100_100.txt, Matching2_100_100.txt
  * Line 1: Nodecount, Line 2: Number of nodes in the first matching group (0..Matchcount-1), following lines: Egdes (i->j) with Numbering scheme 0..Nodecount-1
  * Matching has 100 matching-edges, Matching 2 has onlz 99 matching-edges
