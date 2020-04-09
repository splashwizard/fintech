<?php

namespace App\Http\Controllers;

use App\Account;
use App\GameId;
use App\TransactionPayment;
use App\User;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use DB;
use Excel;

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


            $start = request()->start_date;
            $end =  request()->end_date;


            $ledger = [];

            $query2 = TransactionPayment::join(
                'transactions as t',
                'transaction_payments.transaction_id',
                '=',
                't.id'
            )
                ->join('accounts as a', 'a.id', 'transaction_payments.account_id')
                ->leftJoin('business_locations as bl', 't.location_id', '=', 'bl.id')
                ->join('contacts as c', 'c.id', 't.contact_id')
                // ->where('t.contact_id', $contact_id)
                ->where('t.business_id', $business_id)
                ->where('t.status', '!=', 'draft');

            if($selected_bank == 'GTransfer')
                $query2->where('t.sub_type', 'game_credit_transfer');
            else if($selected_bank == 'Deduction')
                $query2->where('t.sub_type', 'game_credit_deduct');
            $query2->whereDate('paid_on', '>=', $start)
                ->whereDate('paid_on', '<=', $end);

            $payments = $query2->select('transaction_payments.*', 't.id as transaction_id', 't.bank_in_time as bank_in_time', 'bl.name as location_name', 't.type as transaction_type', 't.ref_no', 't.invoice_no'
                , 'c.id as contact_primary_key', 'c.contact_id as contact_id', 'a.id as account_id', 'a.name as account_name', 't.created_by as created_by')->get();
//        $total_deposit = $query2->where('t.type', 'sell')->where('transaction_payments.method', '!=', 'service_transfer')->where('transaction_payments.method','!=', 'bonus')->sum('transaction_payments.amount');
            $paymentTypes = $this->transactionUtil->payment_types();
            if($selected_bank == 'GTransfer' || $selected_bank == 'Deduction') {
                foreach ($payments as $payment) {
                    $ref_no = in_array($payment->transaction_type, ['sell', 'sell_return']) ? $payment->invoice_no : $payment->ref_no;
                    $user = User::find($payment->created_by);


                    $game_data = GameId::where('contact_id', $payment->contact_primary_key)->where('service_id', $payment->account_id)->get();
                    $game_id = null;
                    if(count($game_data) >= 1){
                        $game_id = $game_data[0]->game_id;
                    }
                    $ledger[] = [
                        'date' => $payment->paid_on,
                        'ref_no' => $payment->payment_ref_no,
                        'type' => $this->transactionTypes['payment'],
                        'location' => $payment->location_name,
                        'contact_id' => $payment->contact_id,
                        'payment_method' => !empty($paymentTypes[$payment->method]) ? $paymentTypes[$payment->method] : '',
                        'debit' => 0,
                        'credit' => 0,
                        'free_credit' => 0,
                        'service_debit' => $payment->card_type == 'debit' ? $payment->amount : 0,
                        'service_credit' => $payment->card_type == 'credit' ? $payment->amount : 0,
                        'others' => '<small>' . $ref_no . '</small>',
                        'bank_in_time' => $payment->bank_in_time,
                        'user' => $user['first_name'] . ' ' . $user['last_name'],
                        'service_name' => $payment->account_name,
                        'game_id' => $game_id
                    ];
                }
            } else {
                $ledger_by_payment = [];
                foreach ($payments as $payment) {
                    if(empty($ledger_by_payment[$payment->transaction_id])){
                        $ref_no = in_array($payment->transaction_type, ['sell', 'sell_return']) ?  $payment->invoice_no :  $payment->ref_no;
                        $user = User::find($payment->created_by);
                        $ledger_by_payment[$payment->transaction_id] = [
                            'date' => $payment->paid_on,
                            'ref_no' => $payment->payment_ref_no,
                            'type' => $this->transactionTypes['payment'],
                            'location' => $payment->location_name,
                            'contact_id' => $payment->contact_id,
                            'payment_method' => !empty($paymentTypes[$payment->method]) ? $paymentTypes[$payment->method] : '',
                            'debit' => ($payment->card_type == 'debit' && $payment->method != 'service_transfer') ? $payment->amount : 0,
                            'credit' => ($payment->card_type == 'credit' && $payment->method == 'bank_transfer') ? $payment->amount : 0,
                            'free_credit' => ($payment->card_type == 'credit' && $payment->method == 'free_credit') ? $payment->amount : 0 ,
                            'service_debit' => ($payment->card_type == 'debit' && $payment->method == 'service_transfer') ? $payment->amount : 0,
                            'service_credit' => ($payment->card_type == 'credit' && $payment->method == 'service_transfer' ) ? $payment->amount : 0,
                            'others' => '<small>' . $ref_no . '</small>',
                            'bank_in_time' => $payment->bank_in_time,
                            'user' => $user['first_name'].' '.$user['last_name']
                        ];
                    } else {
                        $ledger_by_payment[$payment->transaction_id]['debit'] += ($payment->card_type == 'debit' && $payment->method != 'service_transfer') ? $payment->amount : 0;
                        $ledger_by_payment[$payment->transaction_id]['credit'] += ($payment->card_type == 'credit' && $payment->method == 'bank_transfer') ? $payment->amount : 0;
                        $ledger_by_payment[$payment->transaction_id]['free_credit'] += ($payment->card_type == 'credit' && $payment->method == 'free_credit') ? $payment->amount : 0;
                        $ledger_by_payment[$payment->transaction_id]['service_debit'] += ($payment->card_type == 'debit' && $payment->method == 'service_transfer') ? $payment->amount : 0;
                        $ledger_by_payment[$payment->transaction_id]['service_credit'] += ($payment->card_type == 'credit' && $payment->method == 'service_transfer' ) ? $payment->amount : 0;
                    }
                    if(($payment->transaction_type == 'sell' || $payment->transaction_type == 'sell_return' ) && $payment->method == 'service_transfer'){
                        $ledger_by_payment[$payment->transaction_id]['service_name'] = $payment->account_name;
                        $game_data = GameId::where('contact_id', $payment->contact_primary_key)->where('service_id', $payment->account_id)->get();
                        if(count($game_data) >= 1){
                            $game_id = $game_data[0]->game_id;
                            $ledger_by_payment[$payment->transaction_id]['game_id'] = $game_id;
                        }
                    }
                    if($payment->method == 'bank_transfer') {
                        $ledger_by_payment[$payment->transaction_id]['bank_id'] = $payment->account_id;
                    }
                }
                foreach ($ledger_by_payment as $item){
                    if(isset($item['bank_id']) && $item['bank_id'] == $selected_bank)
                        $ledger[] = $item;
                }
            }
//        print_r($ledger_by_payment);exit;
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
