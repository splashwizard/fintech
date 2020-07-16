<?php

namespace App\Http\Controllers;

use App\Account;
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
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use DB;
use Excel;
use Illuminate\Http\Request;
use Modules\Essentials\Notifications\EditCustomerNotification;
use Yajra\DataTables\Facades\DataTables;
use \jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;

class ContactController extends Controller
{
    protected $commonUtil;
    protected $transactionUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param Util $commonUtil
     * @return void
     */
    public function __construct(
        Util $commonUtil,
        ModuleUtil $moduleUtil,
        TransactionUtil $transactionUtil
    ) {
        $this->commonUtil = $commonUtil;
        $this->moduleUtil = $moduleUtil;
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
        $type = request()->get('type');

        $types = ['supplier', 'customer', 'blacklisted_customer'];

        if (empty($type) || !in_array($type, $types)) {
            return redirect()->back();
        }

        if (request()->ajax()) {
            if ($type == 'supplier') {
                return $this->indexSupplier();
            } elseif ($type == 'customer') {
                return $this->indexCustomer();
            } elseif ($type == 'blacklisted_customer') {
                return $this->indexBlacklistedCustomer();
            } else {
                die("Not Found");
            }
        }

        $reward_enabled = (request()->session()->get('business.enable_rp') == 1 && in_array($type, ['customer', 'blacklisted_customer'])) ? true : false;

        return view('contact.index')
            ->with(compact('type', 'reward_enabled'));
    }

