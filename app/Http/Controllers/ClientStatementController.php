<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountTransaction;
use App\BankBrand;
use App\Business;
use App\Contact;
use App\CountryCode;
use App\CustomerGroup;
use App\GameId;
use App\Membership;
use App\Transaction;
use App\TransactionPayment;
use App\User;
use App\Utils\ContactUtil;
use App\Utils\GameUtil;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use DB;
use Excel;
use Illuminate\Http\Request;
use Modules\Essentials\Notifications\EditCustomerNotification;
use Yajra\DataTables\Facades\DataTables;
use \jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;

class ClientStatementController extends Controller
{
    protected $commonUtil;
    protected $transactionUtil;
    protected $moduleUtil;
    protected $gameUtil;
    protected $contactUtil;

    /**
     * Constructor
     *
     * @param Util $commonUtil
     * @return void
     */
    public function __construct(
        Util $commonUtil,
        ModuleUtil $moduleUtil,
        TransactionUtil $transactionUtil,
        GameUtil $gameUtil,
        ContactUtil $contactUtil
    ) {
        $this->commonUtil = $commonUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
        $this->gameUtil = $gameUtil;
        $this->contactUtil = $contactUtil;
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
            $business_id = request()->session()->get('user.business_id');
            $query = Contact::leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
                ->leftjoin('customer_groups AS cg', 'contacts.customer_group_id', '=', 'cg.id')
                ->leftjoin('memberships AS m', 'contacts.membership_id', '=', 'm.id')
                ->leftjoin('transaction_payments as tp',
                    'tp.transaction_id',
                    '=',
                    't.id')
                ->where(function($q) use ($business_id) {
                    $q->where('contacts.business_id', $business_id);
                    $q->orWhere('contacts.business_id', 0);
                })
                ->where('contacts.blacked_by_user', null)
                ->where('contacts.type', 'customer');
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $query->whereDate('t.transaction_date', '>=', $start)
                    ->whereDate('t.transaction_date', '<=', $end);
            }
            $query->addSelect(['contacts.contact_id', 'contacts.name', 'contacts.email', 'contacts.created_at', 'contacts.remarks1', 'contacts.remarks2', 'contacts.remarks3',
                'contacts.total_rp', 'cg.name as customer_group', 'm.name as membership', 'contacts.city', 'contacts.state', 'contacts.country', 'contacts.landmark', 'contacts.mobile', 'contacts.id', 'contacts.is_default',
                DB::raw( 'DATE_FORMAT(STR_TO_DATE(birthday, "%Y-%m-%d"), "%d/%m") as birthday'),
                DB::raw("SUM(IF(card_type = 'credit' && method= 'bank_transfer', tp.amount, 0)) as due"),
                DB::raw("SUM(IF(card_type = 'debit' && method != 'service_transfer', tp.amount, 0)) as return_due"),
                DB::raw("SUM(IF(card_type = 'credit' &&  ( method= 'basic_bonus' || method= 'free_credit'), tp.amount, 0)) as bonus"),
                DB::raw("SUM(IF(card_type = 'credit' &&  method= 'free_credit', tp.amount, 0)) as free_credit"),
                DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid")
            ])
                ->groupBy('contacts.id');
            $is_admin_or_super = auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Admin');
            $contacts = Datatables::of($query)
                ->editColumn(
                    'contacts.landmark',
                    '{{implode(", ", array_filter([$landmark, $city, $state, $country]))}}'
                )
                ->editColumn(
                    'due',
                    '<span class="display_currency contact_due" data-orig-value="{{$due}}" data-highlight=true>{{$due}}</span>'
                )
                ->editColumn(
                    'return_due',
                    '<span class="display_currency return_due" data-orig-value="{{$return_due}}" data-highlight=false>{{$return_due}}</span>'
                )
                ->editColumn(
                    'bonus',
                    '<span class="display_currency bonus" data-orig-value="{{$bonus}}" data-highlight=false>{{$bonus}}</span>'
                )
                ->editColumn(
                    'free_credit',
                    '<span class="display_currency free_credit" data-orig-value="{{$free_credit}}" data-highlight=false>{{$free_credit}}</span>'
                )
                ->addColumn(
                    'win_loss',
                    '<span class="display_currency win_loss" data-orig-value="{{$return_due - $due}}" data-highlight=false>{{$return_due - $due}}</span>'
                )
                ->editColumn('contacts.total_rp', '{{$total_rp ?? 0}}')
                ->editColumn('contacts.created_at', '{{@format_date($created_at)}}')
                ->removeColumn('contacts.state')
                ->removeColumn('contacts.country')
                ->removeColumn('contacts.city')
                ->removeColumn('contacts.type')
                ->removeColumn('contacts.id')
                ->removeColumn('contacts.is_default');
            $reward_enabled = (request()->session()->get('business.enable_rp') == 1) ? true : false;
            $raw = ['due', 'return_due', 'bonus', 'win_loss', 'free_credit'];
            return $contacts->rawColumns($raw)->toJson();
        }

        $type = 'customer';
        return view('client_statement.index')
            ->with(compact('type'));
    }
}
