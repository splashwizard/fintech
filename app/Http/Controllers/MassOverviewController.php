<?php

namespace App\Http\Controllers;

use App\Account;
use App\BusinessLocation;

use App\Contact;
use App\Currency;
use App\DisplayGroup;
use App\Transaction;
use App\Business;
use App\User;
use App\AdminHasBusiness;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;

use App\Utils\TransactionUtil;
use App\VariationLocationDetails;

use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MassOverviewController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $businessUtil;
    protected $transactionUtil;
    protected $moduleUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil
    ) {
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->bank_columns = ['currency' => 'Currency', 'balance' => 'Balance B/F', 'deposit' => 'In', 'withdraw' => 'Out', 'service' => 'Service', 'transfer_in' => 'Total In', 'transfer_out' => 'Total Out', 'kiosk' => 'Kiosk', 'back' => 'Cancel',
            'in_ticket' => 'In Ticket', 'out_ticket' => 'Out Ticket', 'overall' => 'Overall Total', 'win_loss' => 'Win/Loss', 'expenses' => 'Expenses', 'unclaim' => 'Unclaim'];
        $this->service_columns = ['balance' => 'Balance B/F', 'deposit' => 'In', 'withdraw' => 'Out', 'bonus' => 'Bonus', 'luckydraw' => 'Luckydraw', 'free_credit' => 'Free Credit', 'advance_credit' => 'Advance credit', 'transfer_in' => 'Transfer + ', 'transfer_out' => 'Transfer - '];
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $business_id = auth()->user()->hasRole('Superadmin') ? 0 : request()->session()->get('user.business_id');

        // if (!auth()->user()->can('dashboard.data') && !auth()->user()->hasRole('Superadmin')) {
        //     return view('home.index');
        // }

        $fy = $this->businessUtil->getCurrentFinancialYear($business_id);
        $date_filters['this_fy'] = $fy;
        $date_filters['this_month']['start'] = date('Y-m-01');
        $date_filters['this_month']['end'] = date('Y-m-t');
        $date_filters['this_week']['start'] = date('Y-m-d', strtotime('monday this week'));
        $date_filters['this_week']['end'] = date('Y-m-d', strtotime('sunday this week'));
        if (request()->ajax()) {
            $start_date = request()->start_date;
            $end_date = request()->end_date;
            if(auth()->user()->hasRole('Superadmin')){
                $query = Business::leftjoin(DB::raw('(SELECT * FROM transactions WHERE DATE(transactions.`transaction_date`) BETWEEN "'. $start_date .'" AND "'. $end_date.'" AND transactions.`type` IN ("sell", "sell_return", "expense") AND transactions.`status` = "final" ) AS t'), 't.business_id', '=', 'business.id')
                    ->groupBy('business.id')
                    ->orderBy('business.id', 'asc')
                    ->select('business.id', DB::raw("SUM(IF(t.type='sell', final_total, 0)) AS total_deposit"),
                        DB::raw("SUM(IF(t.type='sell_return', final_total, 0)) AS total_withdrawal, business.name as company_name"),
                        DB::raw("SUM(IF(t.type='expense', final_total, 0)) AS expense"));
            } 
            else{
                $business_id = request()->session()->get('user.business_id');
                $user_id = request()->session()->get('user.id');
                $data = AdminHasBusiness::where('user_id', $user_id)->get();
                $allowed_business_ids = [];
                foreach ($data as $row){
                    $allowed_business_ids[] = $row->business_id;
                }
                if(array_search($business_id, $allowed_business_ids) === FALSE)
                    $allowed_business_ids[] = $business_id;

                // $query = Business::leftjoin(DB::raw('(SELECT * FROM transactions WHERE DATE(transactions.`transaction_date`) BETWEEN "'. $start_date .'" AND "'. $end_date.'"  AND transactions.`type` IN ("sell", "sell_return") AND transactions.`status` = "final") AS t'), 't.business_id', '=', 'business.id')
                //     ->whereIn('business.id', $allowed_business_ids)
                //     ->groupBy('business.id')
                //     ->orderBy('business.id', 'asc')
                //     ->select('business.id', DB::raw("SUM(IF(t.type='sell', final_total, 0)) AS total_deposit"), DB::raw("SUM(IF(t.type='sell_return', IF( (SELECT method FROM transaction_payments AS tp WHERE tp.transaction_id = t.id) = 'bank_transfer', t.final_total, 0), 0)) AS total_withdrawal, business.name as company_name"));
                $query = Business::leftjoin(DB::raw('(SELECT * FROM transactions WHERE DATE(transactions.`transaction_date`) BETWEEN "'. $start_date .'" AND "'. $end_date.'" AND transactions.`type` IN ("sell", "sell_return", "expense") AND transactions.`status` = "final" ) AS t'), 't.business_id', '=', 'business.id')
                ->groupBy('business.id')
                ->whereIn('business.id', $allowed_business_ids)
                ->orderBy('business.id', 'asc')
                ->select('business.id', DB::raw("SUM(IF(t.type='sell', final_total, 0)) AS total_deposit"),
                    DB::raw("SUM(IF(t.type='sell_return', final_total, 0)) AS total_withdrawal, business.name as company_name"),
                    DB::raw("SUM(IF(t.type='expense', final_total, 0)) AS expense"));
                
                //Check for permitted locations of a user
                // $permitted_locations = auth()->user()->permitted_locations();
                // if ($permitted_locations != 'all') {
                //     $query->whereIn('transactions.location_id', $permitted_locations);
                // }
            }
            $datatable = Datatables::of($query)->addColumn(
                'action',
                function ($row) {
                    if(auth()->user()->hasRole('Superadmin'))
                        $html = '<a href="'.action("MassOverviewController@edit", [$row->id]).'" class="btn btn-info btn-xs">Edit</a>';
                    else
                        $html = '<a href="'.action("MassOverviewController@show", [$row->id]).'" class="btn btn-info btn-xs">View</a>';
                    return $html;
                })
                ->addColumn('currency', null)
                ->addColumn('service', null)
                ->addColumn('transfer_in', null)
                ->addColumn('transfer_out', null)
                ->addColumn('kiosk', null)
                ->addColumn('cancel', null)
                ->addColumn('expense', null)
                ->addColumn('borrow', null)
                ->addColumn('return', null)
                ->editColumn(
                'total_deposit',
                function ($row) {
                    if(!isset($row->total_deposit))
                        return 0;
                    return $row->total_deposit;
                })->editColumn(
                'total_withdrawal',
                function ($row) {
                    if(!isset($row->total_withdrawal))
                        return 0;
                    return $row->total_withdrawal;
                })->editColumn(
                    'expense',
                    function ($row) {
                        if(!isset($row->expense))
                            return 0;
                        return $row->expense;
                    });
            $rawColumns = ['id', 'company_name', 'total_deposit', 'total_withdrawal', 'expense', 'kiosk', 'borrow', 'action'];

            return $datatable->rawColumns($rawColumns)
                ->make(true);
        }
        return view('mass_overview.index', compact('date_filters'));
    }

    public function getTableData(){
        $allowed_business_ids = [];
        if(auth()->user()->hasRole('Superadmin')){
            $data = Business::get();
            foreach ($data as $row) {
                $allowed_business_ids[] = $row->id;
            }
        } else {
            $user_business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');
            $data = AdminHasBusiness::where('user_id', $user_id)->get();
            foreach ($data as $row){
                $allowed_business_ids[] = $row->business_id;
            }
            if(array_search($user_business_id, $allowed_business_ids) === FALSE)
                $allowed_business_ids[] = $user_business_id;
        }
//        print_r($allowed_business_ids);exit;
        $table_data = [];
        foreach ($allowed_business_ids as $business_id) {
            $business_name = Business::find($business_id)->name;
            $new_row = [];
            $new_row['serial'] = $business_id;
            $new_row['name'] = $business_name;
            if(auth()->user()->hasRole('Superadmin'))
                $new_row['action'] = '<a href="'.action("MassOverviewController@edit", [$business_id]).'" class="btn btn-info btn-xs">Edit</a>';
            else
                $new_row['action'] = '<a href="'.action("MassOverviewController@show", [$business_id]).'" class="btn btn-info btn-xs">View</a>';
            $table_data[] = $new_row;

            $group_cnt = DisplayGroup::where('business_id', $business_id)->count();
            if($group_cnt > 0){
                $bank_accounts_obj = [];
                //currency
    //            $bank_accounts_sql = Account::leftjoin('currencies AS c', 'c.id', 'accounts.currency_id')->where('is_service', 0)
    //                ->where('accounts.name', '!=', 'Bonus Account')
    //                ->where('accounts.business_id', $business_id)
    //                ->select(['c.code as code', 'accounts.id as account_id']);
    //            $bank_account_currencies = $bank_accounts_sql->get();
    //            foreach ($bank_account_currencies as $bank_account) {
    //                $bank_accounts_obj[$bank_account['account_id']]['currency'] = $bank_account['code'];
    //            }
                //        print_r($bank_accounts_obj);exit;

                $start = request()->start_date;
                $end = request()->end_date;
                // balance, deposit, withdraw
                $bank_accounts_sql = Account::leftjoin('account_transactions as AT', function ($join) {
                    $join->on('AT.account_id', '=', 'accounts.id');
                    $join->whereNull('AT.deleted_at');
                })
                    ->where('is_service', 0)
                    ->where('name', '!=', 'Bonus Account')
                    ->where('display_group_id', '!=', 0)
                    ->where('business_id', $business_id)
                    ->select(['accounts.display_group_id as group_id',
                        'is_closed', DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")
                        , DB::raw("SUM( IF( AT.type='credit' AND (AT.sub_type IS NULL OR AT.`sub_type` != 'fund_transfer'), AT.amount, 0) ) as total_deposit")
                        , DB::raw("SUM( IF( AT.type='debit' AND (AT.sub_type IS NULL OR AT.`sub_type` != 'fund_transfer'), AT.amount, 0) ) as total_withdraw")
                        , DB::raw("SUM( IF(AT.type='credit' AND AT.sub_type='fund_transfer', amount, 0) ) as transfer_in")
                        , DB::raw("SUM( IF(AT.type='debit' AND AT.sub_type='fund_transfer', amount, 0) ) as transfer_out")])
                    ->groupBy('display_group_id');
                $bank_accounts_sql->where(function ($q) {
                    $q->where('account_type', '!=', 'capital');
                    $q->orWhereNull('account_type');
                });
                $bank_account_balances = $bank_accounts_sql->get();
                //        print_r($bank_account_balances);exit;
                foreach ($bank_account_balances as $bank_account) {
                    $bank_accounts_obj[$bank_account['account_id']]['balance'] = $bank_account['balance'];
                }
                if (!empty($start) && !empty($end)) {
                    $bank_accounts_sql->whereDate('AT.operation_date', '>=', $start)
                        ->whereDate('AT.operation_date', '<=', $end);
                }
                $bank_accounts = $bank_accounts_sql->get();
                foreach ($bank_accounts as $bank_account) {
                    $bank_accounts_obj[$bank_account['group_id']]['deposit'] = $bank_account['total_deposit'];
                    $bank_accounts_obj[$bank_account['group_id']]['withdraw'] = $bank_account['total_withdraw'];
                    $bank_accounts_obj[$bank_account['group_id']]['transfer_in'] = $bank_account['transfer_in'];
                    $bank_accounts_obj[$bank_account['group_id']]['transfer_out'] = $bank_account['transfer_out'];
                    $bank_accounts_obj[$bank_account['group_id']]['overall'] = $bank_account['balance'] + $bank_account['total_deposit'] - $bank_account['total_withdraw'];
                    $bank_accounts_obj[$bank_account['group_id']]['win_loss'] = $bank_account['total_deposit'] - $bank_account['total_withdraw'];
                }
                // back
                $bank_accounts_sql = Account::join('transaction_payments as tp', 'tp.account_id', 'accounts.id')
                    ->join('transactions as t', 't.id', 'tp.transaction_id')
                    ->where('accounts.is_service', 0)
                    ->where('accounts.business_id', $business_id)
                    ->where('accounts.display_group_id', '!=', 0)
                    ->select('accounts.display_group_id as group_id', DB::raw("SUM( IF(t.type = 'sell' AND t.payment_status = 'cancelled' , tp.amount, 0) ) as cancel"))
                    ->groupBy('accounts.display_group_id');
                if (!empty(request()->start_date) && !empty(request()->end_date)) {
                    $bank_accounts_sql->whereDate('t.transaction_date', '>=', $start)
                        ->whereDate('t.transaction_date', '<=', $end);
                }
                $bank_accounts = $bank_accounts_sql->get();
                foreach ($bank_accounts as $bank_account) {
                    $bank_accounts_obj[$bank_account['group_id']]['cancel'] = $bank_account['cancel'];
                }
                // service charge
                $withdraws = Transaction::join('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                    ->leftJoin('accounts', 'tp.account_id', '=', 'accounts.id')
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'sell_return')
                    ->where('transactions.status', 'final')
                    ->where('tp.method', 'bank_charge')
                    ->where('accounts.is_service', 0)
                    ->where('accounts.display_group_id', '!=', 0)
                    ->whereDate('transactions.transaction_date', '>=', $start)
                    ->whereDate('transactions.transaction_date', '<=', $end)
                    ->groupBy('accounts.display_group_id')
                    ->select('accounts.display_group_id as group_id', DB::raw("SUM(tp.amount) as bank_charge"))->get();

                foreach ($withdraws as $row) {
                    $bank_accounts_obj[$row['group_id']]['service'] = $row['bank_charge'];
                }
                // expenses
                $expenses = Transaction::join('transaction_payments AS tp', 'transactions.id', '=', 'tp.transaction_id')
                    ->leftJoin('accounts', 'tp.account_id', '=', 'accounts.id')
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'expense')
                    ->whereDate('tp.paid_on', '>=', $start)
                    ->whereDate('tp.paid_on', '<=', $end)
                    ->select('accounts.display_group_id as group_id', DB::raw('SUM(transactions.final_total) as final_total'))
                    ->where('accounts.display_group_id', '!=', 0)
                    ->groupBy('accounts.display_group_id')->get();

                foreach ($expenses as $row) {
                    $bank_accounts_obj[$row['group_id']]['expenses'] = $row['final_total'];
                }

                $group_names = DisplayGroup::where('business_id', $business_id)->get();
                $keys = ['deposit', 'withdraw', 'service', 'transfer_in', 'transfer_out', 'cancel', 'expenses'];
                foreach ($group_names as $row) {
                    foreach ($keys as $key){
                        if (!isset($bank_accounts_obj[$row->id][$key]))
                            $bank_accounts_obj[$row->id][$key] = 0;
                    }
                    $bank_accounts_obj[$row->id]['name'] = $row->name;
                    $table_data[] = $bank_accounts_obj[$row->id];
                }
            }
        }
        $columns = ['serial', 'name', 'deposit', 'withdraw', 'service', 'transfer_in', 'transfer_out', 'kiosk', 'cancel', 'expenses', 'borrow', 'return', 'action'];

        $output['html_content'] = view('mass_overview.report_table')->with(compact('table_data', 'columns'))->render();
        return json_encode($output);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Business::where('id', $id)->select('name')->get();
        $company_name = $data[0]->name;
        $business_id = $id;
        return view('mass_overview.show', compact('company_name', 'business_id'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(auth()->user()->hasRole('Superadmin')){
            $business = Business::findOrFail($id);

            $currency = $business->currency;
            $currency_data = ['id' => $currency->id,
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'thousand_separator' => $currency->thousand_separator,
                'decimal_separator' => $currency->decimal_separator
            ];
            session()->put('business', $business);
            session()->put('currency', $currency_data);

            $data = Business::where('id', $id)->select('name')->get();
            $company_name = $data[0]->name;
            $business_id = $id;
            return view('mass_overview.edit', compact('company_name', 'business_id'));
        }
    }

    public function createAdminToBusiness($business_id){
        $users = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', 'Admin%');
        })->get();
        $admin_data = $users->pluck('username', 'id');
        return view('mass_overview.create_admin')
            ->with(compact('admin_data', 'business_id'));
    }

    public function storeAdminToBusiness(){
        if (request()->ajax()) {
            try {
                $business_id = request()->get('business_id');
                $admin_id = request()->get('admin_id');

//                $business = AdminHasBusiness::create(['id' => 2, 'user_id' => $admin_id, 'business_id'=> $business_id]);

                DB::table('admin_has_business')->insert(['user_id' => $admin_id, 'business_id'=> $business_id]);
                $output = ['success' => true,
                    'msg' => __("mass_overview.admin_added_success")
                ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = ['success' => false,
                    'msg' => __("messages.something_fwent_wrong")
                ];
            }

            return $output;
        }
        return null;
    }

    public function getBankDetails(){
        if (request()->ajax()) {
            $business_id = request()->get('business_id');
            $accounts = Account::leftjoin('account_transactions as AT', function ($join) {
                $join->on('AT.account_id', '=', 'accounts.id');
                $join->whereNull('AT.deleted_at');
            })
                ->where('is_service', 0)
                ->where('business_id', $business_id)
                ->select(['name', DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")])
                ->groupBy('accounts.id');

//            $account_type = request()->input('account_type');
//
//            if ($account_type == 'capital') {
//                $accounts->where('account_type', 'capital');
//            } elseif ($account_type == 'other') {
//                $accounts->where(function ($q) {
//                    $q->where('account_type', '!=', 'capital');
//                    $q->orWhereNull('account_type');
//                });
//            }

            return DataTables::of($accounts)
                ->editColumn('balance', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true">' . $row->balance . '</span>';
                })->rawColumns(['balance', 'name'])->make(true);
        }
    }


    public function getServiceDetails(){
        if (request()->ajax()) {
            $business_id = request()->get('business_id');
            $accounts = Account::leftjoin('account_transactions as AT', function ($join) {
                $join->on('AT.account_id', '=', 'accounts.id');
                $join->whereNull('AT.deleted_at');
            })
                ->where('is_service', 1)
                ->where('business_id', $business_id)
                ->select(['name', DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")])
                ->groupBy('accounts.id');

            return DataTables::of($accounts)
                ->editColumn('balance', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true">' . $row->balance . '</span>';
                })->rawColumns(['balance', 'name'])->make(true);
        }
    }

    public function getUsers(){
        if (request()->ajax()) {
            $business_id = request()->get('business_id');
            $business = AdminHasBusiness::where('business_id', $business_id)->get();
            $user_arr = [];
            foreach ($business as $row){
                $user_arr[] = $row->user_id;
            }

            $users = User::whereIn('id', $user_arr)
                ->select(['id', 'username', 'business_id',
                    DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as full_name"), 'email']);

            return Datatables::of($users)
                ->addColumn(
                    'role',
                    function ($row) {
                        $role_name = $this->moduleUtil->getUserRoleName($row->id);
                        return $role_name;
                    }
                )
                ->addColumn(
                    'action',
                    '<a data-href="{{action(\'MassOverviewController@removeAdminFromBusiness\', [$id])}}" class="btn btn-xs btn-danger delete_user_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</a>'
                )
                ->filterColumn('full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('id')
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    public function removeAdminFromBusiness($user_id){
        try {
            $business_id = request()->get('business_id');
            $business = AdminHasBusiness::where('business_id', $business_id)->where('user_id', $user_id)->first();
            $business->delete();
            $output = ['success' => true,
                'msg' => __("mass_overview.admin_added_success")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false,
                'msg' => __("messages.something_fwent_wrong")
            ];
        }

        return $output;
    }
}
