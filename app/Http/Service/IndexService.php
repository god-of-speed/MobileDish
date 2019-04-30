<?php
namespace App\Service;

class IndexService {
    public function pagination ($pages,$start,$display,$elements) {
        if($pages && is_int($pages)) {
            $p = $pages;
        }else{
            $p = $elements < $display ? 1 : ceil($elements/$display);
        }
        if($start && is_int($start)) {
            $s = $start;
        }else{
            $s = 0;
        }
        $arr = ['p' => $p, 's' => $s];
        return $arr;
    }
}