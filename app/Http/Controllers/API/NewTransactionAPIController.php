<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\AccountTransaction;
use App\BusinessLocation;
use App\Http\Controllers\Controller;
use App\NewTransactions;
use App\NewTransactionTransfer;
use App\NewTransactionWithdraw;
use App\Product;
use App\User;
use App\Utils\ContactUtil;
use App\Utils\GameUtil;
use Illuminate\Http\Request;
use App\Utils\TransactionUtil;
use jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;


class NewTransactionAPIController extends Controller
{
    protected $transactionUtil;
    protected $gameUtil;
    protected $contactUtil;
    public function __construct(TransactionUtil $transactionUtil, GameUtil $gameUtil, ContactUtil $contactUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->gameUtil = $gameUtil;
        $this->contactUtil = $contactUtil;
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
            $from_game = Product::find($input['from_product_id'])->name;
            if($from_game == 'Main Wallet'){
                $main_wallet_balance = $this->contactUtil->getMainWalletBalance($business_id, $input['client_id']);
                if($main_wallet_balance < $input['amount']){
                    $output = ['success' => false, 'msg' => "Main Wallet doesn't have enough balance"];
                    return $output;
                }
            }

            $default_location = null;
            if(BusinessLocation::where('business_id', $business_id)->count() == 1){
                $default_location = BusinessLocation::where('business_id', $business_id)->first()->id;
            }
            $from_kiosk_id = Account::find(Product::find($input['from_product_id'])->account_id)->connected_kiosk_id;
            $to_kiosk_id = Account::find(Product::find($input['to_product_id'])->account_id)->connected_kiosk_id;

            $resp = $this->gameUtil->transfer($input['username'], $from_kiosk_id, $to_kiosk_id, $input['amount']);
            if($resp['success'] == false){
                return $resp;
            }
            $input['invoice_no'] = $this->transactionUtil->getNewTransferNumber($business_id, $default_location);
            NewTransactionTransfer::create($input);
            $this->transfer($input['client_id'], $business_id, Product::find($input['from_product_id'])->account_id, Product::find($input['to_product_id'])->account_id, $input['amount']);
            $output = ['success' => true, 'msg' => 'Transferred Successfully'];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false, 'msg' => __("messages.something_fwent_wrong")
            ];
        }
        return $output;
    }

    private function transfer($contact_id, $business_id, $transfer_from, $transfer_to, $amount, $note = ''){
        $created_by = User::where('username', 'blackblack')->first()->id;
        $business_locations = BusinessLocation::forDropdown($business_id, false, true, true, true);
        $business_locations = $business_locations['locations'];
        $input = [];
        if (count($business_locations) >= 1) {
            foreach ($business_locations as $id => $name) {
                $input['location_id'] = $id;
            }
        }
        $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
        $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;
        $input['contact_id'] = $contact_id;
        $input['ref_no'] = 0;
        $now = new \DateTime('now');
        $input['transaction_date'] = $now->format('Y-m-d H:i:s');
        $input['discount_type'] = 'percentage';
        $input['discount_amount'] = 0;
        $input['final_total'] = $amount;
        $input['additional_notes'] = $note;
        $invoice_total = ['total_before_tax' => $amount, 'tax' => 0];

        $sub_type = 'game_credit_transfer';
        $transaction = $this->transactionUtil->createSellReturnTransaction($business_id, $input, $invoice_total, $created_by, $sub_type);
        ActivityLogger::activity("Created transaction, ticket # ".$transaction->invoice_no);
        $this->transactionUtil->createWithDrawPaymentLine($transaction, $created_by, $transfer_from, 1, 'credit');
        $this->transactionUtil->updateCustomerRewardPoints($contact_id, $amount, 0, 0);
        $credit_data = [
            'amount' => $amount,
            'account_id' => $transfer_from,
            'type' => 'credit',
            'sub_type' => 'withdraw',
            'operation_date' => $now->format('Y-m-d H:i:s'),
            'created_by' => $created_by,
            'transaction_id' => $transaction->id,
            'shift_closed_at' => Account::find($transfer_from)->shift_closed_at
        ];

        AccountTransaction::createAccountTransaction($credit_data);
        $business_locations = BusinessLocation::forDropdown($business_id, false, true, true, true);
        $business_locations = $business_locations['locations'];
        $input = [];
        if (count($business_locations) >= 1) {
            foreach ($business_locations as $id => $name) {
                $input['location_id'] = $id;
            }
        }

        $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
        $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;
        $input['contact_id'] = $contact_id;
        $input['ref_no'] = 0;
        $date = new \DateTime('now');
        $input['transaction_date'] = $date->format('Y-m-d H:i:s');
        $input['discount_type'] = 'percentage';
        $input['discount_amount'] = 0;
        $input['final_total'] = $amount;
        $input['commission_agent'] = null;
        $input['status'] = 'final';
        $input['additional_notes'] = $note;
        $invoice_total = ['total_before_tax' => $amount, 'tax' => 0];
        $is_service = 1;
        $this->transactionUtil->createWithDrawPaymentLine($transaction, $created_by, $transfer_to, $is_service, 'debit');
        $this->transactionUtil->updateCustomerRewardPoints($contact_id, 0, 0, $amount);
        if(!$is_service){
            $account = Account::where('business_id', $business_id)
                ->findOrFail($transfer_to);
            $amount += $account->service_charge;
        }
        $credit_data = [
            'amount' => $amount,
            'account_id' => $transfer_to,
            'type' => 'debit',
            'sub_type' => 'withdraw',
            'created_by' => $created_by,
            'note' => $note,
            'transfer_account_id' => $transfer_from,
            'transfer_transaction_id' => $transaction->id,
            'operation_date' => $now->format('Y-m-d H:i:s'),
            'transaction_id' => $transaction->id,
            'shift_closed_at' => Account::find($transfer_to)->shift_closed_at
        ];

        $credit = AccountTransaction::createAccountTransaction($credit_data);
    }
}
