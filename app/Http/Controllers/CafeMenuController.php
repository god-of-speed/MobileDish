<?php

namespace App\Http\Controllers;

use App\Cafe;
use App\CafeItem;
use App\CafeMenu;
use App\CafeMember;
use App\CafeCategory;
use Illuminate\Http\Request;
use App\Http\Service\IndexService;
use Illuminate\Support\Facades\Auth;
use App\Http\Service\SecurityService;
use App\Http\Service\NotificationService;
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
            $menu = $menu == null && !is_int($menu) ? false : CafeMenu::find($menu);
        }
        $filter = $request->query('filter');
        $page = $request->query('p');
        $start = $request->query('s');
        $display = 30;
        if($cafe && $menu) {
            if($filter == 'category') {
                $categories = CafeCategory::where([
                    ['cafe',$cafe->id],
                    ['menu',$menu->id]
                ])
                ->get();
                if($categories){
                    //get pages and start
                    $arr = $indexService->pagination($page,$start,display,$categories);
                    $p = $arr['p'];
                    $s = $arr['s'];
                    $result = CafeCategory::where([
                        ['cafe',$cafe->id],
                        ['menu',$menu->id]
                    ])
                    ->take($display)
                    ->skip($s)
                    ->get();
                    if($result) {
                        return response()->json([
                            "result" => $result,
                            "cafe" => $cafe,
                            "menu" => $menu,
                            'p' => (int)$p,
                            's' => (int)$s
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
                $items = CafeItem::where([
                    ['cafe',$cafe->id],
                    ['menu',$menu->id],
                    ['status','set']
                ])
                ->get();
                if($items) {
                    //get pages and start
                    $arr = $indexService->pagination($page,$start,$display,$items);
                    $p = $arr['p'];
                    $s = $arr['s'];
                    $result = CafeItem::where([
                        ['cafe',$cafe->id],
                        ['menu',$menu->id],
                        ['status','set']
                    ])
                    ->take($display)
                    ->skip($s)
                    ->get();
                    if($result) {
                        return response()->json([
                            "result" => $result,
                            "cafe" => $cafe,
                            "menu" => $menu,
                            'p' => (int)$p,
                            's' => (int)$s
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
    public function create (Request $request) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            if(Auth::guard('api')->user()) {
                //get cafe admin
                $admin = CafeMember::where([
                    ['cafe',$cafe->id],
                    ['right','admin'],
                    ['user',Auth::guard('api')->id()]
                ])
                ->first();
                if($admin) {
                    return response()->json(
                            true,
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
    public function edit(Request $request) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe); 
        if($cafe) {
            //get menu
            $menu = $request->query('menu');
            $menu = $menu == null && !is_int($menu) ? false : Cafe_Menu::find($menu);
            if($menu) {
                if(Auth::guard('api')->user()) {
                    //get cafe admin
                    $admin = CafeMember::where(
                        ['cafe',$cafe->id],
                        ['right','admin'],
                        ['user',Auth::guard('api')->id()]
                    )
                    ->first();
                    if($admin) {
                        return response()->json(
                                true,
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
                $admin = CafeMember::where([
                    ['cafe',$cafe->id],
                    ['right','admin'],
                    ['user',Auth::guard('api')->id()]
                ])
                ->first();
                if($admin) {
                    $request->validate([
                        'name' => ['required','string','max:255'],
                        'description' => ['string']
                    ]);
                    //get data
                    $data = $request->only('name','description');
                    //create menu
                    $menu = CafeMenu::create([
                        "name" => $data['name'],
                        "about" => $data['description'],
                        "cafe" => $cafe->id
                    ]);
                    //get cafe members
                    $cafeMembers = CafeMember::where([
                        ['cafe',$cafe->id],
                        ['status','confirmed']
                    ])->get();
    
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
