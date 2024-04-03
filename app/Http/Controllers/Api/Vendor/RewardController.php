<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Models\Reward;
use App\Http\Resources\Vendor\RewardResource;
use Illuminate\Http\Request;


class RewardController extends BaseController
{
    public function index(){
        try{
            $rewards = Reward::with(['achievement']);
            if(isset(request()->type)) {
                if(request()->type == 0) {
                    $rewards = $rewards->whereNotNull('deleted_at')->withTrashed();
                }
            }
            $rewards = $rewards->paginate(25);
            $data = [
                'rewards' => RewardResource::collection($rewards),
                'pagination' => [
                    'current_page' => $rewards->currentPage(),
                    'per_page' => $rewards->perPage(),
                    'total' => $rewards->total(),
                    'prev_page_url' => $rewards->previousPageUrl(),
                    'next_page_url' => $rewards->nextPageUrl(),
                    'first_page_url' => $rewards->url(1),
                    'last_page_url' => $rewards->url($rewards->lastPage()),
                ],
            ];
            return $this->sendResponse($data, 'Rewards fetched successfully');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function create(Request $request){
        try{
            $reward = Reward::create($request->all());
            return $this->sendResponse(new RewardResource($reward), 'Reward created successfully');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function show($id){
        try{
            $reward = Reward::with(['achievement'])->find($id);
            if(!$reward){
                return $this->sendError('Reward not found', [], 400);
            }
            return $this->sendResponse(new RewardResource($reward), 'Reward fetched successfully');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function update(Request $request, Reward $reward){
        try{
            $reward->update([
                'name' => $request->name,
                'description' => $request->description,
                'image' => $request->image,
                'limit_per_member' => $request->limit_per_member
            ]);
            return $this->sendResponse(new RewardResource($reward), 'Reward updated successfully');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function delete($id){
        try{
            $reward = Reward::find($id);
            if(!$reward){
                return $this->sendError('Reward not found', [], 400);
            }
            $reward->delete();
            return $this->sendResponse('Success', 'Reward deleted successfully');
        }catch(Exception $error){
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

}
