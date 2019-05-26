<?php

namespace App\Http\Controllers;

use App\Tag;
use App\Cafe;
use App\CafeItem;
use App\CafeMenu;
use App\CafeMember;
use App\CafeItemTag;
use App\CafeCategory;
use Illuminate\Http\Request;
use App\Service\SecurityService;
use App\Http\Service\IndexService;
use App\Http\Service\UploadService;
use Illuminate\Support\Facades\Auth;
use App\Http\Service\NotificationService;
use Symfony\Component\HttpFoundation\Response;

class CafeItemController extends Controller
{
    public function index(Request $request,IndexService $indexService) {
        //get queries
        $page = $request->query('p');
        $start = $request->query('s');
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            $item = $request->query('item');
            $item = $item == null && !is_int($item) ? false : CafeItem::find($item);
            if($item) {
                return response()->json([
                    "item" => $item,
                    'cafe' => $cafe
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
                'error' => 'Resource not found'
            ],Response::HTTP_BAD_REQUEST);  
        }
    }



    /**
     * create item
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
     * edit item
     */
    public function edit(Request $request) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe); 
        if($cafe) {
            //get menu
            $item = $request->query('item');
            $item = $item == null && !is_int($item) ? false : CafeItem::find($item);
            if($item) {
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
                                [true,'item'=>$item],
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
     * store item
     */
    public function store(Request $request,UploadService $uploadService,NotificationService $notify) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe)? false : Cafe::find($cafe);
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
                    //check if menu is set
                    $menu = $request->query('menu');
                    $menu = $menu == null && !is_int($menu) ? null : CafeMenu($menu);
                    $menu = $menu == true ? $menu->id : null;
                    //check for category
                    $category = $request->query('category');
                    $category = $category == null && !is_int($category) ? null : CafeCategory::find($category);
                    $category = $category == true ? $category->id : null;
                    //validate request
                    $request->validate([
                        "name" => ['required','string','max:255'],
                        "price" => ['required','numeric'],
                        "description" => ['string'],
                        "image" => ['required','file','image','mimetypes:image/png,image/jpg','max:3000'],
                        'tags' => ['required','string'],
                        "discount" => ['numeric']
                    ]);
                    //get fields
                    $data = $request->only('name','description','price','image','tags','discount');
                    //get imageName
                    $imageName = $uploadService->uploadImage("images\cafe\items\about",$data['image']);
                    $item = CafeItem::create([
                        "name" => $data['name'],
                        "price" => (float)$data['price'],
                        "discount" => (float)$data['discount'],
                        "about" => $data['description'],
                        "image" => $imageName,
                        "cafe" => $cafe->id,
                        "menu" => $menu,
                        "category" => $category
                    ]);
                    if($item) {
                        //get tags
                        $tags = $data['tags'];
                        $tags = explode(',',$tags);
                        foreach($tags as $tag) {
                            if(!Tag::where('tagName',$tag)->first()) {
                                $newTag = Tag::create(["tagName" => $tag]);
                                if($newTag) {
                                    CafeItemTag::create([
                                        "tag" => $newTag->id,
                                        "item" => $item->id
                                    ]);
                                }
                            }
                        }
                        //get cafe members
                        $cafeMembers = CafeMember::where([
                            ['cafe',$cafe->id],
                            ['status','confirmed']
                        ])->get();
                        //notify cafe members
                        foreach($cafeMembers as $cafeMember){
                            if($cafeMember->user()->first()->id == Auth::guard('api')->id()) {
                                $notify->createNotification($cafeMember->user()->first()->id,"item",$item->id,"'".$item->name."' was added by you.",'/cafe/item?cafe='.$cafe->id.'&item='.$item->id);
                            }else{
                                $notify->createNotification($cafeMember->user()->first()->id,"item",$item->id,"'".$item->name."' was added.",'/cafe/item?cafe='.$cafe->id.'&item='.$item->id);
                            }
                        }
                        return response()->json([
                            "item" => $item,
                            "cafe" => $cafe
                        ],Response::HTTP_CREATED);
                    }
                    else{
                        return response()->json([
                            "error" => "Internal server error"
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
