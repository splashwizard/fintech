<?php

namespace App\Http\Controllers\API;

use App\BusinessLocation;
use App\Http\Controllers\Controller;
use App\NewTransactions;
use App\NewTransactionTransfer;
use App\NewTransactionWithdraw;
use Illuminate\Http\Request;
use App\Utils\TransactionUtil;



class NewTransactionAPIController extends Controller
{
    protected $transactionUtil;
    public function __construct(TransactionUtil $transactionUtil)
    {
        $this->transactionUtil = $transactionUtil;
    }
    public function store(Request $request) {
        try {
            $input = $request->only(['bank_id', 'deposit_method', 'amount', 'reference_number', 'product_id', 'bonus_id']);
            $business_id = $request->get('business_id');
            $input['client_id'] = $request->post('user_id');
            if ($request->hasFile('image')){
                $input['receipt_url'] = time().'.'.$request->image->getClientOriginalName();
                $request->image->move(public_path('/uploads/receipt_images'), $input['receipt_url']);
            }
            $default_location = null;
            if(BusinessLocation::where('business_id', $business_id)->count() == 1){
                $default_location = BusinessLocation::where('business_id', $business_id)->first()->id;
            }
            $input['invoice_no'] = $this->transactionUtil->getNewInvoiceNumber($business_id, $default_location);
            NewTransactions::create($input);
            $output = ['success' => true, 'msg' => 'Created Successfully'];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false, 'msg' => __("messages.something_fwent_wrong")
            ];
        }
        return $output;
    }

    public function postWithdraw(Request $request) {
        try {
            $input = $request->only(['bank_id', 'amount', 'remark', 'product_id']);
            $input['client_id'] = $request->post('user_id');
            $business_id = $request->get('business_id');
            $default_location = null;
            if(BusinessLocation::where('business_id', $business_id)->count() == 1){
                $default_location = BusinessLocation::where('business_id', $business_id)->first()->id;
            }
            $input['invoice_no'] = $this->transactionUtil->getNewWithdrawNumber($business_id, $default_location);
            NewTransactionWithdraw::create($input);
            $output = ['success' => true, 'msg' => 'Withdrawed Successfully'];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false, 'msg' => __("messages.something_fwent_wrong")
            ];
        }
        return $output;
    }

    public function postTransfer(Request $request) {
        try {
            $input = $request->only(['business_id', 'from_product_id', 'to_product_id', 'amount']);
//            $input['client_id'] = $request->post('user_id');
            NewTransactionTransfer::create($input);
            $output = ['success' => true, 'msg' => 'Transfered Successfully'];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false, 'msg' => __("messages.something_fwent_wrong")
            ];
        }
        return $output;
    }
}
