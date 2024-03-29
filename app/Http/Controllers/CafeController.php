<?php

namespace App\Http\Controllers;

use App\Tag;
use App\Cafe;
use App\CafeTag;
use App\CafeItem;
use App\CafeMenu;
use App\CafeMember;
use App\CafeCategory;
use App\Notification;
use App\CafeCustomRequest;
use Illuminate\Http\Request;
use App\Http\Service\CafeService;
use App\Http\Service\IndexService;
use App\Http\Service\UploadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Service\NotificationService;
use Symfony\Component\HttpFoundation\Response;

class CafeController extends Controller
{
    /**
     * create cafe
    */
    public function create(Request $request) {
        //check if user is allowed
        if(Auth::guard('api')->user()) {
            return response()->json(true,Response::HTTP_OK);
        }
        else{
            return response()->json(false,Response::HTTP_UNAUTHORIZED);
        }
    }




    /**
     * edit cafe
     */
    public function edit(Request $request,SecurityService $securityService) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        //check if user is allowed
        if($cafe) {
            if(Auth::guard('api')->user()) {
                //get cafe admin
                $admins = Cafe_Member::where(
                    ['cafe',$cafe],
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
     * store cafe
    */
    public function store(Request $request,UploadService $uploadService,NotificationService $notify,CafeService $cafeService) {
        //get user
        $user = Auth::guard('api')->user();
        //get request data
        $data = $request->only('name','country','state','location','currency','tags','image');
        //validate data
        $request->validate([
            'name' => ['required','string','max:255'],
            'country' => ['required','string','max:255'],
            'state' => ['required','string','max:255'],
            'location' => ['required','string','max:255'],
            'currency' => ['required','string','max:255'],
            'tags' => ['required','string'],
            "image" => ['required','file','image','max:2000']
        ]);
        //upload image
        $imageName = $uploadService->uploadImage("images\cafe\about",$data['image']);

        //create cafe
        $cafe = Cafe::create([
            'name' => $data['name'],
            'country' => $data['country'],
            'state' => $data['state'],
            'location' => $data['location'],
            'currency' => $data['currency'],
            "picture" => $imageName
        ]);
        if($cafe) {
            //create cafe tag
            $tags = explode(',',$data['tags']);
            foreach($tags as $tag) {
                //check if tag exist
                $alreadyExist = Tag::where('tagName',$tag)->first();
                if(!$alreadyExist) {
                    $newTag = Tag::create([
                        'tagName' => $tag
                    ]);
                    if($tag) {
                        CafeTag::create([
                            'cafe' => $cafe->id,
                            'tag' => $newTag->id
                        ]);
                    }
                }
            }
            //create cafe member
            $cafeMember = CafeMember::create([
                'cafe' => $cafe->id,
                'user' => Auth::guard('api')->id(),
                'right' => 'admin',
                'status' => 'confirmed'
            ]);
            //create cafe wallet
            $cafeWallet = $cafeService->createCafeWallet($cafe,$user);
            //notify user
            $notify->createNotification(Auth::guard('api')->id(),"cafe",$cafe->id,$cafe->name.' was created by you.','/cafe/index?cafe='.$cafe->id);
            //get cafe details
            $members = CafeMember::where('cafe',$cafe->id)->get();
            $customRequests = CafeCustomRequest::where('cafe',$cafe->id)->get();
            $cafeItems = CafeItem::where('cafe',$cafe)->get();
            return response()->json([
                'cafe' => $cafe
            ],Response::HTTP_CREATED);
        }
        return response()->json([
            'error' => 'Error.'
        ],Response::HTTP_INTERNAL_SERVER_ERROR);
    }


    /**
     * cafe index page
     */
    public function index(Request $request,IndexService $indexService) {
        //get cafe, menu and category
        $cafe = $request->query('cafe');
        //check the cafe
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            //get filter
            $filter = $request->query('filter');
            $page = $request->query('p');
            $start = $request->query('s');
            $display = 30;
            if($filter == 'menu') {
                //get all cafe menu
                $menus = CafeMenu::where('cafe',$cafe->id)->get();
                if($menus) {
                    //get pages and start
                    $arr = $indexService->pagination($page,$start,$display,$menus);
                    $p = $arr['p'];
                    $s = $arr['s'];
                    $result = CafeMenu::where('cafe',$cafe->id)->take($display)->skip($s)->get();
                    if($result) {
                        return response()->json([
                            "result" => $result,
                            "cafe" => $cafe,
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
                        "data" => "No menu yet!"
                    ],Response::HTTP_NO_CONTENT);
                }
            }
            elseif($filter ==  'category') {
                //get all cafe category
                $categories = CafeCategory::where('cafe',$cafe->id)->get();
                if($categories) {
                    //get pages and start
                    $arr = $indexService->pagination($page,$start,$display,$categories);
                    $p = $arr['p'];
                    $s = $arr['s'];
                    $result = CafeCategory::where('cafe',$cafe->id)->take($display)->skip($s)->get();
                    if($result) {
                        return response()->json([
                            "result" => $result,
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
                        "data" => "No category yet!"
                    ],Response::HTTP_NO_CONTENT);
                }
            }
            elseif($filter == 'tag') {
                //get cafe tags
                $cafeTags = CafeTag::where('cafe',$cafe->id)->get();
                if($cafeTags) {
                    //get page and start
                    $arr = $indexService->pagination($page,$start,$display,$cafeTags);
                    $p = $arr['p'];
                    $s = $arr['s'];
                    $results = CafeTag::where('cafe',$cafe->id)->take($display)->skip($s)->get();
                    if($results) {
                        //get tags
                        $tags = [];
                        foreach($results as $result) {
                            $tag[] = $result->tag()->first();
                        }
                        return response()->json([
                            "result" => $tags,
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
                        "data" => "No category yet!"
                    ],Response::HTTP_NO_CONTENT);
                }
            }
            else {
                //get items
                $items = CafeItem::where([
                    ['cafe',$cafe],
                    ['status','set']
                ])->get();
                if($items) {
                    //get pages and start
                    $arr = $indexService->pagination($page,$start,$display,$items);
                    $p = $arr['p'];
                    $s = $arr['s'];
                    $result = CafeItem::where([
                        ['cafe',$cafe],
                        ['status','set']
                    ])->take($display)->skip($s)->get();
                    if($result) {
                        return response()->json([
                            "result" => $result,
                            'cafe' => $cafe,
                            'p' => (int)$p,
                            's' => (int)$s
                        ],Response::HTTP_OK);
                    }
                    return response()->json([
                        "data" => "Not found!"
                    ],Response::HTTP_BAD_REQUEST);
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
     * cafe join requests
     */
    public function allCafeJoinRequests(Request $request,IndexService $indexService) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            $requests = CafeMember::where([
                ['cafe',$cafe->id],
                ['status','pending'],
                ['requestType','join']
            ])
            ->get();
            if($requests) {
                //get page and start
                $page = $request->query('p');
                $start = $request->query('s');
                $display = 30;
                $arr = $indexService->pagination($page,$start,$display,$requests);
                $p = $arr['p'];
                $s = $arr['s'];
                //paginate result
                $result = CafeMember::where([
                    ['cafe',$cafe->id],
                    ['status','pending'],
                    ['requestType','join']
                ])
                ->take($display)
                ->skip($s)
                ->get();
                if($result) {
                    return response()->json([
                        "result" => $result,
                        'cafe' => $cafe,
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
                    "data" => "No request yet!"
                ],Response::HTTP_NO_CONTENT);
            }
        }
        else{
            return response()->json([
                'error' => 'Resource not found'
            ],Response::HTTP_BAD_REQUEST); 
        }
    }





    /**
     * cafe invite requests
     */
    public function allCafeInviteRequests(Request $request,IndexService $indexService) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            $requests = CafeMember::where([
                ['cafe',$cafe->id],
                ['status','pending'],
                ['requestType','invite']
            ])
            ->get();
            if($requests) {
                //get page and start
                $page = $request->query('p');
                $start = $request->query('s');
                $display = 30;
                $arr = $indexService->pagination($page,$start,$display,$requests);
                $p = $arr['p'];
                $s = $arr['s'];
                //paginate result
                $result = CafeMember::where([
                    ['cafe',$cafe->id],
                    ['status','pending'],
                    ['requestType','invite']
                ])
                ->take($display)
                ->skip($s)
                ->get();
                if($result) {
                    return response()->json([
                        "result" => $result,
                        'cafe' => $cafe,
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
                    "data" => "No invite yet!"
                ],Response::HTTP_NO_CONTENT);
            }
        }
        else{
            return response()->json([
                'error' => 'Resource not found'
            ],Response::HTTP_BAD_REQUEST); 
        }
    }





    /**
     * confirm join request
     */
    public function confirmJoinRequest(Request $request,NotificationService $notify) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            //get request
            $joinRequest = $request->query('request');
            $joinRequest = $joinRequest == null  && !is_int($joinRequest) ? false : Cafe_Member::find($joinRequest);
            if($joinRequest) {
                //get user
                $user = Auth::guard('api')->user();
                if($user) {
                    //authorize user
                    if(CafeMember::where([
                        ['cafe',$cafe->id],
                        ['right','admin'],
                        ['user',$user->id],
                        ['status','confirmed']
                    ])
                    ->first()
                    ) {
                        $update = $joinRequest->update([
                            "status" => "confirmed"
                        ]);
                        if($update) {
                            //get cafe_members
                            $cafeMembers = CafeMember::where([
                                ['cafe',$cafe->id],
                                ['status','confirmed']
                            ])
                            ->get();
                            foreach($cafeMembers as $member) {
                                if($member->user()->first()->id == $user->id) {
                                    $notify->createNotification($member->user()->id,"joinRequest",$joinRequest->id,"You accepted ".$joinRequest->user()->first()->username." request to join ".$cafe->name,"/cafe/member?cafe=".$cafe->id."&member=".$joinRequest->id);
                                }else{
                                    $notify->createNotification($member->user()->id,"joinRequest",$joinRequest->id,$joinRequest->user()->first()->username." joined ".$cafe->name,"/cafe/member?cafe=".$cafe->id."&member=".$joinRequest->id);
                                }
                            }
                            return response()->json(true,Response::HTTP_OK);
                        }
                        else{
                            return response()->json([
                                "error" => "Internal server error"
                            ],HTTP_INTERNAL_SERVER_ERROR);
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
                    "data" => "Not Found!"
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
     * decline join request
     */
    public function declineJoinRequest(Request $request) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? false : Cafe::find($cafe);
        if($cafe) {
            //get request
            $joinRequest = $request->query('request');
            $joinRequest = $joinRequest == null  && !is_int($joinRequest) ? false : Cafe_Member::find($joinRequest);
            if($joinRequest) {
                //get user
                $user = Auth::guard('api')->user();
                if($user) {
                    //authorize user
                    if(CafeMember::where([
                        ['cafe',$cafe->id],
                        ['right','admin'],
                        ['user',$user->id],
                        ['status','confirmed']
                    ])
                    ->first()
                    ) {
                        //get notifications and delete
                        $notifications = Notification::where([
                            ['type','joinRequest'],
                            ['id',$joinRequest->id]
                        ])
                        ->get();
                        foreach($notifications as $notify) {
                            $notify->delete();
                        }
                        //delete join request
                        $joinRequest->delete();
                        return response()->json(true,Response::HTTP_OK);
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
                    "data" => "Not Found!"
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
     * send invite email
     */
    public function inviteUser(Request $request) {
        //get cafe
        $cafe = $request->query('cafe');
        $cafe = $cafe == null && !is_int($cafe) ? null : Cafe::find($cafe);
        if($cafe) {
            //get user
            $user = Auth::guard('api')->user();
            if($user) {
                //authorize user
                if(CafeMember::where([
                    ['cafe',$cafe->id],
                    ['right','admin'],
                    ['user',$user->id],
                    ['status','confirmed']
                ])
                ->first()
                ) {
                    //set content
                    $content = [
                        "email" => $request->email,
                        "url" => "http://www.mobileDish.com/cafe/invite?email=".$request->email
                    ];
                    $mail = Mail::send(new InviteMail($content));
                    if($mail) {
                        return response()->json([
                            "error" => "Mail sent"
                        ],Response::HTTP_OK);
                    }
                    else{
                        return response()->json([
                            "error" => "Unable to send mail"
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
