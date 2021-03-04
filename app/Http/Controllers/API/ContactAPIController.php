<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\Http\Controllers\Controller;
use App\TransactionPayment;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;


class ContactAPIController extends Controller
{
    public function __construct(
        TransactionUtil $transactionUtil
    ) {
        $this->transactionUtil = $transactionUtil;
    }
    public function history(Request $request) {
        try {
            $business_id = $request->get('business_id');
            $contact_id = $request->get('contact_id');
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');

            $ledger = [];

            $query2 = TransactionPayment::join(
                'transactions as t',
                'transaction_payments.transaction_id',
                '=',
                't.id'
            )
                ->leftJoin('business_locations as bl', 't.location_id', '=', 'bl.id')
                ->where('t.contact_id', $contact_id)
                ->where('t.business_id', $business_id)
                ->where('t.status', '!=', 'draft');

            if (!empty($start_date) && !empty($end_date)) {
                $start = $start_date;
                $end =  $end_date;

                $query2->whereDate('paid_on', '>=', $start)
                    ->whereDate('paid_on', '<=', $end);
            }

            $payments = $query2->select('transaction_payments.*', 'bl.name as location_name', 't.type as transaction_type', 't.ref_no', 't.invoice_no', 't.payment_status')->get();
            $paymentTypes = $this->transactionUtil->payment_types();
            foreach ($payments as $payment) {
                $ref_no = in_array($payment->transaction_type, ['sell', 'sell_return']) ?  $payment->invoice_no :  $payment->ref_no;
                $new_item = [
                    'date' => $payment->paid_on,
                    'ref_no' => $payment->payment_ref_no,
                    'debit' => ($payment->card_type == 'debit' && $payment->method != 'service_transfer') ? $payment->amount : '',
                    'credit' => ($payment->card_type == 'credit' && $payment->method == 'bank_transfer') ? $payment->amount : '',
                    'bonus' => ($payment->card_type == 'credit' && ($payment->method == 'basic_bonus' || $payment->method == 'free_credit') ) ? $payment->amount : '',
                    'service_debit' => ($payment->card_type == 'debit' && $payment->method == 'service_transfer') ? $payment->amount : '',
                    'service_credit' => ($payment->card_type == 'credit' && $payment->method == 'service_transfer' ) ? $payment->amount : '',
                    'RefDetail' => !empty($paymentTypes[$payment->method]) ? $paymentTypes[$payment->method] : '',
                    'ItemNumber' => $payment->note . '<small>' . __('account.payment_for') . ': ' . $ref_no . '</small>'
                ];
                if($payment->method =='bank_transfer')
                    $new_item['Ref Detail'] = Account::find($payment->account_id)->name;
                else if ($payment->method =='service_transfer')
                    $new_item['Ref Detail'] = Account::find($payment->account_id)->name;
                $ledger[]= $new_item;
            }
            //Sort by date
            if (!empty($ledger)) {
                usort($ledger, function ($a, $b) {
                    $t1 = strtotime($a['date']);
                    $t2 = strtotime($b['date']);
                    return $t2 - $t1;
                });
            }

            $output = ['success' => true, 'data' => $ledger];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false, 'msg' => __("messages.something_went_wrong")
            ];
        }
        return $output;
    }
}
