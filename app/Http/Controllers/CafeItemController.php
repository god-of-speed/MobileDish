<?php

namespace App\Http\Controllers;

use App\Tag;
use App\Cafe;
use App\CafeItem;
use App\Cafe_Menu;
use App\Cafe_Member;
use App\CafeItemTag;
use App\Cafe_Category;
use Illuminate\Http\Request;
use App\Service\IndexService;
use App\Service\SecurityService;
use App\Service\NotificationService;
use Illuminate\Support\Facades\Auth;
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
     * edit item
     */
    public function edit(Request $request,SecurityService $securityService) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe); 
        if($cafe) {
            //get menu
            $item = $request->query('item');
            $item = $item == null && !is_int($item) ? false : CafeItem::where(
                ['cafe', $cafe->id],
                ['id',$item->id]
            )
            ->get();
            if($item) {
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
                                ['item' => $item],
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
    public function store(Request $request,NotificationService $notify) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe)? false : Cafe::find($cafe);
        if($cafe) {
            if(Auth::guard('api')->user()) {
                //get cafe admin
                $admins = Cafe_Member::where(
                    ['cafe',$cafe->id],
                    ['right','admin']
                )
                ->get();
                if($securityService->checkUserIsAdmin(Auth::guard('api')->id(),$admins)) {
                    //check if menu is set
                    $menu = $request->query('menu');
                    $menu = $menu == null && !is_int($menu) ? null : Cafe_Menu($menu);
                    //check for category
                    $category = $request->query('category');
                    $category = $category == null && !is_int($category) ? null : Cafe_Category::find($category);
                    //validate request
                    $request->validate([
                        "name" => ['required','string','max:255'],
                        "price" => ['required','numeric'],
                        "description" => ['string'],
                        "image" => ['required','file','image','size:2000'],
                        'tags' => ['required','string'],
                        "discount" => ['numeric']
                    ]);
                    //get fields
                    $data = $request->only('name','description','price','image','tags');
                    $name = $data['name'];
                    $price = (float) $data['price'];
                    $description = $data['description'];
                    $image = $data['image'];
                    $discount = (float) $data['discount'];
                    $oldPrice = (float) 0;

                    $item = CafeItem::create([
                        "name" => $name,
                        "price" => $price,
                        "discount" => $discount,
                        "oldPrice" => $oldPrice,
                        "about" => $description,
                        "image" => $image,
                        "cafe" => $cafe,
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
                        $cafeMembers = Cafe_Member::where(
                            ['cafe',$cafe->id],
                            ['status','confirmed']
                        )->get();
                        //notify cafe members
                        foreach($cafeMembers as $cafeMember){
                            if($cafeMember->user()->first()->id == Auth::guard('api')->id()) {
                                $notify->createNotification($cafeMember->user()->first()->id,"'".$item->name."' was added by you.",'/cafe/item?cafe='.$cafe->id.'&item='.$item->id);
                            }else{
                                $notify->createNotification($cafeMember->user()->first()->id,"'".$item->name."' was added.",'/cafe/item?cafe='.$cafe->id.'&item='.$item->id);
                            }
                        }
                        return response()->json([
                            "item" => $item,
                            "cafe" => $cafe,
                            "category" => $category,
                            "menu" => $menu
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
