<?php

namespace App\Http\Controllers;

use App\Account;
use App\BusinessLocation;

use App\Contact;
use App\Currency;
use App\Transaction;
use App\Business;
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
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('dashboard.data')) {
            return view('home.index');
        }

        $fy = $this->businessUtil->getCurrentFinancialYear($business_id);
        $date_filters['this_fy'] = $fy;
        $date_filters['this_month']['start'] = date('Y-m-01');
        $date_filters['this_month']['end'] = date('Y-m-t');
        $date_filters['this_week']['start'] = date('Y-m-d', strtotime('monday this week'));
        $date_filters['this_week']['end'] = date('Y-m-d', strtotime('sunday this week'));
        if (request()->ajax()) {
            $start = request()->start;
            $end = request()->end;
            $business_id = request()->session()->get('user.business_id');
            $query = Transaction::join('business', 'transactions.business_id', '=', 'business.id')
                ->where('transactions.business_id', $business_id)
                ->whereIn('transactions.type', ['sell', 'sell_return'])
                ->where('transactions.status', 'final')
                ->select('transactions.id','transactions.business_id as business_id', DB::raw("SUM(IF(transactions.type='sell', final_total, 0)) AS total_deposit"), DB::raw("SUM(IF(transactions.type='sell_return', final_total, 0)) AS total_withdrawal, business.name as company_name"));

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty($start_date) && !empty($end_date)) {
                $query->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date]);
            }

            if (empty($start_date) && !empty($end_date)) {
                $query->whereDate('transaction_date', '<=', $end_date);
            }


            $transaction_types = ['sell_return'];
            $transaction_totals = $this->transactionUtil->getTransactionTotals($business_id, $transaction_types, $start, $end);
            $datatable = Datatables::of($query)->addColumn(
                'action',
                function ($row) {
                    $html = '<a href="'.action("MassOverviewController@show", [$row->business_id]).'" class="btn btn-info btn-xs">View</a>';
                    return $html;
                });
            $rawColumns = ['business_id', 'company_name', 'total_deposit', 'total_withdrawal', 'action'];

            return $datatable->rawColumns($rawColumns)
                ->make(true);
        }
        return view('mass_overview.index', compact('date_filters'));
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
        return view('mass_overview.show', compact('company_name'));
    }

    public function getBankDetails(){
        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
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
        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
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
}
