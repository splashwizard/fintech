<?php

namespace App\Http\Controllers;

use App\BankBrand;
use App\BusinessLocation;
use App\Contact;
use App\Currency;
use App\CustomerGroup;
use App\DisplayGroup;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\Util;
use App\Utils\TransactionUtil;
use App\Utils\ContactUtil;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Account;
use App\AccountTransaction;
use App\TransactionPayment;

use Yajra\DataTables\Facades\DataTables;
use \jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;


use DB;

class DashboardDepositController extends Controller
{
    protected $commonUtil;
    protected $transactionUtil;
    protected $contactUtil;

    /**
     * Constructor
     *
     * @param Util $commonUtil
     * @return void
     */
    public function __construct(Util $commonUtil,
                                TransactionUtil $transactionUtil,
                                ContactUtil $contactUtil,
                                BusinessUtil $businessUtil
    ) {
        $this->commonUtil = $commonUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->contactUtil = $contactUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');
        if (request()->ajax()) {
            $accounts = Account::leftjoin('account_transactions as AT', function ($join) {
                $join->on('AT.account_id', '=', 'accounts.id');
                $join->whereNull('AT.deleted_at');
            })
                ->leftjoin( 'transactions as T',
                    'AT.transaction_id',
                    '=',
                    'T.id')
                ->leftjoin('currencies', 'currencies.id', 'accounts.currency_id')
                ->leftjoin('bank_brands', 'bank_brands.id', 'accounts.bank_brand_id')
                ->where('accounts.is_service', 0)
                ->where('accounts.name', '!=', 'Bonus Account')
                ->where('accounts.business_id', $business_id)
                ->where(function ($q) {
                    $q->where('T.payment_status', '!=', 'cancelled');
                    $q->orWhere('T.payment_status', '=', null);
                })
                ->select(['accounts.name', 'accounts.account_number', 'accounts.note', 'accounts.id', 'currencies.code as currency',
                    'bank_brands.name as bank_brand', 'accounts.is_display_front',
                    'accounts.is_closed',
                    \Illuminate\Support\Facades\DB::raw("SUM( IF( accounts.shift_closed_at IS NULL OR AT.operation_date >= accounts.shift_closed_at,  IF( AT.type='credit', AT.amount, -1*AT.amount), 0) ) as balance"),
                ])
                ->groupBy('accounts.id');

            $account_type = request()->input('account_type');

            if ($account_type == 'capital') {
                $accounts->where('account_type', 'capital');
            } elseif ($account_type == 'other') {
                $accounts->where(function ($q) {
                    $q->where('account_type', '!=', 'capital');
                    $q->orWhereNull('account_type');
                });
            }
            $is_admin_or_super = auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Admin');
            if(!$is_admin_or_super){
                $accounts->where('is_safe','0');
            }

            return DataTables::of($accounts)
                ->editColumn('name', function ($row) {
                    if ($row->is_closed == 1) {
                        return $row->name . ' <small class="label pull-right bg-red no-print">' . __("account.closed") . '</small><span class="print_section">(' . __("account.closed") . ')</span>';
                    } else {
                        return $row->name;
                    }
                })
                ->editColumn('balance', function ($row) {
                    return '<span class="display_currency">' . $row->balance . '</span>';
                })
                ->addColumn('is_display_front', function ($row) {
                    return  '<input type="checkbox" class="account_display_front" data-id="'.$row->id.'"'. ($row->is_display_front ? 'checked' : null) .'>' ;
                })
                ->removeColumn('id')
                ->removeColumn('is_closed')
                ->rawColumns(['is_display_front', 'name'])
                ->make(true);
        }

        $not_linked_payments = TransactionPayment::leftjoin(
            'transactions as T',
            'transaction_payments.transaction_id',
            '=',
            'T.id'
        )
            ->whereNull('transaction_payments.parent_id')
            ->where('transaction_payments.business_id', $business_id)
            ->whereNull('account_id')
            ->count();
        return view('dashboard_deposit.index')
            ->with(compact('not_linked_payments'));
    }

    public function updateDisplayFront(Request $request, $id){
        $is_display_front = $request->get('is_display_front');
        Account::find($id)->update(['is_display_front' => $is_display_front]);
        return ['success' => true];
    }
}
