<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ProgressStepResource;
use App\Models\ProgressStep;
use Exception;
use Illuminate\Http\Request;

class StepsController extends BaseController
{
    public function getSteps(Request $request){
        try {
            $plan = $request->input('plan');
            $plan = ($plan) ? $plan : 'scale';
            $progress_steps = ProgressStep::where('plan', $plan)->with('tasks')->orderBy('orders')->paginate(25);

            $data = [
                'progressSteps' => ProgressStepResource::collection($progress_steps),
                'pagination' => [
                    'current_page' => $progress_steps->currentPage(),
                    'per_page' => $progress_steps->perPage(),
                    'total' => $progress_steps->total(),
                    'prev_page_url' => $progress_steps->previousPageUrl(),
                    'next_page_url' => $progress_steps->nextPageUrl(),
                    'first_page_url' => $progress_steps->url(1),
                    'last_page_url' => $progress_steps->url($progress_steps->lastPage()),
                ],
            ];

            return $this->sendResponse($data, 'Progress steps fetched successfully');
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getSingleStep($id){
        try {
            $step = ProgressStep::where('id', $id)->with('tasks')->first();
            return $step ? 
                $this->sendResponse(new ProgressStepResource($step), 'Progress step fetched successfully') : 
                $this->sendError('No Step Found', [], 404);
                
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

}