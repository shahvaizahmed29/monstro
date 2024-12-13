<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ContractStoreRequest;
use App\Http\Resources\Vendor\SignedContractsResource;
use App\Models\Contract;
use App\Models\Location;
use App\Models\Member;
use App\Models\MemberContract;
use App\Models\Plan;
use App\Models\Program;
use App\Models\StripePlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContractController extends BaseController
{

    public function addContract(Request $request)
    {
        $location = request()->location;
        $location = Location::find($location->id);
        try {
            DB::beginTransaction();
            $contract = Contract::create([
                'vendor_id' => $location->vendor_id,
                'content' => $request->content,
                'title' => $request->title,
                'editable' => $request->editable,
                'isDraft' => $request->isDraft,
                'location_id' => $location->id,
            ]);
            if($request->planId){
                // Get the Stripe plan you want to attach the contract to
                $stripePlan = StripePlan::find($request->planId);
                // Attach the contract to the Stripe plan
                $stripePlan->contract_id = $contract->id;
                $stripePlan->save();
            }
            DB::commit();
            return $this->sendResponse($contract, 'Contract created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('===== ContractController - addContracct() - error =====');
            Log::info($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function getContractsByProgram($programId) {
        try {

            $program = Program::with(['location'])->where('id', $programId)->firstOrFail();
            $location = $program->location;
            if (!$location) {
                return $this->sendError("Location doesnot exist", [], 400);
            }
            $plans = Contract::with('stripePlans')->where('vendor_id', $location->vendor_id)->get();

            return $this->sendResponse($plans, 'Contract List');

        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getContracts()
    {
        try {
            $location = request()->location;
            $location = Location::find($location->id);
            if (!$location) {
                return $this->sendError("Location doesnot exist", [], 400);
            }
            $contracts = Contract::where('location_id', $location->id)->get();

            return $this->sendResponse($contracts, 'Contract List');

        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getSignedContracts() {
        $location = request()->location;
        $location = Location::find($location->id);
        if (!$location) {
            return $this->sendError("Location doesnot exist", [], 400);
        }
        try {
            $contracts = MemberContract::with(['member', 'stripePlan.program', 'contract'])->where(['location_id' => $location->id])->get();
            return $this->sendResponse(SignedContractsResource::collection($contracts), 'Contract List');
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }

    }

    public function getContractById($contractId)
    {
        try {
            $location = request()->location;
            $location = Location::find($location->id);
            if (!$location) {
                return $this->sendError("Location doesnot exist", [], 400);
            }
            $plans = Contract::with('stripePlans')->find($contractId);

            return $this->sendResponse($plans, 'Contract');

        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function deleteContract($contractId)
    {
        try {
            $location = request()->location;
            $location = Location::find($location->id);
            if (!$location) {
                return $this->sendError("Location doesnot exist", [], 400);
            }
            $contract = Contract::with('stripePlans')->find($contractId);
            foreach ($contract->stripePlans as $stripePlan) {
                $stripePlan->contract_id = null;
                $stripePlan->save();
            }
            $contract->delete();
            

            return $this->sendResponse($contract, 'Contract Deleted');

        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function getContractVariables($contractId)
    {
        try {
            // return $this->sendResponse($data, 'Contract');
            $member = Auth::user()->member;
            $memberDetails = Member::find($member->id);
            Log::info(json_encode($memberDetails));
            $plan = StripePlan::with('pricing')->where(['contract_id' => $contractId])->first();
            $program = Program::find($plan->program_id);
            $location = Location::find($program->location_id);
            $data = collect([
                "contact" => [
                    "fullName" => $memberDetails->first_name.' '.$memberDetails->last_name,
                    "firstName" => $memberDetails->first_name,
                    "lastName" => $memberDetails->last_name,
                    "email" => $memberDetails->email,
                    "phone" => $memberDetails->phone
                ],
                "plan" => [
                    "name" => $plan->name,
                    "description" => $plan->description,
                    "price" => $plan->pricing->amount,
                    "period" => $plan->pricing->billing_period,
                    "familyMemberLimit" => $plan->family_member_limit
                ],
                "program" => [
                    "name" => $program->name,
                    "description" => $program->description    
                ],
                "company" => [
                    "name" => $location->name,
                    "address" => $location->address,
                    "city" => $location->city,
                    "state" => $location->state,
                    "zip" => $location->postal_code,
                    "email" => $location->email,
                    "phone" => $location->phone
                ]

            ]);

            return $this->sendResponse($data, 'Contract');

        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function fillContract(Request $request)
    {
        try {
            $member = Auth::user()->member;
            DB::beginTransaction();
            $contract = Contract::find($request->contractId);
            if(!$contract){
                return $this->sendError("Contract doesnot exist", [], 400);
            }
            $memberContract = MemberContract::create([
                'member_id' => $member->id,
                'contract_id' => $request->contractId,
                'stripe_plan_id' => $request->stripePlanId,
                'content' => $request->content,
                'signed' => $request->signed,
                'location_id' => $contract->location_id
            ]);
            $contract->update([
                'editable' => false
            ]);
            DB::commit();
            // Generate the PDF from the HTML content
            $pdf = Pdf::loadHTML(mb_convert_encoding($request->content, 'UTF-8', 'UTF-8'));

            // Save the PDF to a public path directly in the 'public' directory
            $fileName = uniqid() . '.pdf';
            $pdfPath = public_path('contracts/' . $fileName);
        
            // Ensure the 'contracts' directory exists
            if (!file_exists(public_path('contracts'))) {
                mkdir(public_path('contracts'), 0755, true);
            }
        
            // Save the PDF to the specified path
            file_put_contents($pdfPath, $pdf->output());
        
            // Generate the URL to the file
            $pdfUrl = url('contracts/' . $fileName);
        
            // Send the response with the PDF URL
            return $this->sendResponse(["data" => $memberContract, "pdfUrl" => $pdfUrl], 'Contract created successfully.');
        
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('===== ContractController - fillContract() - error =====');
            Log::info($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function updateContractById(Request $request, $contractId) {
        try {
            $location = request()->location;
            $location = Location::find($location->id);
            if (!$location) {
                return $this->sendError("Location doesnot exist", [], 400);
            }
            $contract = Contract::where(['editable' => true])->find($contractId);
            if (!$contract) {
                return $this->sendError("contract doesnot exist", [], 400);
            }
            $contract->update([
                'content' => $request->content,
                'title' => $request->title,
                'description' => $request->description,
                'isDraft' => $request->isDraft,
            ]);
            return $this->sendResponse($contract, 'Contract');

        } catch (Exception $error) {
            Log::info(json_encode($error));
            return $this->sendError($error->getMessage(), [], 500);
        }
    }

    public function createPdf($contractId) {
        try {
            $contract = MemberContract::find($contractId);
            $pdf = Pdf::loadHTML(mb_convert_encoding($contract->content, 'UTF-8', 'UTF-8'));

            // Save the PDF to a public path directly in the 'public' directory
            $fileName = uniqid() . '.pdf';
            $pdfPath = public_path('contracts/' . $fileName);
        
            // Ensure the 'contracts' directory exists
            if (!file_exists(public_path('contracts'))) {
                mkdir(public_path('contracts'), 0755, true);
            }        
            // Save the PDF to the specified path
            file_put_contents($pdfPath, $pdf->output());
        
            // Generate the URL to the file
            $pdfUrl = url('contracts/' . $fileName);
            return $this->sendResponse(["pdfUrl" => $pdfUrl], 'Contract List');
        } catch (Exception $error) {
            return $this->sendError($error->getMessage(), [], 500);
        }
    }
}
