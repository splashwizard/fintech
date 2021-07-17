<?php

namespace App\Http\Controllers;

use App\Account;
use App\CashRegister;
use App\GameId;
use App\TransactionPayment;
use App\User;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use DB;
use Excel;
use Modules\Essentials\Entities\EssentialsRequest;

class PosLedgerController extends Controller
{
    protected $transactionUtil;
    /**
     * Constructor
     *
     * @param Util $commonUtil
     * @return void
     */
    public function __construct(
        TransactionUtil $transactionUtil
    ) {
        $this->transactionUtil = $transactionUtil;
        $this->transactionTypes = [
            'sell' => __('sale.sale'),
            'purchase' => __('lang_v1.purchase'),
            'sell_return' => __('lang_v1.sell_return'),
            'purchase_return' =>  __('lang_v1.purchase_return'),
            'opening_balance' => __('lang_v1.opening_balance'),
            'payment' => __('lang_v1.payment')
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->ajax()) {
            if (!auth()->user()->can('supplier.view') && !auth()->user()->can('customer.view')) {
                abort(403, 'Unauthorized action.');
            }

            $business_id = request()->session()->get('user.business_id');
            $contact_id = request()->input('contact_id');
            $selected_bank = request()->input('selected_bank');
            $bank_list = Account::where('is_service', 0)->where('business_id', $business_id)->where('is_safe', 0)->where('name', '!=', 'Bonus Account')->get();
            if(empty($selected_bank))
                $selected_bank = $bank_list[0]->id;


            $start =  date('Y-m-d H:i:s', strtotime(request()->start_date));
            $end =  date('Y-m-d H:i:s', strtotime(request()->end_date.' +1 day') - 1);
//            print_r($start);
//            print_r($end);exit;

            $ledger = [];

            $query2 = TransactionPayment::join(
                'transactions as t',
                'transaction_payments.transaction_id',
                '=',
                't.id'
            )
                ->join('contacts as c', 'c.id', 'transaction_payments.payment_for')
                ->join('accounts as a', 'a.id', 'transaction_payments.account_id')
                ->leftJoin('business_locations as bl', 't.location_id', '=', 'bl.id')
//            ->join('contacts as c', 'c.id', 't.contact_id')
                ->where('t.business_id', $business_id)
                ->where('t.status', '!=', 'draft');

            if($selected_bank == 'GTransfer')
                $query2->where('t.sub_type', 'game_credit_transfer');
            else if($selected_bank == 'Deduction')
                $query2->whereIn('t.sub_type', ['game_credit_deduct', 'game_credit_addict']);
            $query2->where('transaction_date', '>=', $start)
                ->where('transaction_date', '<=', $end);
            $query2->orderBy('transaction_date', 'DESC');
            $query2->orderBy('invoice_no', 'DESC');
            $query2->orderBy('transaction_payments.id', 'ASC');

            $payments = $query2->select('transaction_payments.*', 't.id as transaction_id', 't.sub_type as sub_type', 't.document as document', 't.bank_in_time as bank_in_time', 't.transaction_date as transaction_date', 'bl.name as location_name', 't.type as transaction_type', 't.ref_no', 't.invoice_no'
                , 'transaction_payments.id as tp_id', 'transaction_payments.game_id as tp_game_id', 'c.id as contact_primary_key', 'c.contact_id as contact_id', 'c.is_default as is_default', 'a.id as account_id', 'a.name as account_name', 't.created_by as created_by')->get();

//        $total_deposit = $query2->where('t.type', 'sell')->where('transaction_payments.method', '!=', 'service_transfer')->where('transaction_payments.method','!=', 'bonus')->sum('transaction_payments.amount');
            $paymentTypes = $this->transactionUtil->payment_types();
            if($selected_bank == 'GTransfer' || $selected_bank == 'Deduction') {
                foreach ($payments as $payment) {
                    $ref_no = in_array($payment->transaction_type, ['sell', 'sell_return']) ? $payment->invoice_no : $payment->ref_no;
                    $user = User::find($payment->created_by);

                    $game_data = GameId::where('contact_id', $payment->contact_primary_key)->where('service_id', $payment->account_id)->get();
                    $game_id = null;
                    if(count($game_data) >= 1){
                        $game_id = $game_data[0]->cur_game_id;
                    }
                    $item = [
                        'date' => date("Y-m-d H:i:s", strtotime($payment->transaction_date) ),
                        'ref_no' => $payment->payment_ref_no,
                        'type' => $this->transactionTypes['payment'],
                        'location' => $payment->location_name,
                        'contact_id' => $payment->contact_id,
                        'payment_method' => !empty($paymentTypes[$payment->method]) ? $paymentTypes[$payment->method] : '',
                        'others' => '<small>' . $ref_no . '</small>',
                        'bank_in_time' => $payment->bank_in_time,
                        'user' => $user['first_name'] . ' ' . $user['last_name'],
                        'service_name' => $payment->account_name,
                        'game_id' => $payment->tp_game_id,
                        'transaction_id' => $payment->transaction_id,
                        'is_default' => 0,
                        'is_edit_request' => 0
                    ];
                    if($payment->card_type == 'debit')
                        $item['service_debit'] = $payment->amount;
                    else if($payment->card_type == 'credit')
                        $item['service_credit'] = $payment->amount;
                    $document = $payment->document;
                    if($document && isFileImage($document)) {
                        $document_path = 'uploads/service_documents/' . $document;
                        $item['document_path'] = $document_path;
                    }
                    $ledger[] = $item;
                }
            } else {
                $ledger_by_payment = [];
                $payment_item = [];
                foreach ($payments as $payment) {
                    if(empty($payment_item) || $payment_item['transaction_id'] != $payment->transaction_id ||
                        (!empty($payment_item['service_debit']) && $payment->card_type == 'debit' && $payment->method == 'service_transfer')){
                        if(!empty($payment_item)){
                            $ledger_by_payment[] = $payment_item;
                        }
                        if(!empty($payment_item['service_debit']) && $payment->card_type == 'debit' && $payment->method == 'service_transfer'){
                            $payment_item = [
                                'bank_id' => isset($payment_item['bank_id']) ? $payment_item['bank_id'] : -1,
                                'transaction_id' => $payment_item['transaction_id'],
                                'date' => $payment_item['date'],
                                'contact_id' => $payment->contact_id,
                                'account_name' => $payment_item['account_name'],
                                'is_first_service' => 0
                            ];
                        }
                        else {
                            $ref_no = in_array($payment->transaction_type, ['sell', 'sell_return']) ?  $payment->invoice_no :  $payment->ref_no;
                            $user = User::find($payment->created_by);
                            $payment_item = [
                                'date' => $payment->transaction_date,
                                'ref_no' => $payment->payment_ref_no,
                                'type' => $this->transactionTypes['payment'],
                                'location' => $payment->location_name,
                                'contact_id' => $payment->contact_id,
                                'payment_method' => !empty($paymentTypes[$payment->method]) ? $paymentTypes[$payment->method] : '',
                                'others' => '<small>' . $ref_no . '</small>',
                                'bank_in_time' => $payment->bank_in_time,
                                'user' => $user['first_name'].' '.$user['last_name'],
                                'is_default' => $payment->is_default,
                                'account_name' => $payment->account_name,
                                'transaction_id' => $payment->transaction_id
                            ];
                            if($payment->transaction_type == 'sell_return') {
                                $document = $payment->document;
                                if($document && isFileImage($document)) {
                                    if ($payment->sub_type == null)
                                        $document_path = 'uploads/account_documents/' . $document;
                                    else
                                        $document_path = 'uploads/service_documents/' . $document;
                                    $payment_item['document_path'] = $document_path;
                                }
                            }
                        }
                    }
                    if($payment->card_type == 'credit'){
                        if($payment->method == 'bank_transfer')
                            $payment_item['credit'] = $payment->amount;
                        else if($payment->method == 'free_credit')
                            $payment_item['free_credit'] = $payment->amount;
                        else if($payment->method == 'basic_bonus')
                            $payment_item['basic_bonus'] = $payment->amount;
                        else if($payment->method == 'service_transfer')
                            $payment_item['service_credit'] = $payment->amount;
                    }
                    else if ($payment->card_type == 'debit'){
                        if($payment->method != 'service_transfer')
                            $payment_item['debit'] = $payment->amount;
                        else{
                            $payment_item['service_debit'] = $payment->amount;
                            $payment_item['transaction_payment_id'] = $payment->tp_id;
                            if(!isset($payment_item['is_first_service'])){
                                $payment_item['is_first_service'] = 1;
                            }
                        }
                    }
                    if(($payment->transaction_type == 'sell' || $payment->transaction_type == 'sell_return' ) && $payment->method == 'service_transfer'){
                        $payment_item['service_name'] = $payment->account_name;
                        $payment_item['game_id'] = $payment->tp_game_id;
                    }
                    if($payment->method == 'bank_transfer') {
                        $payment_item['bank_id'] = $payment->account_id;
                    }
                }
                $ledger_by_payment[] = $payment_item;
//            print_r($ledger_by_payment);exit;


                foreach ($ledger_by_payment as $item){
                    if( ( $selected_bank == 'free_credit' && isset($item['free_credit'])) || (isset($item['bank_id']) && $item['bank_id'] == $selected_bank)){
//                    $item['transaction_id'] = $transaction_id;
                        if(EssentialsRequest::where('transaction_id', $item['transaction_id'])->where('status', 'pending')->count() > 0)
                            $item['is_edit_request'] = 1;
                        else
                            $item['is_edit_request'] = 0;
                        $ledger[] = $item;
                    }
                }
//            print_r($ledger);exit;
            }
            $user_id = request()->session()->get('user.id');
            $data = CashRegister::join('users as u', 'u.id', 'user_id')->join('business as b', 'b.id', 'u.business_id')->where('u.business_id', $business_id)->where('closed_at', '>=', $start)->where('closed_at', '<=', $end )->get();
            foreach($data as $row){
                $ledger[] = ['date' => $row->closed_at,
                    'user' => $row['first_name'].' '.$row['last_name']];
            }
            //Sort by date
            if (!empty($ledger)) {
                usort($ledger, function ($a, $b) {
                    $t1 = strtotime($a['date']);
                    $t2 = strtotime($b['date']);
                    return $t2 - $t1;
                });
            }

            return view('pos_ledger.ledger')
                ->with(compact('ledger', 'bank_list', 'selected_bank'));
        }

        return view('pos_ledger.index');
    }
}
