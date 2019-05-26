<?php

namespace App\Http\Controllers;

use App\Cafe;
use App\CafeItem;
use App\CafeMember;
use App\CafeMenu;
use App\CafeCategory;
use Illuminate\Http\Request;
use App\Service\SecurityService;
use App\Http\Service\IndexService;
use Illuminate\Support\Facades\Auth;
use App\Http\Service\NotificationService;
use Symfony\Component\HttpFoundation\Response;

class CafeCategoryController extends Controller
{
    public function index(Request $request,IndexService $indexService) {
        //get start and page
        $page = $request->query('p');
        $start = $request->query('s');
        //set display
        $display = 30;
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            //get category
            $category = $request->query('category');
            $category = $category == null && !is_int($category) ? false : CafeCategory::find($category);
            if($category) {
                //get items
                $items = CafeItem::where([
                    ['cafe',$cafe->id],
                    ['category',$category->id],
                    ['status','set']
                ])
                ->get();
                if($items) {
                    //get page and start
                    $arr = $indexService->pagination($page,$start,$display,$items);
                    $p = $arr['p'];
                    $s = $arr['s'];
                    $result = CafeItem::where([
                        ['cafe',$cafe],
                        ['category',$category],
                        ['status','set']
                    ])
                    ->take(30)
                    ->skip($s)
                    ->get();
                    if($result) {
                        return response()->json([
                            "result" => $result,
                            "category" => $category,
                            "cafe" => $cafe,
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
            else{
                return response()->json([
                    "error" => "Resource not found"
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
     * create category
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
     * edit category
     */
    public function edit(Request $request) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe); 
        if($cafe) {
            //get menu
            $category = $request->query('category');
            $category = $category == null && !is_int($category) ? false : CafeCategory::find($category);
            if($category) {
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
        else{
            return response()->json([
                'error' => 'Resource not found'
            ],Response::HTTP_BAD_REQUEST); 
        }
    }




    /**
     * store category
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
                    $menu = $request->query('menu');
                    $menu = $menu == null && !is_int($menu) ? null : CafeMenu::find($menu);
                    //validate post request
                    $request->validate([
                        "name" => ['required','string','max:255'],
                        "description" => ['string']
                    ]);
                    //get data
                    $data = $request->only('name','description');
                    //save category
                    $category = CafeCategory::create([
                        "name" => $data['name'],
                        "about" => $data['description'],
                        "cafe" => $cafe->id,
                        "menu" => $menu->id
                    ]);
                    if($category) {
                        //get cafe members
                        $cafeMembers = CafeMember::where([
                            ['cafe',$cafe->id],
                            ['status','confirmed']
                        ])->get();
                        //notify cafe members
                        foreach($cafeMembers as $cafeMember){
                            if($cafeMember->user()->first()->id == Auth::guard('api')->id()) {
                                $notify->createNotification($cafeMember->user()->first()->id,"category",$category->id,"'".$category->name."' was created by you.",'/cafe/about?cafe='.$cafe->id);
                            }else{
                                $notify->createNotification($cafeMember->user()->first()->id,"category",$category->id,"'".$category->name."' was created.",'/cafe/about?cafe='.$cafe->id);
                            }
                        }
                        return response()->json([
                            "category" => $category
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
