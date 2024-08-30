<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ContractStoreRequest;
use App\Models\Contract;
use App\Models\Location;
use App\Models\Member;
use App\Models\MemberContract;
use App\Models\Program;
use App\Models\StripePlan;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ContractController extends BaseController
{
    public function addContract(ContractStoreRequest $request, $programId)
    {
        $program = Program::with(['location'])->where('id', $programId)->firstOrFail();
        $location = $program->location;
        try {
            DB::beginTransaction();
            $contract = Contract::create([
                'vendor_id' => $location->vendor_id,
                'content' => $request->content,
                'title' => $request->title,
                'description' => $request->description,
            ]);
            // Get the Stripe plan you want to attach the contract to
            $stripePlan = StripePlan::find($request->plan_id);
            // Attach the contract to the Stripe plan
            $stripePlan->contract_id = $contract->id;
            $stripePlan->save();
            DB::commit();
            return $this->sendResponse($contract, 'Contract created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('===== ContractController - addContracct() - error =====');
            Log::info($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

    public function getContracts($programId)
    {
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

    public function getContractVariables($contractId)
    {
        try {
            // return $this->sendResponse($data, 'Contract');
            $member = Auth::user()->member;
            $memberDetails = Member::with('programs')->find($member->id);
            $plans = Contract::with('stripePlans')->find($contractId);
            $data = collect([
                "member_name" => $memberDetails->name,
                "member_email" => $memberDetails->email,
                "member_phone" => $memberDetails->phone,
                "plan_title" => $plans->title,
                "plan_description" => $plans->description,
                "member_details" => $memberDetails,

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
            $memberContract = MemberContract::create([
                'member_id' => $member->id,
                'contract_id' => $request->contractId,
                'stripe_plan_id' => $request->stripePlanId,
                'content' => $request->content,
                'signed' => $request->signed,
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
            return $this->sendResponse(["data" => $memberContract, "pdf_url" => $pdfUrl], 'Contract created successfully.');
        
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('===== ContractController - fillContract() - error =====');
            Log::info($e->getMessage());
            return $this->sendError($e->getMessage(), [], 500);
        }
    }
}
