<?php

namespace App\Http\Controllers;

use App\Cafe;
use App\CafeItem;
use App\Cafe_Menu;
use App\Cafe_Member;
use App\Cafe_Category;
use Illuminate\Http\Request;
use App\Service\IndexService;
use App\Service\SecurityService;
use App\Service\NotificationService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CafeMenuController extends Controller
{
    public function index(Request $request,IndexService $indexService) {
        //get queries
        $cafe = $request->query('cafe');
        //get cafe
        $cafe =  $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            $menu = $request->query('menu');
            //get menu
            $menu = $menu == null && !is_int($menu) ? false : Cafe_Menu::find($menu);
        }
        $filter = $request->query('filter');
        $page = $request->query('p');
        $start = $request->query('s');
        $display = 30;
        if($cafe && $menu) {
            if($filter == 'category') {
                $categories = Cafe_Category::where(
                    ['cafe',$cafe->id],
                    ['menu',$menu->id]
                )
                ->get();
                if($categories){
                    //get pages and start
                    $arr = $indexService->pagination($page,$start,display,$categories);
                    $p = $arr['p'];
                    $s = $arr['s'];
                    $result = Cafe_Category::where(
                        ['cafe',$cafe->id],
                        ['menu',$menu->id]
                    )
                    ->take($display)
                    ->skip($s)
                    ->get();
                    if($result) {
                        return response()->json([
                            "result" => $result,
                            "cafe" => $cafe,
                            "menu" => $menu,
                            'p' => $p,
                            's' => $s
                        ],Response::HTTP_OK);
                    }
                    else{
                        return response()->json([
                            "data" => "Not found!"
                        ],Response::HTTP_BAD_REQUEST);
                    }
                }
                else{
                    return response()->json([
                        "data" => "No category yet!"
                    ],Response::HTTP_NO_CONTENT);
                }
            }
            else{
                //get items
                $items = CafeItem::where(
                    ['cafe',$cafe->id],
                    ['menu',$menu->id]
                )
                ->get();
                if($items) {
                    //get pages and start
                    $arr = $indexService->pagination($page,$start,$display,$items);
                    $p = $arr['p'];
                    $s = $arr['s'];
                    $result = CafeItem::where(
                        ['cafe',$cafe->id],
                        ['menu',$menu->id]
                    )
                    ->take($display)
                    ->skip($s)
                    ->get();
                    if($result) {
                        return response()->json([
                            "result" => $result,
                            "cafe" => $cafe,
                            "menu" => $menu,
                            'p' => $p,
                            's' => $s
                        ],Response::HTTP_OK);
                    }
                    else{
                        return response()->json([
                            "data" => "Not found!"
                        ],Response::HTTP_BAD_REQUEST);
                    }
                }
                else{
                    return response()->json([
                        "data" => "No item yet!"
                    ],Response::HTTP_NO_CONTENT);
                }
            }
        }
        return response()->json([
            'error' => 'Resource not found'
        ],Response::HTTP_BAD_REQUEST); 
    }





    /**
     * create menu 
    */
    public function create (Request $request,SecurityService $securityService) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            if(Auth::guard('api')->user()) {
                //get cafe admin
                $admins = Cafe_Member::where(
                    ['cafe',$cafe->id],
                    ['right','admin']
                )
                ->get();
                if($securityService->checkUserIsAdmin(Auth::guard('api')->id(),$admins)) {
                    return response()->json(
                            true,
                            ['cafe' => $cafe],
                            Response::HTTP_OK);
                }
                else{
                    return response()->json(false,Response::HTTP_FORBIDDEN);
                }
            }
            else{
                return response()->json(false,Response::HTTP_UNAUTHORIZED);
            }
        }
        else{
            return response()->json([
                'error' => 'Resource not found'
            ],Response::HTTP_BAD_REQUEST); 
        }
    }





    /**
     * edit menu
     *
     */
    public function edit(Request $request,SecurityService $securityService) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe); 
        if($cafe) {
            //get menu
            $menu = $request->query('menu');
            $menu = $menu == null && !is_int($menu) ? false : Cafe_Menu::where(
                ['cafe', $cafe->id],
                ['id',$menu->id]
            )
            ->get();
            if($menu) {
                if(Auth::guard('api')->user()) {
                    //get cafe admin
                    $admins = Cafe_Member::where(
                        ['cafe',$cafe->id],
                        ['right','admin']
                    )
                    ->get();
                    if($securityService->checkUserIsAdmin(Auth::guard('api')->id(),$admins)) {
                        return response()->json(
                                true,
                                ['menu' => $menu],
                                Response::HTTP_OK);
                    }
                    else{
                        return response()->json(false,Response::HTTP_FORBIDDEN);
                    }
                }
                else{
                    return response()->json(false,Response::HTTP_UNAUTHORIZED);
                }
            }
            else{
                return response()->json([
                    'error' => 'Resource not found'
                ],Response::HTTP_BAD_REQUEST); 
            }
        }
        else{
            return response()->json([
                'error' => 'Resource not found'
            ],Response::HTTP_BAD_REQUEST); 
        }
    }





    /**
     * store menu
     */
    public function store(Request $request,NotificationService $notify) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            if(Auth::guard('api')->user()) {
                //get cafe admin
                $admins = Cafe_Member::where(
                    ['cafe',$cafe->id],
                    ['right','admin']
                )
                ->get();
                if($securityService->checkUserIsAdmin(Auth::guard('api')->id(),$admins)) {
                    $request->validate([
                        'name' => ['required','string','max:255'],
                        'description' => ['string']
                    ]);
                    //get post request
                    $name = $request->only('name');
                    $description = $request->only('description') == null ? null : $request->only('description');
                    //create menu
                    $menu = Cafe_Menu::create([
                        "name" => $name,
                        "abouts" => $description,
                        "cafe" => $cafe->id
                    ]);
                    //get cafe members
                    $cafeMembers = Cafe_Member::where(
                        ['cafe',$cafe->id],
                        ['status','confirmed']
                    )->get();
                    //notify cafe members
                    foreach($cafeMembers as $cafeMember){
                        if($cafeMember->user()->first()->id == Auth::guard('api')->id()) {
                            $notify->createNotification($cafeMember->user()->first()->id,"menu",$menu->id,"'".$menu->name."' was created by you.",'/cafe/about?cafe='.$cafe->id);
                        }else{
                            $notify->createNotification($cafeMember->user()->first()->id,"menu",$menu->id,"'".$menu->name."' was created.",'/cafe/about?cafe='.$cafe->id);
                        }
                    }
                    if($menu) {
                        return response()->json([
                            "menu" => $menu
                        ],Response::HTTP_CREATED); 
                    }
                    else{
                        return response()->json([
                            'error' => 'Internal Server Error'
                        ],Response::HTTP_INTERNAL_SERVER_ERROR); 
                    }
                }
                else{
                    return response()->json([
                        "error" => "Forbidden"
                    ],Response::HTTP_FORBIDDEN);
                }
            }
            else{
                return response()->json([
                    "error" => "Unauthorized"
                ],Response::HTTP_UNAUTHORIZED);
            }
        } 
        else{
            return response()->json([
                'error' => 'Resource not found'
            ],Response::HTTP_BAD_REQUEST); 
        }
    }
}
