<?php
/**
 * Author: Benedikt Bauer
 * Date: 21.04.2015
 * Time: 23:07
 */

namespace Xenzilla\Graph\Algorithm;

class UnionFind {

    public static function find(Node &$node) {
        if ($node->parent !== $node) {
            $node->parent = self::find($node->parent);
        }

        return $node->parent;
    }

    public static function union(Node &$nodeA, Node &$nodeB) {
        $aRoot = self::find($nodeA);
        $bRoot = self::find($nodeB);

        if ($aRoot === $bRoot) {
            return;
        }


        if ($aRoot->rank < $bRoot->rank) {
            $aRoot->parent = $bRoot;
        }
        elseif ($aRoot->rank > $bRoot->rank) {
            $bRoot->parent = $aRoot;
        }
        else {
            $bRoot->parent = $aRoot;
            $aRoot->rank++;
        }

    }
}