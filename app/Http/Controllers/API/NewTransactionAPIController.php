<?php

namespace App\Http\Controllers\API;

use App\BusinessLocation;
use App\Http\Controllers\Controller;
use App\NewTransactions;
use App\NewTransactionTransfer;
use App\NewTransactionWithdraw;
use App\Product;
use App\Utils\GameUtil;
use Illuminate\Http\Request;
use App\Utils\TransactionUtil;



class NewTransactionAPIController extends Controller
{
    protected $transactionUtil;
    protected $gameUtil;
    public function __construct(TransactionUtil $transactionUtil, GameUtil $gameUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->gameUtil = $gameUtil;
    }
    public function store(Request $request) {
        try {
            $input = $request->only(['bank_id', 'deposit_method', 'amount', 'reference_number', 'product_id', 'bonus_id']);
            $business_id = $request->get('business_id');
            $input['client_id'] = $request->post('user_id');
            if ($request->hasFile('image')){
                $input['receipt_url'] = $this->transactionUtil->uploadFile($request, 'image', 'receipt_images');
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
            $input = $request->only(['business_id', 'from_product_id', 'to_product_id', 'amount', 'username']);
            $input['client_id'] = $request->post('user_id');
            $business_id = $request->get('business_id');
            $default_location = null;
            if(BusinessLocation::where('business_id', $business_id)->count() == 1){
                $default_location = BusinessLocation::where('business_id', $business_id)->first()->id;
            }

            $from_game = Product::find($input['from_product_id'])->name;
            $to_game = Product::find($input['to_product_id'])->name;
            $resp = $this->gameUtil->withdraw($from_game, $input['username'], $input['amount']);
            if($resp->code != 0) { // Player name exist
                switch ($resp->code) {
                    case 33:
                        $msg = 'Kiosk Admin is disabled, contact to company';
                        break;
                    case 34:
                        $msg = 'Access is denied, contact to company';
                        break;
                    case 36:
                        $msg = 'Can’t withdraw, because player is playing game now.';
                        break;
                    case 37:
                        $msg = 'The possible values of amount can be only numbers';
                        break;
                    case 39:
                        $msg = 'Cannot make withdraw, Amount is not bigger than current balance.';
                        break;
                    case 41:
                        $msg = 'Player does not exists';
                        break;
                    case 42:
                        $msg = 'Player is frozen';
                        break;
                    case 72:
                        $msg = "Could not load data. Error: 'Service error accessing API'";
                        break;
                    case 73:
                        $msg = "Could not load data from database. Error: 'Database error occured, please contact support'. Please, try again later";
                        break;
                    default:
                        $msg = __("messages.something_went_wrong");
                }
                $output = ['success' => false, 'msg' => $msg];
                return $output;
            }
            $resp = $this->gameUtil->deposit($to_game, $input['username'], $input['amount']);
            if($resp->code != 0){
                switch ($resp->code){
                    case 33:
                        $msg = 'Kiosk Admin is disabled, contact to company';
                        break;
                    case 34:
                        $msg = 'Access is denied, contact to company';
                        break;
                    case 35:
                        $msg = 'Kiosk admin doesn’t have enough balance to deposit, please deposit first';
                        break;
                    case 37:
                        $msg = 'The possible values of amount can be only numbers';
                        break;
                    case 38:
                        $msg = 'Cannot make deposit, Amount is less than minimum deposit amount for this player';
                        break;
                    case 41:
                        $msg = 'Player does not exists';
                        break;
                    case 42:
                        $msg = 'Player is frozen';
                        break;
                    case 72:
                        $msg = "Could not load data. Error: 'Service error accessing API'";
                        break;
                    case 73:
                        $msg = "Could not load data from database. Error: 'Database error occured, please contact support'. Please, try again later";
                        break;
                    default:
                        $msg = __("messages.something_went_wrong");
                }
                $output = ['success' => false, 'msg' => $msg];
                return $output;
            }
            $input['invoice_no'] = $this->transactionUtil->getNewTransferNumber($business_id, $default_location);
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
