<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountTransaction;

use App\BusinessLocation;
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
        $this->bank_columns = ['balance' => 'Balance B/F', 'deposit' => 'In', 'withdraw' => 'Out', 'service' => 'Service', 'transfer_in' => 'Transfer + ', 'transfer_out' => 'Transfer - ', 'kiosk' => 'Kiosk', 'back' => 'Back',
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
        $categories = null;
        $business_id = request()->session()->get('user.business_id');
        $banks = Account::where('business_id', $business_id)->where('is_service', 0)->get();
        $banks_obj = [];
        $banks_obj[0] = '';
        foreach ($banks as $row){
            $banks_obj[$row->id] = $row->name;
        }
        $bank_accounts_obj = [];
        foreach ($this->bank_columns as $key=>$bank_column){
            $bank_accounts_obj[$key][0] = $bank_column;
        }
        // balance, deposit, withdraw
        $bank_accounts_sql = Account::leftjoin('account_transactions as AT', function ($join) {
            $join->on('AT.account_id', '=', 'accounts.id');
            $join->whereNull('AT.deleted_at');
        })
            ->where('is_service', 0)
            ->where('business_id', $business_id)
            ->select(['name', 'account_number', 'accounts.note', 'accounts.id as account_id',
                'is_closed', DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")
                , DB::raw("SUM( IF(AT.type='credit', amount, 0) ) as total_deposit")
                , DB::raw("SUM( IF(AT.type='debit', amount, 0) ) as total_withdraw")
                , DB::raw("SUM( IF(AT.type='credit' AND AT.sub_type='fund_transfer', amount, 0) ) as transfer_in")
                , DB::raw("SUM( IF(AT.type='debit' AND AT.sub_type='fund_transfer', amount, 0) ) as transfer_out")])
            ->groupBy('accounts.id');
        $bank_accounts_sql->where(function ($q) {
            $q->where('account_type', '!=', 'capital');
            $q->orWhereNull('account_type');
        });
        $bank_accounts = $bank_accounts_sql->get();
        foreach ($bank_accounts as $bank_account) {
            $bank_accounts_obj['balance'][$bank_account['account_id']] = $bank_account['balance'];
            $bank_accounts_obj['deposit'][$bank_account['account_id']] = $bank_account['total_deposit'];
            $bank_accounts_obj['withdraw'][$bank_account['account_id']] = $bank_account['total_withdraw'];
            $bank_accounts_obj['transfer_in'][$bank_account['account_id']] = $bank_account['transfer_in'];
            $bank_accounts_obj['transfer_out'][$bank_account['account_id']] = $bank_account['transfer_out'];
            $bank_accounts_obj['overall'][$bank_account['account_id']] = $bank_account['balance'] + $bank_account['total_deposit'] - $bank_account['total_withdraw'];
            $bank_accounts_obj['win_loss'][$bank_account['account_id']] = $bank_account['total_deposit'] - $bank_account['total_withdraw'];
        }
        // back
        $bank_accounts_sql = Account::join('transaction_payments as tp', 'tp.account_id', 'accounts.id')
            ->join('transactions as t', 't.id', 'tp.transaction_id')
            ->where('accounts.is_service', 0)
            ->where('accounts.business_id', $business_id)
            ->select('accounts.id as account_id', DB::raw("SUM( IF(t.type='sell', amount, IF(t.type='sell_return', -amount, 0)) ) as back"))
            ->groupBy('accounts.id');
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
            ->groupBy('accounts.id')
            ->select('accounts.id as account_id', DB::raw("COUNT(transactions.id) as in_ticket"))->get();
        $withdraws = Transaction::leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
            ->leftJoin('accounts', 'tp.account_id', '=', 'accounts.id')
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell_return')
            ->where('transactions.status', 'final')
            ->where('accounts.is_service', 0)
            ->groupBy('accounts.id')
            ->select('accounts.id as account_id', DB::raw("COUNT(transactions.id) as out_ticket"))->get();
        foreach ($sells as $row) {
            $bank_accounts_obj['in_ticket'][$row['account_id']] = $row['in_ticket'];
        }
        foreach ($withdraws as $row) {
            $bank_accounts_obj['out_ticket'][$row['account_id']] = $row['out_ticket'];
        }

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
        $service_accounts = $service_accounts_sql->get();
        foreach ($service_accounts as $service_account) {
            $service_accounts_obj['balance'][$service_account['account_id']] = $service_account['balance'];
            $service_accounts_obj['deposit'][$service_account['account_id']] = $service_account['total_deposit'];
            $service_accounts_obj['withdraw'][$service_account['account_id']] = $service_account['total_withdraw'];
            $service_accounts_obj['transfer_in'][$service_account['account_id']] = $service_account['transfer_in'];
            $service_accounts_obj['transfer_out'][$service_account['account_id']] = $service_account['transfer_out'];
        }



        return view('daily_report.index')
            ->with(compact('banks_obj', 'bank_accounts_obj', 'services_obj', 'service_accounts_obj'));
    }
    function getTableData(){
        $categories = null;
        $business_id = request()->session()->get('user.business_id');
        $banks = Account::where('business_id', $business_id)->where('is_service', 0)->get();
        $banks_obj = [];
        $banks_obj[0] = '';
        foreach ($banks as $row){
            $banks_obj[$row->id] = $row->name;
        }
        $bank_accounts_obj = [];
        foreach ($this->bank_columns as $key=>$bank_column){
            $bank_accounts_obj[$key][0] = $bank_column;
        }
        $start = request()->start_date;
        $end =  request()->end_date;
        // balance, deposit, withdraw
        $bank_accounts_sql = Account::leftjoin('account_transactions as AT', function ($join) {
            $join->on('AT.account_id', '=', 'accounts.id');
            $join->whereNull('AT.deleted_at');
        })
            ->where('is_service', 0)
            ->where('business_id', $business_id)
            ->select(['name', 'account_number', 'accounts.note', 'accounts.id as account_id',
                'is_closed', DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")
                , DB::raw("SUM( IF(AT.type='credit', amount, 0) ) as total_deposit")
                , DB::raw("SUM( IF(AT.type='debit', amount, 0) ) as total_withdraw")
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
        // back
        $bank_accounts_sql = Account::join('transaction_payments as tp', 'tp.account_id', 'accounts.id')
            ->join('transactions as t', 't.id', 'tp.transaction_id')
            ->where('accounts.is_service', 0)
            ->where('accounts.business_id', $business_id)
            ->select('accounts.id as account_id', DB::raw("SUM( IF(t.type='sell', amount, IF(t.type='sell_return', -amount, 0)) ) as back"))
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

        $output['html_content'] = view('daily_report.report_table')->with(compact('banks_obj', 'bank_accounts_obj', 'services_obj', 'service_accounts_obj'))->render();
        return json_encode($output);
    }
}
