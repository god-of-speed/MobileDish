<?php

namespace App\Http\Controllers;

use App\Tag;
use App\Cafe;
use App\CafeItem;
use Illuminate\Http\Request;
use App\Http\Service\IndexService;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{
    public function index (Request $request,IndexService $indexService) {
        //get filter
        $filter = $request->query('filter');
        $pages = $request->query('p');
        $start = $request->query('s');
        $display = 30;
        if($filter == 'cafe') {
            //get all cafes
            $cafes = Cafe::all();
            if($cafes) {
                $arr = $indexService->pagination($pages,$start,$display,$cafes);
                $p = $arr['p'];
                $s = $arr['s'];
                $result = Cafe::take($display)->skip($s)->get();
                if($result) {
                    return response()->json([
                        'result' => $result,
                        'p' => (int)$p,
                        's' => (int)$s
                    ],Response::HTTP_OK);
                }else{
                    return response()->json([
                        "data" => "Not found!"
                    ],Response::HTTP_BAD_REQUEST);
                }
            }else{
                return response()->json([
                    "data" => "No cafe yet!"
                ],Response::HTTP_NO_CONTENT);
            }            
        }
        elseif($filter == 'tag') {
            //get all tags
            $tags = Tag::all();
            if($tags) {
                //get pages and start row
                $arr = $indexService->pagination($pages,$start,$display,$tags);
                $p = $arr['p'];
                $s = $arr['s'];
                $result = Tag::take($display)->skip($s)->get();
                if($result) {
                    return response()->json([
                        "result" => $result,
                        'p' => (int)$p,
                        's' => (int)$s
                    ],Response::HTTP_OK);
                }else{
                    return response()->json([
                        "data" => "Not found!"
                    ],Response::HTTP_BAD_REQUEST);
                }
            }
            else{
                return response()->json([
                    "data" => "No tags yet!"
                ],Response::HTTP_NO_CONTENT);
            }
        }
        else{
            //get all items
            $items = CafeItem::where('status','set')->get();
            if($items) {
                //get pages and start row
                $arr = $indexService->pagination($pages,$start,$display,$items);
                $p = $arr['p'];
                $s = $arr['s'];
                $result = CafeItem::where('status','set')->take($display)->skip($s)->get();
                if($result) {
                    return response()->json([
                        'result' => $result,
                        'p' => (int)$p,
                        's' => (int)$s
                    ],Response::HTTP_OK);
                }else{
                    return response()->json([
                        "data" => "Not found!"
                    ],Response::HTTP_BAD_REQUEST);
                }
            }else{
                return response()->json([
                    "data" => "No tags yet!"
                ],Response::HTTP_NO_CONTENT);
            }
        }
    }
}
