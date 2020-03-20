<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountTransaction;

use App\BusinessLocation;
use App\DisplayGroup;
use App\ExpenseCategory;
use App\Transaction;
use App\User;
use App\Utils\ModuleUtil;
    

use App\Utils\TransactionUtil;

use DB;
use Illuminate\Http\Request;

use Yajra\DataTables\Facades\DataTables;

class DailyReportController extends Controller
{
    /**
    * Constructor
    *
    * @param TransactionUtil $transactionUtil
    * @return void
    */
    public function __construct(TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->bank_columns = ['currency' => 'Currency', 'balance' => 'Balance B/F', 'deposit' => 'In', 'withdraw' => 'Out', 'service' => 'Service', 'transfer_in' => 'Total In', 'transfer_out' => 'Total Out', 'kiosk' => 'Kiosk', 'back' => 'Cancel',
                            'in_ticket' => 'In Ticket', 'out_ticket' => 'Out Ticket', 'overall' => 'Overall Total', 'win_loss' => 'Win/Loss', 'expenses' => 'Expenses', 'unclaim' => 'Unclaim'];
        $this->service_columns = ['balance' => 'Balance B/F', 'deposit' => 'In', 'withdraw' => 'Out', 'bonus' => 'Bonus', 'luckydraw' => 'Luckydraw', 'free_credit' => 'Free Credit', 'advance_credit' => 'Advance credit', 'transfer_in' => 'Transfer + ', 'transfer_out' => 'Transfer - '];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('daily_report.index');
    }
    function getTableData(){
        $categories = null;
        $business_id = request()->session()->get('user.business_id');
        $banks = Account::where('business_id', $business_id)->where('is_service', 0)->where('name', '!=', 'Bonus Account')->where('display_group_id', '!=', 0)->orderBy('display_group_id', 'asc')->get();
        $banks_group_obj = [];
        foreach ($banks as $row){
            $banks_group_obj[$row->display_group_id][$row->id] = $row->name;
        }

        $bank_accounts_obj = [];
        foreach ($this->bank_columns as $key=>$bank_column){
            $bank_accounts_obj[$key][0] = $bank_column;
        }
        //currency
        $bank_accounts_sql = Account::leftjoin('currencies AS c', 'c.id', 'accounts.currency_id')->where('is_service', 0)
            ->where('accounts.name', '!=', 'Bonus Account')
            ->where('accounts.business_id', $business_id)
            ->select(['c.code as code', 'accounts.id as account_id']);
        $bank_account_currencies = $bank_accounts_sql->get();
        foreach ($bank_account_currencies as $bank_account) {
            $bank_accounts_obj['currency'][$bank_account['account_id']] = $bank_account['code'];
        }
//        print_r($bank_accounts_obj);exit;

        $start = request()->start_date;
        $end =  request()->end_date;
        // balance, deposit, withdraw
        $bank_accounts_sql = Account::leftjoin('account_transactions as AT', function ($join) {
            $join->on('AT.account_id', '=', 'accounts.id');
            $join->whereNull('AT.deleted_at');
        })
            ->where('is_service', 0)
            ->where('name', '!=', 'Bonus Account')
            ->where('business_id', $business_id)
            ->select(['name', 'account_number', 'accounts.note', 'accounts.id as account_id',
                'is_closed', DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")
                , DB::raw("SUM( IF( AT.type='credit' AND (AT.sub_type IS NULL OR AT.`sub_type` != 'fund_transfer'), AT.amount, 0) ) as total_deposit")
                , DB::raw("SUM( IF( AT.type='debit' AND (AT.sub_type IS NULL OR AT.`sub_type` != 'fund_transfer'), AT.amount, 0) ) as total_withdraw")
                , DB::raw("SUM( IF(AT.type='credit' AND AT.sub_type='fund_transfer', amount, 0) ) as transfer_in")
                , DB::raw("SUM( IF(AT.type='debit' AND AT.sub_type='fund_transfer', amount, 0) ) as transfer_out")])
            ->groupBy('accounts.id');
        $bank_accounts_sql->where(function ($q) {
            $q->where('account_type', '!=', 'capital');
            $q->orWhereNull('account_type');
        });
        $bank_account_balances = $bank_accounts_sql->get();
        foreach ($bank_account_balances as $bank_account) {
            $bank_accounts_obj['balance'][$bank_account['account_id']] = $bank_account['balance'];
        }
        if (!empty($start) && !empty($end)) {
            $bank_accounts_sql->whereDate('AT.operation_date', '>=', $start)
                        ->whereDate('AT.operation_date', '<=', $end);
        }
        $bank_accounts = $bank_accounts_sql->get();
        foreach ($bank_accounts as $bank_account) {
            $bank_accounts_obj['deposit'][$bank_account['account_id']] = $bank_account['total_deposit'];
            $bank_accounts_obj['withdraw'][$bank_account['account_id']] = $bank_account['total_withdraw'];
            $bank_accounts_obj['transfer_in'][$bank_account['account_id']] = $bank_account['transfer_in'];
            $bank_accounts_obj['transfer_out'][$bank_account['account_id']] = $bank_account['transfer_out'];
            $bank_accounts_obj['overall'][$bank_account['account_id']] = $bank_account['balance'] + $bank_account['total_deposit'] - $bank_account['total_withdraw'];
            $bank_accounts_obj['win_loss'][$bank_account['account_id']] = $bank_account['total_deposit'] - $bank_account['total_withdraw'];
        }
        //unclaimed trans
        $bank_accounts_sql = Account::leftjoin('account_transactions as AT', function ($join) {
                $join->on('AT.account_id', '=', 'accounts.id');
                $join->whereNull('AT.deleted_at');
            })->join('transactions AS T', 'T.id', 'AT.transaction_id')
            ->join('contacts AS C', 'C.id', 'T.contact_id')
            ->where('is_service', 0)
            ->where('accounts.name', '!=', 'Bonus Account')
            ->where('C.name', 'Unclaimed Trans')
            ->where('accounts.business_id', $business_id)
            ->select(['accounts.id as account_id', DB::raw("SUM( IF( AT.type='credit' AND (AT.sub_type IS NULL OR AT.`sub_type` != 'fund_transfer'), AT.amount, 0) ) as unclaim")])
            ->groupBy('accounts.id');
        $bank_accounts_sql->where(function ($q) {
            $q->where('account_type', '!=', 'capital');
            $q->orWhereNull('account_type');
        });
        if (!empty($start) && !empty($end)) {
            $bank_accounts_sql->whereDate('AT.operation_date', '>=', $start)
                ->whereDate('AT.operation_date', '<=', $end);
        }
        $bank_accounts = $bank_accounts_sql->get();
        foreach ($bank_accounts as $bank_account) {
            $bank_accounts_obj['unclaim'][$bank_account['account_id']] = $bank_account['unclaim'];
        }
        // back
        $bank_accounts_sql = Account::join('transaction_payments as tp', 'tp.account_id', 'accounts.id')
            ->join('transactions as t', 't.id', 'tp.transaction_id')
            ->where('accounts.is_service', 0)
            ->where('accounts.business_id', $business_id)
            ->select('accounts.id as account_id', DB::raw("SUM( IF(t.type = 'sell' AND t.payment_status = 'cancelled' , tp.amount, 0) ) as back"))
            ->groupBy('accounts.id');
        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $bank_accounts_sql->whereDate('t.transaction_date', '>=', $start)
                        ->whereDate('t.transaction_date', '<=', $end);
        }
        $bank_accounts = $bank_accounts_sql->get();
        foreach ($bank_accounts as $bank_account) {
            $bank_accounts_obj['back'][$bank_account['account_id']] = $bank_account['back'];
        }
        // in_ticket, out_ticket
        $sells = Transaction::leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
            ->leftJoin('accounts', 'tp.account_id', '=', 'accounts.id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->where('accounts.is_service', 0)
            ->whereDate('transactions.transaction_date', '>=', $start)
                        ->whereDate('transactions.transaction_date', '<=', $end)
            ->groupBy('accounts.id')
            ->select('accounts.id as account_id', DB::raw("COUNT(transactions.id) as in_ticket"))->get();
        $withdraws = Transaction::leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
            ->leftJoin('accounts', 'tp.account_id', '=', 'accounts.id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell_return')
            ->where('transactions.status', 'final')
            ->where('accounts.is_service', 0)
            ->whereDate('transactions.transaction_date', '>=', $start)
                        ->whereDate('transactions.transaction_date', '<=', $end)
            ->groupBy('accounts.id')
            ->select('accounts.id as account_id', DB::raw("COUNT(transactions.id) as out_ticket"))->get();
        foreach ($sells as $row) {
            $bank_accounts_obj['in_ticket'][$row['account_id']] = $row['in_ticket'];
        }
        foreach ($withdraws as $row) {
            $bank_accounts_obj['out_ticket'][$row['account_id']] = $row['out_ticket'];
        }
        // service charge 
        $withdraws = Transaction::join('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
        ->leftJoin('accounts', 'tp.account_id', '=', 'accounts.id')
        ->where('transactions.business_id', $business_id)
        ->where('transactions.type', 'sell_return')
        ->where('transactions.status', 'final')
        ->where('tp.method', 'bank_charge')
        ->where('accounts.is_service', 0)
        ->whereDate('transactions.transaction_date', '>=', $start)
                    ->whereDate('transactions.transaction_date', '<=', $end)
        ->groupBy('accounts.id')
        ->select('accounts.id as account_id', DB::raw("SUM(tp.amount) as bank_charge"))->get();

        foreach ($withdraws as $row) {
            $bank_accounts_obj['service'][$row['account_id']] = $row['bank_charge'];
        }
        // expenses
        $expenses = Transaction::join('transaction_payments AS tp', 'transactions.id', '=','tp.transaction_id')
                        ->leftJoin('accounts', 'tp.account_id', '=', 'accounts.id')
                        ->where('transactions.business_id', $business_id)
                        ->where('transactions.type', 'expense')
                        ->whereDate('tp.paid_on', '>=', $start)
                        ->whereDate('tp.paid_on', '<=', $end)
                        ->select('accounts.id as account_id', DB::raw('SUM(transactions.final_total) as final_total'))
                        ->groupBy('accounts.id')->get();

        foreach ($expenses as $row) {
            $bank_accounts_obj['expenses'][$row['account_id']] = $row['final_total'];
        }
        // services start

        $services = Account::where('business_id', $business_id)->where('is_service', 1)->get();
        $services_obj = [];
        $services_obj[0] = '';
        foreach ($services as $row){
            $services_obj[$row->id] = $row->name;
        }
        $service_accounts_obj = [];
        foreach ($this->service_columns as $key=>$service_column){
            $service_accounts_obj[$key][0] = $service_column;
        }
        // balance, deposit, withdraw
        $service_accounts_sql = Account::leftjoin('account_transactions as AT', function ($join) {
            $join->on('AT.account_id', '=', 'accounts.id');
            $join->whereNull('AT.deleted_at');
        })
            ->where('is_service', 1)
            ->where('business_id', $business_id)
            ->select(['name', 'account_number', 'accounts.note', 'accounts.id as account_id',
                'is_closed', DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")
                , DB::raw("SUM( IF(AT.type='credit', amount, 0) ) as total_deposit")
                , DB::raw("SUM( IF(AT.type='debit', amount, 0) ) as total_withdraw")
                , DB::raw("SUM( IF(AT.type='credit' AND AT.sub_type='fund_transfer', amount, 0) ) as transfer_in")
                , DB::raw("SUM( IF(AT.type='debit' AND AT.sub_type='fund_transfer', amount, 0) ) as transfer_out")])
            ->groupBy('accounts.id');
        $service_accounts_sql->where(function ($q) {
            $q->where('account_type', '!=', 'capital');
            $q->orWhereNull('account_type');
        });
        $service_account_balances = $service_accounts_sql->get();
        foreach($service_account_balances as $service_account){
            $service_accounts_obj['balance'][$service_account['account_id']] = $service_account['balance'];
        }
        if (!empty($start) && !empty($end)) {
            $service_accounts_sql->whereDate('AT.operation_date', '>=', $start)
                        ->whereDate('AT.operation_date', '<=', $end);
        }
        $service_accounts = $service_accounts_sql->get();
        foreach ($service_accounts as $service_account) {
            $service_accounts_obj['deposit'][$service_account['account_id']] = $service_account['total_deposit'];
            $service_accounts_obj['withdraw'][$service_account['account_id']] = $service_account['total_withdraw'];
            $service_accounts_obj['transfer_in'][$service_account['account_id']] = $service_account['transfer_in'];
            $service_accounts_obj['transfer_out'][$service_account['account_id']] = $service_account['transfer_out'];
        }
        //services end
        // basic_bonus, free_credit
        $transaction_ids_sql = Account::join('transaction_payments as tp', 'tp.account_id', 'accounts.id')
            ->join('transactions as t', 't.id', 'tp.transaction_id')
            ->where('accounts.is_service', 1)
            ->where('accounts.business_id', $business_id)
            ->select('t.transaction_id as transaction_id');

        $service_accounts_sql = Account::join('transaction_payments as tp', 'tp.account_id', 'accounts.id')
            ->where('accounts.business_id', $business_id)
            ->whereDate('tp.paid_on', '>=', $start)
            ->whereDate('tp.paid_on', '<=', $end)
            ->select('method', 'tp.amount', 'account_id', 'transaction_id');
        $payments_data = $service_accounts_sql->get();
        $temp = [];
        foreach($payments_data as $row) {
            if($row['method'] == 'basic_bonus')
                $temp[$row['transaction_id']]['basic_bonus'] = $row['amount'];
            else if($row['method'] == 'free_credit')
                $temp[$row['transaction_id']]['free_credit'] = $row['amount'];
            else if($row['method'] == 'service_transfer')
                $temp[$row['transaction_id']]['account_id'] = $row['account_id'];
        }

        foreach ($temp as $service_account) {
            if(isset($service_account['account_id'])){
                if(!isset($service_accounts_obj['bonus'][$service_account['account_id']])){
                    $service_accounts_obj['bonus'][$service_account['account_id']] = isset($service_account['basic_bonus']) ? $service_account['basic_bonus'] : 0;
                    $service_accounts_obj['free_credit'][$service_account['account_id']] = isset($service_account['free_credit']) ? $service_account['free_credit'] : 0;
                } else {
                    $service_accounts_obj['bonus'][$service_account['account_id']] += isset($service_account['basic_bonus']) ? $service_account['basic_bonus'] : 0;
                    $service_accounts_obj['free_credit'][$service_account['account_id']] += isset($service_account['free_credit']) ? $service_account['free_credit'] : 0;
                }
            }
        }
        // print_r($service_accounts_obj);exit;
        // calc total
//        end($banks_obj);
//        $bank_last_key = key($banks_obj);
//        $banks_obj[$bank_last_key + 1] = 'Total';

//        foreach($bank_accounts_obj as $bank_key => $bank_obj){
//            $total = 0;
//            foreach($bank_obj as $key => $item){
//                if($key != 0)
//                    $total += $item;
//            }
//            $bank_accounts_obj[$bank_key][$bank_last_key + 1] = $total;
//        }

        end($services_obj);
        $service_last_key = key($services_obj);
        $services_obj[$service_last_key + 1] = 'Total';

        foreach($service_accounts_obj as $service_key => $service_obj){
            $total = 0;
            foreach($service_obj as $key => $item){
                if($key != 0)
                    $total += $item;
            }
            $service_accounts_obj[$service_key][$service_last_key + 1] = $total;
        }

        $group_names = DisplayGroup::join('accounts', 'accounts.display_group_id', 'display_groups.id')
            ->select('display_groups.name as group_name', DB::raw('COUNT(accounts.id) as bank_cnt'))
            ->groupBy('display_groups.id')->get();

        $output['html_content'] = view('daily_report.report_table')->with(compact('banks_group_obj', 'bank_accounts_obj', 'services_obj', 'service_accounts_obj', 'group_names'))->render();
        return json_encode($output);
    }
}