    /**
     * Returns the database object for supplier
     *
     * @return \Illuminate\Http\Response
     */
    private function indexSupplier()
    {
        if (!auth()->user()->can('supplier.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $contact = Contact::leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
                    ->where('contacts.business_id', $business_id)
                    ->onlySuppliers()
                    ->select(['contacts.contact_id', 'supplier_business_name', 'name', 'contacts.created_at', 'mobile',
                        'contacts.type', 'contacts.id',
                        DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                        DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                        DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                        DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_return_paid"),
                        DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                        DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid")
                        ])
                    ->groupBy('contacts.id');

        return Datatables::of($contact)
            ->addColumn(
                'due',
                '<span class="display_currency contact_due" data-orig-value="{{$total_purchase - $purchase_paid}}" data-currency_symbol=true data-highlight=false>{{$total_purchase - $purchase_paid }}</span>'
            )
            ->addColumn(
                'return_due',
                '<span class="display_currency return_due" data-orig-value="{{$total_purchase_return - $purchase_return_paid}}" data-currency_symbol=true data-highlight=false>{{$total_purchase_return - $purchase_return_paid }}</span>'
            )
            ->addColumn(
                'action',
                '<div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                        data-toggle="dropdown" aria-expanded="false">' .
                        __("messages.actions") .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                @if(($total_purchase + $opening_balance - $purchase_paid - $opening_balance_paid)  > 0)
                    <li><a href="{{action(\'TransactionPaymentController@getPayContactDue\', [$id])}}?type=purchase" class="pay_purchase_due"><i class="fa fa-money" aria-hidden="true"></i>@lang("contact.pay_due_amount")</a></li>
                @endif
                @if(($total_purchase_return - $purchase_return_paid)  > 0)
                    <li><a href="{{action(\'TransactionPaymentController@getPayContactDue\', [$id])}}?type=purchase_return" class="pay_purchase_due"><i class="fa fa-money" aria-hidden="true"></i>@lang("lang_v1.receive_purchase_return_due")</a></li>
                @endif
                @can("supplier.view")
                    <li><a href="{{action(\'ContactController@show\', [$id])}}"><i class="fa fa-external-link" aria-hidden="true"></i> @lang("messages.view")</a></li>
                @endcan
                @can("supplier.update")
                    <li><a href="{{action(\'ContactController@edit\', [$id])}}" class="edit_contact_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a></li>
                @endcan
                @can("supplier.delete")
                    <li><a href="{{action(\'ContactController@destroy\', [$id])}}" class="delete_contact_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</a></li>
                @endcan </ul></div>'
            )
            ->editColumn('created_at', '{{@format_date($created_at)}}')
            ->removeColumn('opening_balance')
            ->removeColumn('opening_balance_paid')
            ->removeColumn('type')
            ->removeColumn('id')
            ->removeColumn('total_purchase')
            ->removeColumn('purchase_paid')
            ->removeColumn('total_purchase_return')
            ->removeColumn('purchase_return_paid')
            ->rawColumns([5, 6, 7])
            ->make(false);
    }

    /**
     * Returns the database object for customer
     *
     * @return \Illuminate\Http\Response
     */
    private function indexCustomer()
    {
        if (!auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $month = request()->get('month');
        $query = Contact::leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
            ->leftjoin('customer_groups AS cg', 'contacts.customer_group_id', '=', 'cg.id')
            ->leftjoin('memberships AS m', 'contacts.membership_id', '=', 'm.id')
            ->where(function($q) use ($business_id) {
                $q->where('contacts.business_id', $business_id);
                $q->orWhere('contacts.business_id', 0);
            })
            ->where('contacts.blacked_by_user', null)
            ->where('contacts.type', 'customer');
        if($month!="0")
            $query->where(DB::raw('DATE_FORMAT(STR_TO_DATE(birthday, "%Y-%m-%d"), "%m")'), $month);
        $query->addSelect(['contacts.contact_id', 'contacts.name', 'contacts.email', 'contacts.created_at', 'contacts.remarks', 'total_rp', 'cg.name as customer_group', 'm.name as membership', 'city', 'state', 'country', 'landmark', 'mobile', 'contacts.id', 'is_default',
            DB::raw( 'DATE_FORMAT(STR_TO_DATE(birthday, "%Y-%m-%d"), "%d/%m") as birthday'),
            DB::raw("SUM(IF(t.type = 'sell'  AND t.status = 'final', final_total, 0)) as total_invoice"),
            DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
//                        DB::raw("SUM(IF( t.type = 'sell_return' AND (SELECT transaction_payments.method FROM transaction_payments WHERE transaction_payments.transaction_id=t.id) = 'bank_transfer', final_total, 0)) as total_sell_return"),
            DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
            DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
            DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
            DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid")
            ])
        ->groupBy('contacts.id');
        $is_admin_or_super = auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Admin');
        $contacts = Datatables::of($query)
            ->editColumn(
                'landmark',
                '{{implode(array_filter([$landmark, $city, $state, $country]), ", ")}}'
            )
            ->addColumn(
                'due',
                '<span class="display_currency contact_due" data-orig-value="{{$total_invoice}}" data-highlight=true>{{($total_invoice)}}</span>'
//                '<span class="display_currency contact_due" data-orig-value="{{$total_invoice - $invoice_received}}" data-currency_symbol=true data-highlight=true>{{($total_invoice - $invoice_received)}}</span>'
            )
            ->addColumn(
                'return_due',
                '<span class="display_currency return_due" data-orig-value="{{$total_sell_return}}" data-highlight=false>{{$total_sell_return}}</span>'
//                '<span class="display_currency return_due" data-orig-value="{{$total_sell_return - $sell_return_paid}}" data-currency_symbol=true data-highlight=false>{{$total_sell_return - $sell_return_paid }}</span>'
            )
            ->addColumn(
                'action',
                '<div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                        data-toggle="dropdown" aria-expanded="false">' .
                        __("messages.actions") .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                @can("customer.view")
                    <li><a href="{{action(\'ContactController@show\', [$id])}}"><i class="fa fa-external-link" aria-hidden="true"></i> @lang("messages.view")</a></li>
                @endcan
                @if(!$is_default)
                @can("customer.update")
                    <li><a href="{{action(\'ContactController@edit\', [$id])}}?type=customer" class="edit_contact_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a></li>
                @endcan
                @if(auth()->user()->hasRole("Superadmin") || auth()->user()->hasRole("Admin#" . auth()->user()->business_id) || auth()->user()->hasRole("Admin"))
                    <li><a href="{{action(\'ContactController@blacklist\', [$id])}}" class="edit_blacklist_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.blacklist")</a></li>
                @endif
                @can("customer.delete")
                    <li><a href="{{action(\'ContactController@destroy\', [$id])}}" class="delete_contact_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</a></li>
                @endcan
                @endif </ul></div>'
            )
            ->editColumn('total_rp', '{{$total_rp ?? 0}}')
            ->editColumn('created_at', '{{@format_date($created_at)}}')
            ->removeColumn('total_invoice')
            ->removeColumn('opening_balance')
            ->removeColumn('opening_balance_paid')
            ->removeColumn('invoice_received')
            ->removeColumn('state')
            ->removeColumn('country')
            ->removeColumn('city')
            ->removeColumn('type')
            ->removeColumn('id')
            ->removeColumn('is_default')
            ->removeColumn('total_sell_return')
            ->removeColumn('sell_return_paid');
        $reward_enabled = (request()->session()->get('business.enable_rp') == 1) ? true : false;
        $raw = ['due', 'return_due', 'action'];
//        if (!$reward_enabled) {
//            $contacts->removeColumn('total_rp');
//        }
        return $contacts->rawColumns($raw)->toJson();
//                        ->make(false);
    }


    /**
     * Returns the database object for blacklisted customer
     *
     * @return \Illuminate\Http\Response
     */
    private function indexBlacklistedCustomer()
    {
        if (!auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $query = Contact::leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
            ->leftjoin('customer_groups AS cg', 'contacts.customer_group_id', '=', 'cg.id')
            ->where('contacts.business_id', $business_id)
            ->where('contacts.blacked_by_user', '!=', null)
            ->onlyCustomers()
            ->addSelect(['contacts.contact_id', 'contacts.name', 'contacts.email', 'contacts.created_at', 'total_rp', 'cg.name as customer_group', 'city', 'state', 'country', 'landmark', 'mobile', 'contacts.id', 'is_default', 'contacts.blacked_by_user', 'contacts.remark', 'contacts.banned_by_user',
                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
//                        DB::raw("1000 as total_invoice"),
                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                // DB::raw("SUM(IF( t.type = 'sell_return' AND (SELECT transaction_payments.method FROM transaction_payments WHERE transaction_payments.transaction_id=t.id) = 'bank_transfer', final_total, 0)) as total_sell_return"),
                DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
                DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid")
            ])
            ->groupBy('contacts.id');

        $contacts = Datatables::of($query)
            ->editColumn(
                'landmark',
                '{{implode(array_filter([$landmark, $city, $state, $country]), ", ")}}'
            )
            ->addColumn(
                'due',
                '<span class="display_currency contact_due" data-orig-value="{{$total_invoice}}" data-currency_symbol=true data-highlight=true>{{($total_invoice)}}</span>'
//                '<span class="display_currency contact_due" data-orig-value="{{$total_invoice - $invoice_received}}" data-currency_symbol=true data-highlight=true>{{($total_invoice - $invoice_received)}}</span>'
            )
            ->addColumn(
                'return_due',
                '<span class="display_currency return_due" data-orig-value="{{$total_sell_return}}" data-currency_symbol=true data-highlight=false>{{$total_sell_return}}</span>'
//                '<span class="display_currency return_due" data-orig-value="{{$total_sell_return - $sell_return_paid}}" data-currency_symbol=true data-highlight=false>{{$total_sell_return - $sell_return_paid }}</span>'
            )
            ->addColumn(
                'action',
                '<div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                        data-toggle="dropdown" aria-expanded="false">' .
                __("messages.actions") .
                '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                @if(($total_invoice + $opening_balance - $invoice_received - $opening_balance_paid)  > 0)
                    <li><a href="{{action(\'TransactionPaymentController@getPayContactDue\', [$id])}}?type=sell" class="pay_sale_due"><i class="fa fa-money" aria-hidden="true"></i>@lang("contact.pay_due_amount")</a></li>
                @endif
                @if(($total_sell_return - $sell_return_paid)  > 0)
                    <li><a href="{{action(\'TransactionPaymentController@getPayContactDue\', [$id])}}?type=sell_return" class="pay_purchase_due"><i class="fa fa-money" aria-hidden="true"></i>@lang("lang_v1.pay_sell_return_due")</a></li>
                @endif
                @can("customer.view")
                    <li><a href="{{action(\'ContactController@show\', [$id])}}"><i class="fa fa-external-link" aria-hidden="true"></i> @lang("messages.view")</a></li>
                @endcan
                @can("customer.update")
                    <li><a href="{{action(\'ContactController@edit\', [$id])}}?type=blacklisted_customer" class="edit_contact_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a></li>
                    <li><a href="{{action(\'ContactController@banUser\', [$id])}}" class="ban_user_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.ban")</a></li>
                @endcan
                @if(!$is_default)
                @can("customer.delete")
                    <li><a href="{{action(\'ContactController@destroy\', [$id])}}" class="delete_contact_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</a></li>
                @endcan
                @endif </ul></div>'
            )
            ->editColumn('total_rp', '{{$total_rp ?? 0}}')
            ->editColumn('created_at', '{{@format_date($created_at)}}')
            ->removeColumn('total_invoice')
            ->removeColumn('opening_balance')
            ->removeColumn('opening_balance_paid')
            ->removeColumn('invoice_received')
            ->removeColumn('state')
            ->removeColumn('country')
            ->removeColumn('city')
            ->removeColumn('type')
            ->removeColumn('id')
            ->removeColumn('is_default')
            ->removeColumn('total_sell_return')
            ->removeColumn('sell_return_paid');
        $reward_enabled = (request()->session()->get('business.enable_rp') == 1) ? true : false;
        $raw = ['due', 'return_due', 'action'];
//        if (!$reward_enabled) {
//            $contacts->removeColumn('total_rp');
//            $raw = [7, 8, 9];
//        }
        return $contacts->rawColumns($raw)->toJson();
//                        ->make(false);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $type = request()->get('type');

        //Check if subscribed or not
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }

        $customer_groups = CustomerGroup::forDropdown($business_id);
        $memberships = Membership::forDropdown($business_id);
        $bank_brands = BankBrand::forDropdown($business_id);


        $services = Account::where('business_id', $business_id)->where('is_service', 1)->where('name', '!=', 'Safe Kiosk Account')->get();
        $country_codes = CountryCode::forDropdown(false);
        return view('contact.create')
            ->with(compact('types', 'customer_groups', 'country_codes', 'memberships', 'bank_brands', 'type', 'services'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $mobile_list = $request->get('mobile');

        $data = Contact::where('banned_by_user', '!=', null)->get(['mobile']);
        foreach ($data as $item){
            if(!empty($item->mobile)){
                foreach (json_decode($item->mobile) as $old_mobile) {
                    foreach ($mobile_list as $new_mobile) {
                        if ($old_mobile == $new_mobile) {
                            $msg = ' has been banned in the system!';
                            $output = ['success' => false,
                                'msg' => $request->get('mobile') . $msg . ' Please use another contact!'
                            ];
                            return $output;
                        }
                    }
                }
            }
        }

        $data = Contact::where('business_id', $business_id)->get(['mobile', 'blacked_by_user']);
        foreach ($data as $item){
            if(!empty($item->mobile)){
                foreach (json_decode($item->mobile) as $old_mobile){
                    foreach ($mobile_list as $new_mobile){
                        if($old_mobile == $new_mobile){
                            if($item->blacked_by_user){
                                $msg = ' has been blacklisted in the system!';
                            } else
                                $msg = ' already exist in the system!';
                            $output = ['success' => false,
                                'msg' => $new_mobile.$msg.' Please use another contact!'
                            ];
                            return $output;
                        }
                    }
                }
            }
        }

        if(Contact::where('name', $request->get('name'))->count() > 0 && Contact::where('name', $request->get('name'))->get()[0]->banned_by_user){
            $msg = ' has been banned in the system!';
            $output = ['success' => false,
                'msg' => $request->get('name').$msg.' Please use another IC Name!'
            ];
        }
        else if(Contact::where('name', $request->get('name'))->where('business_id', $business_id)->count() && !empty($request->get('name'))){
            if(Contact::where('name', $request->get('name'))->where('business_id', $business_id)->get()[0]->blacked_by_user)
                $msg = ' has been blacklisted in the system!';
            else
                $msg = ' already exist in the system!';
            $output = ['success' => false,
                'msg' => $request->get('name').$msg.' Please use another IC Name!'
            ];
        }
        else if(Contact::where('email', $request->get('email'))->count() > 0 && Contact::where('email', $request->get('email'))->get()[0]->banned_by_user){
            $msg = ' has been banned in the system!';
            $output = ['success' => false,
                'msg' => $request->get('email').$msg.' Please use another email!'
            ];
        }
        else if(Contact::where('email', $request->get('email'))->where('business_id', $business_id)->count() && !empty($request->get('email'))){
            if(Contact::where('email', $request->get('email'))->where('business_id', $business_id)->get()[0]->blacked_by_user)
                $msg = ' has been blacklisted in the system!';
            else
                $msg = ' already exist in the system!';
            $output = ['success' => false,
                'msg' => $request->get('email').$msg.' Please use another email!'
            ];
        }
        else{
            $contacts = Contact::where('business_id', $business_id)->get();
            $new_bank_details = $request->get('bank_details');
            $is_equal = 0;$bank_account_number = 0; $equal_id = 0;
            foreach ($contacts as $contact){
                if($is_equal)
                    break;
                $bank_details = empty($contact->bank_details) ? [] : json_decode($contact->bank_details);
                foreach ($bank_details as $bank_detail){
                    foreach ($new_bank_details as $new_bank_detail){
                        if(!empty($bank_detail->bank_brand_id)){
                            if($new_bank_detail['bank_brand_id'] == $bank_detail->bank_brand_id && $new_bank_detail['account_number'] == $bank_detail->account_number){
                                $is_equal = 1;
                                $equal_id = $contact->id;
                                $bank_account_number = $bank_detail->account_number;
                                break;
                            }
                        }
                    }
                }
            }
            if($is_equal){
                if(Contact::find($equal_id)->blacked_by_user)
                    $msg = ' has been blacklisted in the system!';
                else
                    $msg = ' already exist in the system!';
                $output = ['success' => false,
                    'msg' => $bank_account_number.$msg.' Please use another account number!'
                ];
            }
            else {
                $contacts = Contact::get();
                $new_bank_details = $request->get('bank_details');
                $is_equal = 0;$bank_account_number = 0; $equal_id = 0;
                foreach ($contacts as $contact){
                    if($is_equal)
                        break;
                    $bank_details = empty($contact->bank_details) ? [] : json_decode($contact->bank_details);
                    foreach ($bank_details as $bank_detail){
                        foreach ($new_bank_details as $new_bank_detail){
                            if(!empty($bank_detail->bank_brand_id)){
                                if($new_bank_detail['bank_brand_id'] == $bank_detail->bank_brand_id && $new_bank_detail['account_number'] == $bank_detail->account_number){
                                    $is_equal = 1;
                                    $equal_id = $contact->id;
                                    $bank_account_number = $bank_detail->account_number;
                                    break;
                                }
                            }
                        }
                    }
                }
                if($is_equal && Contact::find($equal_id)->banned_by_user){
                    $msg = ' has been banned in the system!';
                    $output = ['success' => false,
                        'msg' => $bank_account_number.$msg.' Please use another account number!'
                    ];
                }
                else {
//                    try {
                        $business_id = $request->session()->get('user.business_id');

                        if (!$this->moduleUtil->isSubscribed($business_id)) {
                            return $this->moduleUtil->expiredResponse();
                        }

                        $input = $request->only(['supplier_business_name', 'name', 'tax_number', 'pay_term_number', 'pay_term_type', 'landline',
                            'alternate_number', 'city', 'state', 'country', 'landmark', 'customer_group_id', 'membership_id', 'contact_id', 'birthday', 'email', 'remarks', 'country_code_id']);
                        $input['type'] = 'customer';
                        $input['business_id'] = $business_id;
                        $input['created_by'] = $request->session()->get('user.id');
                        $input['no_bonus'] = $request->input('no_bonus') ? true : false;

                        $input['credit_limit'] = $request->input('credit_limit') != '' ? $this->commonUtil->num_uf($request->input('credit_limit')) : null;
                        $bank_details = $request->get('bank_details');
                        $input['bank_details'] = !empty($bank_details) ? json_encode($bank_details) : null;
                        $mobile = $request->get('mobile');
                        $input['mobile'] = !empty($mobile) ? json_encode($mobile) : null;

                        $type = $request->get('type');
                        if($type == 'blacklisted_customer'){
                            $input['remark'] = $request->get('remark');
                            $input['blacked_by_user'] = $request->session()->get('user.first_name').' '.$request->session()->get('user.last_name');
                        }

                        //Check Contact id
                        $count = 0;
                        if (!empty($input['contact_id'])) {
                            $count = Contact::where('business_id', $input['business_id'])
                                            ->where('contact_id', $input['contact_id'])
                                            ->count();
                        }

                        if ($count == 0) {
                            //Update reference count
                            $ref_count = $this->commonUtil->setAndGetReferenceCount('contacts');

                            if (empty($input['contact_id'])) {
                                //Generate reference number
                                $input['contact_id'] = $this->commonUtil->generateReferenceNumber('contacts', $ref_count, $business_id);
                            }

                            $contact = Contact::create($input);

                            ActivityLogger::activity("Created customer, contact ID ".$contact->contact_id);


                            $game_ids = request()->get('game_ids');
                            foreach ($game_ids as $service_id => $game_id){
                                if(!empty($game_id['cur_game_id']) || !empty($game_id['old_game_id'])){
                                    GameId::create([
                                        'service_id' => $service_id,
                                        'contact_id' => $contact->id,
                                        'cur_game_id' => $game_id['cur_game_id'],
                                        'old_game_id' => $game_id['old_game_id']
                                    ]);
                                }
                            }

                            //Add opening balance
                            if (!empty($request->input('opening_balance'))) {
                                $this->transactionUtil->createOpeningBalanceTransaction($business_id, $contact->id, $request->input('opening_balance'));
                            }

                            $output = ['success' => true,
                                        'data' => $contact,
                                        'msg' => __("contact.added_success").'</br>Name: '.$contact->name.'</br>Contact ID:'.$contact->contact_id
                                    ];
                        } else {
                            throw new \Exception("Error Processing Request", 1);
                        }
//                    } catch (\Exception $e) {
//                        \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
//
//                        $output = ['success' => false,
//                                        'msg' =>__("messages.something_went_wrong")
//                                    ];
//                    }
                }
            }
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $contact = Contact::where('contacts.id', $id)
                            ->where('contacts.business_id', $business_id)
                            ->join('transactions AS t', 'contacts.id', '=', 't.contact_id')
                            ->select(
                                DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                                DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
                                DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
                                DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                                DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
                                'contacts.*'
                            )->first();

        $reward_enabled = (request()->session()->get('business.enable_rp') == 1 && in_array($contact->type, ['customer', 'both'])) ? true : false;


        $game_data = GameId::join('accounts', 'accounts.id', 'game_ids.service_id')->where('game_ids.contact_id', $id)
            ->select('accounts.name', 'game_ids.cur_game_id')
            ->get();
//        print_r($game_data);exit;

        return view('contact.show')
             ->with(compact('contact', 'game_data', 'reward_enabled'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('supplier.update') && !auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $contact = Contact::where('business_id', $business_id)->find($id);

            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $customer_type = request()->get('type');
            $types = [];
            if (auth()->user()->can('supplier.create')) {
                $types['supplier'] = __('report.supplier');
            }
            if (auth()->user()->can('customer.create')) {
                $types['customer'] = __('report.customer');
            }
            if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
                $types['both'] = __('lang_v1.both_supplier_customer');
            }

            $customer_groups = CustomerGroup::forDropdown($business_id);

            $memberships = Membership::forDropdown($business_id);
            $bank_brands = BankBrand::forDropdown($business_id);

            $services = Account::where('business_id', $business_id)->where('is_service', 1)->where('name', '!=', 'Safe Kiosk Account')->get();

            $ob_transaction =  Transaction::where('contact_id', $id)
                                            ->where('type', 'opening_balance')
                                            ->first();
            $opening_balance = !empty($ob_transaction->final_total) ? $ob_transaction->final_total : 0;

            //Deduct paid amount from opening balance.
            if (!empty($opening_balance)) {
                $opening_balance_paid = $this->transactionUtil->getTotalAmountPaid($ob_transaction->id);
                if (!empty($opening_balance_paid)) {
                    $opening_balance = $opening_balance - $opening_balance_paid;
                }

                $opening_balance = $this->commonUtil->num_f($ob_transaction->final_total);
            }
            $game_data = GameId::where('contact_id', $id)->get();
            $game_ids = [];
            foreach ($game_data as $game){
                $game_ids[$game->service_id] = $game;
            }
            $bank_details = !empty($contact->bank_details) ? json_decode($contact->bank_details, true) : null;

            $country_codes = CountryCode::forDropdown(false);
            return view('contact.edit')
                ->with(compact('contact', 'bank_details', 'types', 'customer_groups', 'country_codes', 'memberships', 'bank_brands', 'opening_balance', 'services', 'game_ids', 'customer_type'));
        }
    }

    public function blacklist($id)
    {
        if (!auth()->user()->can('supplier.update') && !auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $contact = Contact::where('business_id', $business_id)->find($id);

            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $types = [];
            if (auth()->user()->can('supplier.create')) {
                $types['supplier'] = __('report.supplier');
            }
            if (auth()->user()->can('customer.create')) {
                $types['customer'] = __('report.customer');
            }
            if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
                $types['both'] = __('lang_v1.both_supplier_customer');
            }

            $customer_groups = CustomerGroup::forDropdown($business_id);

            $ob_transaction =  Transaction::where('contact_id', $id)
                ->where('type', 'opening_balance')
                ->first();
            $opening_balance = !empty($ob_transaction->final_total) ? $ob_transaction->final_total : 0;

            //Deduct paid amount from opening balance.
            if (!empty($opening_balance)) {
                $opening_balance_paid = $this->transactionUtil->getTotalAmountPaid($ob_transaction->id);
                if (!empty($opening_balance_paid)) {
                    $opening_balance = $opening_balance - $opening_balance_paid;
                }

                $opening_balance = $this->commonUtil->num_f($ob_transaction->final_total);
            }

            return view('contact.blacklist')
                ->with(compact('contact', 'types', 'customer_groups', 'opening_balance'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('supplier.update') && !auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        $mobile_list = $request->get('mobile');
        $data = Contact::where('business_id', $business_id)->where('id', '!=', $id)->get(['mobile', 'blacked_by_user']);
        foreach ($data as $item){
            if(!empty($item->mobile)){
                foreach (json_decode($item->mobile) as $old_mobile){
                    foreach ($mobile_list as $new_mobile){
                        if($old_mobile == $new_mobile){
                            if($item->blacked_by_user){
                                $msg = ' has been blacklisted in the system!';
                            } else
                                $msg = ' already exist in the system!';
                            $output = ['success' => false,
                                'msg' => $new_mobile.$msg.' Please use another contact!'
                            ];
                            return $output;
                        }
                    }
                }
            }
        }

        $data = Contact::where('banned_by_user', '!=', null)->where('id', '!=', $id)->get(['mobile']);
        foreach ($data as $item){
            if(!empty($item->mobile)){
                foreach (json_decode($item->mobile) as $old_mobile) {
                    foreach ($mobile_list as $new_mobile) {
                        if ($old_mobile == $new_mobile) {
                            $msg = ' has been banned in the system!';
                            $output = ['success' => false,
                                'msg' => $request->get('mobile') . $msg . ' Please use another contact!'
                            ];
                            return $output;
                        }
                    }
                }
            }
        }

        if(Contact::where('name', $request->get('name'))->where('id', '!=', $id)->count() > 0 && Contact::where('name', $request->get('name'))->get()[0]->banned_by_user){
            $msg = ' has been banned in the system!';
            $output = ['success' => false,
                'msg' => $request->get('name').$msg.' Please use another IC Name!'
            ];
        }
        else if(Contact::where('name', $request->get('name'))->where('business_id', $business_id)->where('id', '!=', $id)->count() && !empty($request->get('name'))){
            if(Contact::where('name', $request->get('name'))->where('business_id', $business_id)->where('id', '!=', $id)->get()[0]->blacked_by_user)
                $msg = ' has been blacklisted in the system!';
            else
                $msg = ' already exist in the system!';
            $output = ['success' => false,
                'msg' => $request->get('name').$msg.' Please use another IC Name!'
            ];
        }
        else if(Contact::where('email', $request->get('email'))->where('id', '!=', $id)->count() > 0 && Contact::where('email', $request->get('email'))->where('id', '!=', $id)->get()[0]->banned_by_user){
            $msg = ' has been banned in the system!';
            $output = ['success' => false,
                'msg' => $request->get('email').$msg.' Please use another email!'
            ];
        }
        else if(Contact::where('email', $request->get('email'))->where('business_id', $business_id)->where('id', '!=', $id)->count() && !empty($request->get('email')) > 0){
            if(Contact::where('email', $request->get('email'))->where('business_id', $business_id)->where('id', '!=', $id)->get()[0]->blacked_by_user)
                $msg = ' has been blacklisted in the system!';
            else
                $msg = ' already exist in the system!';
            $output = ['success' => false,
                'msg' => $request->get('email').$msg.' Please use another email!'
            ];
        }
        else{
            $contacts = Contact::where('business_id', $business_id)->where('id', '!=', $id)->get();
            $new_bank_details = $request->get('bank_details');
            $is_equal = 0;$bank_account_number = 0; $equal_id = 0;
            foreach ($contacts as $contact){
                if($is_equal)
                    break;
                $bank_details = empty($contact->bank_details) ? [] : json_decode($contact->bank_details);
                foreach ($bank_details as $bank_detail){
                    foreach ($new_bank_details as $new_bank_detail){
                        if(!empty($bank_detail->bank_brand_id)){
                            if($new_bank_detail['bank_brand_id'] == $bank_detail->bank_brand_id && $new_bank_detail['account_number'] == $bank_detail->account_number){
                                $is_equal = 1;
                                $equal_id = $contact->id;
                                $bank_account_number = $bank_detail->account_number;
                                break;
                            }
                        }
                    }
                }
            }
            if($is_equal){
                if(Contact::find($equal_id)->blacked_by_user)
                    $msg = ' has been blacklisted in the system!';
                else
                    $msg = ' already exist in the system!';
                $output = ['success' => false,
                    'msg' => $bank_account_number.$msg.' Please use another account number!'
                ];
            }
            else {
                $contacts = Contact::where('id', '!=', $id)->get();
                $new_bank_details = $request->get('bank_details');
                $is_equal = 0;
                $bank_account_number = 0;
                $equal_id = 0;
                foreach ($contacts as $contact) {
                    if ($is_equal)
                        break;
                    $bank_details = empty($contact->bank_details) ? [] : json_decode($contact->bank_details);
                    foreach ($bank_details as $bank_detail) {
                        foreach ($new_bank_details as $new_bank_detail) {
                            if (!empty($bank_detail->bank_brand_id)) {
                                if ($new_bank_detail['bank_brand_id'] == $bank_detail->bank_brand_id && $new_bank_detail['account_number'] == $bank_detail->account_number) {
                                    $is_equal = 1;
                                    $equal_id = $contact->id;
                                    $bank_account_number = $bank_detail->account_number;
                                    break;
                                }
                            }
                        }
                    }
                }
                if ($is_equal && Contact::find($equal_id)->banned_by_user) {
                    $msg = ' has been banned in the system!';
                    $output = ['success' => false,
                        'msg' => $bank_account_number . $msg . ' Please use another account number!'
                    ];
                } else {
//                    try {
                        $input = $request->only(['type', 'supplier_business_name', 'name', 'tax_number', 'pay_term_number', 'pay_term_type', 'landline', 'alternate_number',
                            'city', 'state', 'country', 'landmark', 'customer_group_id', 'membership_id', 'contact_id', 'birthday', 'email', 'remarks', 'country_code_id']);

                        $input['credit_limit'] = $request->input('credit_limit') != '' ? $this->commonUtil->num_uf($request->input('credit_limit')) : null;

                        $business_id = $request->session()->get('user.business_id');

                        if (!$this->moduleUtil->isSubscribed($business_id)) {
                            return $this->moduleUtil->expiredResponse();
                        }

                        $count = 0;

                        //Check Contact id
                        if (!empty($input['contact_id'])) {
                            $count = Contact::where('business_id', $business_id)
                                ->where('contact_id', $input['contact_id'])
                                ->where('id', '!=', $id)
                                ->count();
                        }

                        if ($count == 0) {
                            $contact = Contact::where('business_id', $business_id)->findOrFail($id);
                            $activity = 'Customer ID: ' . $contact->contact_id;
                            foreach ($input as $key => $value) {
                                $contact->$key = $value;
                            }
                            $contact->no_bonus = $request->input('no_bonus') ? true : false;
                            $new_bank_details = $request->get('bank_details');
                            if (!empty($contact->bank_details)) {
                                foreach (json_decode($contact->bank_details) as $old_bank_detail) {
                                    foreach ($new_bank_details as $new_bank_detail) {
                                        if ($old_bank_detail->bank_brand_id == $new_bank_detail['bank_brand_id'] && $old_bank_detail->account_number != $new_bank_detail['account_number']) {
                                            $activity .= chr(10) . chr(13) . BankBrand::find($old_bank_detail->bank_brand_id)->name . ': ' . $old_bank_detail->account_number . ' >>>' . $new_bank_detail['account_number'];
                                        }
                                    }
                                }
                            }
                            $contact->bank_details = json_encode($new_bank_details);
                            $contact->mobile = json_encode($request->get('mobile'));
                            $type = $request->get('customer_type');
                            if ($type == 'blacklisted_customer') {
                                $contact->remark = $request->get('remark');
                                $contact->blacked_by_user = $request->session()->get('user.first_name') . ' ' . $request->session()->get('user.last_name');
                            }

                            $contact->save();
                            $admins = $this->moduleUtil->get_admins($business_id);
                            $user_id = request()->session()->get('user.id');
                            \Notification::send($admins, new EditCustomerNotification(['changed_by' => $user_id, 'contact_id' => $contact->contact_id]));
                            $game_ids = $request->get('game_ids');
                            foreach ($game_ids as $service_id => $game_id) {
                                $game_name = Account::find($service_id)->name;
                                $game_cnt = GameId::where('service_id', $service_id)->where('contact_id', $id)->count();
                                if ($game_cnt == 0) {
                                    if(!empty($game_id['cur_game_id']) || !empty($game_id['old_game_id'])){
                                        GameId::create([
                                            'service_id' => $service_id,
                                            'contact_id' => $id,
                                            'cur_game_id' => $game_id['cur_game_id'],
                                            'old_game_id' => $game_id['old_game_id']
                                        ]);
                                        if(!$game_id['cur_game_id'])
                                            $activity .= chr(10) . chr(13) . $game_name . ': >>>' . $game_id['cur_game_id'];
                                        if(!$game_id['old_game_id'])
                                            $activity .= chr(10) . chr(13) . 'Old - '.$game_name . ': >>>' . $game_id['old_game_id'];
                                    }
                                } else {
                                    $row = GameId::where('service_id', $service_id)->where('contact_id', $id)->get()->first();
                                    if ( $row->cur_game_id != $game_id['cur_game_id']) {
                                        GameId::where('service_id', $service_id)->where('contact_id', $id)->update(['cur_game_id' => $game_id['cur_game_id']]);
                                        $activity .= chr(10) . chr(13) . $game_name . ': ' . $row->cur_game_id . ' >>>' . $game_id['cur_game_id'];
                                    }
                                    if ( $row->old_game_id != $game_id['old_game_id']) {
                                        GameId::where('service_id', $service_id)->where('contact_id', $id)->update(['old_game_id' => $game_id['old_game_id']]);
                                        $activity .= chr(10) . chr(13) . 'Old - '.$game_name . ': ' . $row->old_game_id . ' >>>' . $game_id['old_game_id'];
                                    }
                                }
                            }
                            ActivityLogger::activity($activity);

                            //Get opening balance if exists
                            $ob_transaction = Transaction::where('contact_id', $id)
                                ->where('type', 'opening_balance')
                                ->first();

                            if (!empty($ob_transaction)) {
                                $amount = $this->commonUtil->num_uf($request->input('opening_balance'));
                                $opening_balance_paid = $this->transactionUtil->getTotalAmountPaid($ob_transaction->id);
                                if (!empty($opening_balance_paid)) {
                                    $amount += $opening_balance_paid;
                                }

                                $ob_transaction->final_total = $amount;
                                $ob_transaction->save();
                                //Update opening balance payment status
                                $this->transactionUtil->updatePaymentStatus($ob_transaction->id, $ob_transaction->final_total);
                            } else {
                                //Add opening balance
                                if (!empty($request->input('opening_balance'))) {
                                    $this->transactionUtil->createOpeningBalanceTransaction($business_id, $contact->id, $request->input('opening_balance'));
                                }
                            }

                            $output = ['success' => true,
                                'msg' => __("contact.updated_success")
                            ];
                        } else {
                            throw new \Exception("Error Processing Request", 1);
                        }
//                    } catch (\Exception $e) {
//                        \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
//
//                        $output = ['success' => false,
//                            'msg' => __("messages.something_went_wrong")
//                        ];
//                    }
                }
            }
        }
        return $output;
    }

    public function getBankDetailHtml(){

        if(request()->ajax()) {
            $account_index = request()->get('account_index');
            $business_id = request()->session()->get('user.business_id');
            $bank_brands = BankBrand::forDropdown($business_id);
            $html = view('contact.bank_detail')->with(['bank_brands' => $bank_brands, 'account_index' => $account_index])->render();
            $output = ['success' => true, 'html' => $html];
            return $output;
        }
    }

    public function updateBlackList($id)
    {
        if (!auth()->user()->can('supplier.update') && !auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                $contact = Contact::where('business_id', $business_id)->findOrFail($id);
                $contact->remark = request()->get('remark');
                $contact->blacked_by_user = request()->session()->get('user.first_name').' '.request()->session()->get('user.last_name');
                $contact->save();

                $output = ['success' => true,
                    'msg' => __("contact.updated_success")
                ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = ['success' => false,
                    'msg' => __("messages.something_went_wrong")
                ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('supplier.delete') && !auth()->user()->can('customer.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                //Check if any transaction related to this contact exists
                $count = Transaction::where('business_id', $business_id)
                                    ->where('contact_id', $id)
                                    ->count();
                if ($count == 0) {
                    $contact = Contact::where('business_id', $business_id)->findOrFail($id);
                    if (!$contact->is_default) {
                        $contact->delete();
                    }
                    $output = ['success' => true,
                                'msg' => __("contact.deleted_success")
                                ];
                } else {
                    $output = ['success' => false,
                                'msg' => __("lang_v1.you_cannot_delete_this_contact")
                                ];
                }
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function banUser($id)
    {
        if (!auth()->user()->can('supplier.delete') && !auth()->user()->can('customer.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                $contact = Contact::where('business_id', $business_id)->findOrFail($id);
                $contact->banned_by_user =request()->session()->get('user.first_name').' '.request()->session()->get('user.last_name');
                $contact->save();
                $output = ['success' => true,
                    'msg' => __("contact.updated_success")
                ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = ['success' => false,
                    'msg' => __("messages.something_went_wrong")
                ];
            }

            return $output;
        }
    }

    /**
     * Retrieves list of customers, if filter is passed then filter it accordingly.
     *
     * @param  string  $q
     * @return JSON
     */
    public function getCustomers()
    {
        if (request()->ajax()) {
            $term = request()->input('q', '');

            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

//            $contacts = Contact::join('game_ids', 'game_ids.contact_id', 'contacts.id');
            if (empty($term)) {
                $contacts = Contact::where('business_id', 0);
            } else{
                $contacts = Contact::where('business_id', $business_id);

                $selected_contacts = User::isSelectedContacts($user_id);
                if ($selected_contacts) {
                    $contacts->join('user_contact_access AS uca', 'contacts.id', 'uca.contact_id')
                    ->where('uca.user_id', $user_id);
                }
                $contacts->where('blacked_by_user', null);
                $contacts->where('is_default', 0);
                $contacts->where(function ($query) use ($term) {
                    $query->where('name', 'like', '%' . $term .'%')
                            ->orWhere('supplier_business_name', 'like', '%' . $term .'%')
                            ->orWhere('mobile', 'like', '%' . $term .'%')
                            ->orWhere('contacts.contact_id', 'like', '%' . $term .'%');
                });
            }
            // else {
            //     $contacts->where('business_id', 0);
            // }

            $contacts->select(
                'contacts.id' ,
                'contact_id',
                DB::raw("IF(contact_id IS NULL OR contact_id='', name, CONCAT('(', contact_id, ') ', name)) AS text"),
                'mobile',
                'landmark',
                'city',
                'state',
                'pay_term_number',
                'pay_term_type'
            )
                    ->onlyCustomers();

            if (request()->session()->get('business.enable_rp') == 1) {
                $contacts->addSelect('total_rp');
            }
            $result = $contacts->get();
            $contacts_final = [];
            foreach ($result as $item){
                $contacts_final[] = $item;
            }

            $contacts = Contact::join('game_ids', 'game_ids.contact_id', 'contacts.id');
            $contacts->join('accounts', 'game_ids.service_id', 'accounts.id');
            $contacts->where('contacts.business_id', $business_id);

            $selected_contacts = User::isSelectedContacts($user_id);
            if ($selected_contacts) {
                $contacts->join('user_contact_access AS uca', 'contacts.id', 'uca.contact_id')
                    ->where('uca.user_id', $user_id);
            }
            $contacts->where('blacked_by_user', null);
            $contacts->where('is_default', 0);
            $contacts->where(function ($query) use ($term) {
                $query->where('game_ids.cur_game_id', 'like', '%' . $term .'%');
            });
            $contacts->select(
                'contacts.id' ,
                'contacts.contact_id',
                DB::raw("IF(contacts.contact_id IS NULL OR contacts.contact_id='', contacts.name, CONCAT('(', contacts.contact_id, ') ', contacts.name)) AS text"),
                DB::raw("CONCAT( accounts.name, ': ', game_ids.cur_game_id) AS game_text"),
                'mobile',
                'landmark',
                'city',
                'state',
                'pay_term_number',
                'pay_term_type'
            )
            ->onlyCustomers();

            $result = $contacts->get();
            foreach ($result as $item){
                $contacts_final[] = $item;
            }

            return json_encode($contacts_final);
        }
    }

    public function getCustomersWithId()
    {
        if (request()->ajax()) {
            $term = request()->input('q', '');

            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

//            $contacts = Contact::join('game_ids', 'game_ids.contact_id', 'contacts.id');
            if (empty($term)) {
                $contacts = Contact::where('business_id', 0);
            } else{
                $contacts = Contact::where('business_id', $business_id);

                $selected_contacts = User::isSelectedContacts($user_id);
                if ($selected_contacts) {
                    $contacts->join('user_contact_access AS uca', 'contacts.id', 'uca.contact_id')
                        ->where('uca.user_id', $user_id);
                }
                $contacts->where('blacked_by_user', null);
                $contacts->where('is_default', 0);
                $contacts->where(function ($query) use ($term) {
                    $query->where('name', 'like', '%' . $term .'%')
                        ->orWhere('supplier_business_name', 'like', '%' . $term .'%')
                        ->orWhere('mobile', 'like', '%' . $term .'%')
                        ->orWhere('contacts.contact_id', 'like', '%' . $term .'%');
                });
            }
            // else {
            //     $contacts->where('business_id', 0);
            // }

            $contacts->select(
                'contacts.id' ,
                'contact_id',
                DB::raw("IF(contact_id IS NULL OR contact_id='', name, contact_id) AS text"),
                'mobile',
                'landmark',
                'city',
                'state',
                'pay_term_number',
                'pay_term_type'
            )
                ->onlyCustomers();

            if (request()->session()->get('business.enable_rp') == 1) {
                $contacts->addSelect('total_rp');
            }
            $result = $contacts->get();
            $contacts_final = [];
            foreach ($result as $item){
                $contacts_final[] = $item;
            }

            $contacts = Contact::join('game_ids', 'game_ids.contact_id', 'contacts.id');
            $contacts->join('accounts', 'game_ids.service_id', 'accounts.id');
            $contacts->where('contacts.business_id', $business_id);

            $selected_contacts = User::isSelectedContacts($user_id);
            if ($selected_contacts) {
                $contacts->join('user_contact_access AS uca', 'contacts.id', 'uca.contact_id')
                    ->where('uca.user_id', $user_id);
            }
            $contacts->where('blacked_by_user', null);
            $contacts->where('is_default', 0);
            $contacts->where(function ($query) use ($term) {
                $query->where('game_ids.cur_game_id', 'like', '%' . $term .'%');
            });
            $contacts->select(
                'contacts.id' ,
                'contacts.contact_id',
                DB::raw("IF(contacts.contact_id IS NULL OR contacts.contact_id='', contacts.name, CONCAT('(', contacts.contact_id, ') ', contacts.name)) AS text"),
                DB::raw("CONCAT( accounts.name, ': ', game_ids.cur_game_id) AS game_text"),
                'mobile',
                'landmark',
                'city',
                'state',
                'pay_term_number',
                'pay_term_type'
            )
                ->onlyCustomers();

            $result = $contacts->get();
            foreach ($result as $item){
                $contacts_final[] = $item;
            }

            return json_encode($contacts_final);
        }
    }

    /**
     * Checks if the given contact id already exist for the current business.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkContactId(Request $request)
    {
        $contact_id = $request->input('contact_id');

        $valid = 'true';
        if (!empty($contact_id)) {
            $business_id = $request->session()->get('user.business_id');
            $hidden_id = $request->input('hidden_id');

            $query = Contact::where('business_id', $business_id)
                            ->where('contact_id', $contact_id);
            if (!empty($hidden_id)) {
                $query->where('id', '!=', $hidden_id);
            }
            $count = $query->count();
            if ($count > 0) {
                $valid = 'false';
            }
        }
        echo $valid;
        exit;
    }

    /**
     * Shows import option for contacts
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function getImportContacts()
    {
        if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create')) {
            abort(403, 'Unauthorized action.');
        }

        $zip_loaded = extension_loaded('zip') ? true : false;

        //Check if zip extension it loaded or not.
        if ($zip_loaded === false) {
            $output = ['success' => 0,
                            'msg' => 'Please install/enable PHP Zip archive for import'
                        ];

            return view('contact.import')
                ->with('notification', $output);
        } else {
            return view('contact.import');
        }
    }

    /**
     * Imports contacts
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function postImportContacts(Request $request)
    {
        if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            //Set maximum php execution time
            ini_set('max_execution_time', 0);

            if ($request->hasFile('contacts_csv')) {
                $file = $request->file('contacts_csv');
                $imported_data = Excel::load($file->getRealPath())
                                ->noHeading()
                                ->skipRows(1)
                                ->get()
                                ->toArray();
                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');

                $formated_data = [];

                $is_valid = true;
                $error_msg = '';
                
                DB::beginTransaction();
                foreach ($imported_data as $key => $value) {
                    //Check if 21 no. of columns exists
                    if (count($value) != 21) {
                        $is_valid =  false;
                        $error_msg = "Number of columns mismatch";
                        break;
                    }

                    $row_no = $key + 1;
                    $contact_array = [];

                    //Check contact type
                    $contact_type = '';
                    if (!empty($value[0])) {
                        $contact_type = strtolower(trim($value[0]));
                        if (in_array($contact_type, ['supplier', 'customer', 'both'])) {
                            $contact_array['type'] = $contact_type;
                        } else {
                            $is_valid =  false;
                            $error_msg = "Invalid contact type in row no. $row_no";
                            break;
                        }
                    } else {
                        $is_valid =  false;
                        $error_msg = "Contact type is required in row no. $row_no";
                        break;
                    }

                    //Check contact name
                    if (!empty($value[1])) {
                        $contact_array['name'] = $value[1];
                    } else {
                        $is_valid =  false;
                        $error_msg = "Contact name is required in row no. $row_no";
                        break;
                    }

                    //Check supplier fields
                    if (in_array($contact_type, ['supplier', 'both'])) {
                        //Check business name
                        if (!empty(trim($value[2]))) {
                            $contact_array['supplier_business_name'] = $value[2];
                        } else {
                            $is_valid =  false;
                            $error_msg = "Business name is required in row no. $row_no";
                            break;
                        }

                        //Check pay term
                        if (trim($value[6]) != '') {
                            $contact_array['pay_term_number'] = trim($value[6]);
                        } else {
                            $is_valid =  false;
                            $error_msg = "Pay term is required in row no. $row_no";
                            break;
                        }

                        //Check pay period
                        $pay_term_type = strtolower(trim($value[7]));
                        if (in_array($pay_term_type, ['days', 'months'])) {
                            $contact_array['pay_term_type'] = $pay_term_type;
                        } else {
                            $is_valid =  false;
                            $error_msg = "Pay term period is required in row no. $row_no";
                            break;
                        }
                    }

                    //Check contact ID
                    if (!empty(trim($value[3]))) {
                        $count = Contact::where('business_id', $business_id)
                                    ->where('contact_id', $value[3])
                                    ->count();
                

                        if ($count == 0) {
                            $contact_array['contact_id'] = $value[3];
                        } else {
                            $is_valid =  false;
                            $error_msg = "Contact ID already exists in row no. $row_no";
                            break;
                        }
                    }

                    //Tax number
                    if (!empty(trim($value[4]))) {
                        $contact_array['tax_number'] = $value[4];
                    }

                    //Check opening balance
                    if (!empty(trim($value[5])) && $value[5] != 0) {
                        $contact_array['opening_balance'] = trim($value[5]);
                    }

                    //Check credit limit
                    if (trim($value[8]) != '' && in_array($contact_type, ['customer', 'both'])) {
                        $contact_array['credit_limit'] = trim($value[8]);
                    }

                    //Check email
                    if (!empty(trim($value[9]))) {
                        if (filter_var(trim($value[9]), FILTER_VALIDATE_EMAIL)) {
                            $contact_array['email'] = $value[9];
                        } else {
                            $is_valid =  false;
                            $error_msg = "Invalid email id in row no. $row_no";
                            break;
                        }
                    }

                    //Mobile number
                    if (!empty(trim($value[10]))) {
                        $contact_array['mobile'] = $value[10];
                    } else {
                        $is_valid =  false;
                        $error_msg = "Mobile number is required in row no. $row_no";
                        break;
                    }

                    //Alt contact number
                    $contact_array['alternate_number'] = $value[11];

                    //Landline
                    $contact_array['landline'] = $value[12];

                    //City
                    $contact_array['city'] = $value[13];

                    //State
                    $contact_array['state'] = $value[14];

                    //Country
                    $contact_array['country'] = $value[15];

                    //Landmark
                    $contact_array['landmark'] = $value[16];

                    //Cust fields
                    $contact_array['custom_field1'] = $value[17];
                    $contact_array['custom_field2'] = $value[18];
                    $contact_array['custom_field3'] = $value[19];
                    $contact_array['custom_field4'] = $value[20];

                    $formated_data[] = $contact_array;
                }
                if (!$is_valid) {
                    throw new \Exception($error_msg);
                }

                if (!empty($formated_data)) {
                    foreach ($formated_data as $contact_data) {
                        $ref_count = $this->transactionUtil->setAndGetReferenceCount('contacts');
                        //Set contact id if empty
                        if (empty($contact_data['contact_id'])) {
                            $contact_data['contact_id'] = $this->commonUtil->generateReferenceNumber('contacts', $ref_count);
                        }

                        $opening_balance = 0;
                        if (isset($contact_data['opening_balance'])) {
                            $opening_balance = $contact_data['opening_balance'];
                            unset($contact_data['opening_balance']);
                        }

                        $contact_data['business_id'] = $business_id;
                        $contact_data['created_by'] = $user_id;

                        $contact = Contact::create($contact_data);

                        if (!empty($opening_balance)) {
                            $this->transactionUtil->createOpeningBalanceTransaction($business_id, $contact->id, $opening_balance);
                        }
                    }
                }

                $output = ['success' => 1,
                            'msg' => __('product.file_imported_successfully')
                        ];

                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => $e->getMessage()
                        ];
            return redirect()->route('contacts.import')->with('notification', $output);
        }

        return redirect()->action('ContactController@index', ['type' => 'supplier'])->with('status', $output);
    }

    public function getBankDetail()
    {
        if(request()->ajax()){
            $user_id = request()->get('user_id');
            $user = Contact::find($user_id);
            $bank_details = json_decode($user->bank_details);
            if(empty($bank_details))
                $bank_details = [];
            foreach($bank_details as $key => $bank_detail){
                $bank_details[$key]->bank_name = BankBrand::find($bank_detail->bank_brand_id)->name;
            }
            $bank_account_detail = view('service.bank_account_detail')
                ->with(['bank_details' => $bank_details])->render();
            return json_encode(['bank_account_detail'=> $bank_account_detail]);
        }
    }

    /**
     * Shows ledger for contacts
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function getLedger()
    {
        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $contact_id = request()->input('contact_id');
        $transaction_types = explode(',', request()->input('transaction_types'));
//        $show_payments = request()->input('show_payments') == 'true' ? true : false;

        //Get transactions
        $query1 = Transaction::where('transactions.contact_id', $contact_id)
                            ->where('transactions.business_id', $business_id)
                            ->where('status', '!=', 'draft')
                            ->whereIn('type', $transaction_types)
                            ->with(['location']);

        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start = request()->start_date;
            $end =  request()->end_date;
            $query1->whereDate('transactions.transaction_date', '>=', $start)
                        ->whereDate('transactions.transaction_date', '<=', $end);
        }

        $transactions = $query1->get();

        $ledger = [];
//        foreach ($transactions as $transaction) {
//            $ledger[] = [
//                'date' => $transaction->transaction_date,
//                'ref_no' => in_array($transaction->type, ['sell', 'sell_return']) ? $transaction->invoice_no : $transaction->ref_no,
//                'type' => $this->transactionTypes[$transaction->type],
//                'location' => $transaction->location->name,
//                'payment_status' =>  __('lang_v1.' . $transaction->payment_status),
//                'total' => $transaction->final_total,
//                'payment_method' => '',
//                'debit' => '',
//                'credit' => '',
//                'others' => $transaction->additional_notes
//            ];
//        }

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

        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start = request()->start_date;
            $end =  request()->end_date;
            $query1->whereDate('transactions.transaction_date', '>=', $start)
                        ->whereDate('transactions.transaction_date', '<=', $end);

            $query2->whereDate('paid_on', '>=', $start)
                    ->whereDate('paid_on', '<=', $end);
        }

        $payments = $query2->select('transaction_payments.*', 'bl.name as location_name', 't.type as transaction_type', 't.ref_no', 't.invoice_no')->get();
//        $total_deposit = $query2->where('t.type', 'sell')->where('transaction_payments.method', '!=', 'service_transfer')->where('transaction_payments.method','!=', 'bonus')->sum('transaction_payments.amount');
        $paymentTypes = $this->transactionUtil->payment_types();
        foreach ($payments as $payment) {
            $ref_no = in_array($payment->transaction_type, ['sell', 'sell_return']) ?  $payment->invoice_no :  $payment->ref_no;
            $ledger[] = [
                'date' => $payment->paid_on,
                'ref_no' => $payment->payment_ref_no,
                'type' => $this->transactionTypes['payment'],
                'location' => $payment->location_name,
                'payment_method' => !empty($paymentTypes[$payment->method]) ? $paymentTypes[$payment->method] : '',
//                'debit' => in_array($payment->transaction_type, ['purchase', 'sell_return']) || ($payment->transaction_type == 'sell' && $payment->method =='other') ? $payment->amount : '',
//                'credit' => in_array($payment->transaction_type, ['sell', 'purchase_return', 'opening_balance']) && $payment->method !='other' ? $payment->amount : '',
                'debit' => ($payment->card_type == 'debit' && $payment->method != 'service_transfer') ? $payment->amount : '',
                'credit' => ($payment->card_type == 'credit' && $payment->method == 'bank_transfer') ? $payment->amount : '',
                'bonus' => ($payment->card_type == 'credit' && ($payment->method == 'basic_bonus' || $payment->method == 'free_credit') ) ? $payment->amount : '',
                'service_debit' => ($payment->card_type == 'debit' && $payment->method == 'service_transfer') ? $payment->amount : '',
                'service_credit' => ($payment->card_type == 'credit' && $payment->method == 'service_transfer' ) ? $payment->amount : '',
                'others' => $payment->note . '<small>' . __('account.payment_for') . ': ' . $ref_no . '</small>'
            ];
        }
//        print_r($ledger);exit;

        //Sort by date
        if (!empty($ledger)) {
            usort($ledger, function ($a, $b) {
                $t1 = strtotime($a['date']);
                $t2 = strtotime($b['date']);
                return $t2 - $t1;
            });
        }
        return view('contact.ledger')
             ->with(compact('ledger'));
    }

    public function postCustomersApi(Request $request)
    {
        try {
            $api_token = $request->header('API-TOKEN');

            $api_settings = $this->moduleUtil->getApiSettings($api_token);

            $business = Business::find($api_settings->business_id);

            $data = $request->only(['name', 'email']);

            $customer = Contact::where('business_id', $api_settings->business_id)
                                ->where('email', $data['email'])
                                ->whereIn('type', ['customer', 'both'])
                                ->first();

            if (empty($customer)) {
                $data['type'] = 'customer';
                $data['business_id'] = $api_settings->business_id;
                $data['created_by'] = $business->owner_id;
                $data['mobile'] = 0;

                $ref_count = $this->commonUtil->setAndGetReferenceCount('contacts', $business->id);

                $data['contact_id'] = $this->commonUtil->generateReferenceNumber('contacts', $ref_count, $business->id);

                $customer = Contact::create($data);
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            return $this->respondWentWrong($e);
        }

        return $this->respond($customer);
    }
}
