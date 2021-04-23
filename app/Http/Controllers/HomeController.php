<?php

namespace App\Http\Controllers;

use App\Account;
use App\Business;
use App\BusinessLocation;

use App\Contact;
use App\Currency;
use App\Transaction;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;

use App\Utils\TransactionUtil;
use App\VariationLocationDetails;


use Datatables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\TransactionPayment;

class HomeController extends Controller
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
        if (request()->ajax()) {
            $business_util = new BusinessUtil;
            $business_id = request()->get('business_id');
            $user = Auth::user();
            $session_data = ['id' => $user->id,
                'surname' => $user->surname,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'business_id' => $business_id,
                'language' => $user->language,
            ];
            $business = Business::findOrFail($business_id);

            $currency = $business->currency;
            $currency_data = ['id' => $currency->id,
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'thousand_separator' => $currency->thousand_separator,
                'decimal_separator' => $currency->decimal_separator
            ];
            $session = request()->session();

            $session->put('user', $session_data);
            $session->put('business', $business);
            $session->put('currency', $currency_data);
            if($user->hasRole('Superadmin')){
                $business_list = Business::get()->pluck('name','id');
                $session->put('business_list', $business_list);
            }

            //set current financial year to session
            $financial_year = $business_util->getCurrentFinancialYear($business->id);
            $session->put('financial_year', $financial_year);
            echo json_encode(array('flag' => 1, 'msg' => 'success'));
            return;
        }
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

//        $currency = Currency::where('id', request()->session()->get('business.currency_id'))->first();
        
        //Chart for sells last 30 days
//        $sells_last_30_days = $this->transactionUtil->getSellsLast30Days($business_id);
//        $labels = [];
//        $all_sell_values = [];
//        $dates = [];
//        for ($i = 29; $i >= 0; $i--) {
//            $date = \Carbon::now()->subDays($i)->format('Y-m-d');
//            $dates[] = $date;
//
//            $labels[] = date('j M Y', strtotime($date));
//
//            if (!empty($sells_last_30_days[$date])) {
//                $all_sell_values[] = $sells_last_30_days[$date];
//            } else {
//                $all_sell_values[] = 0;
//            }
//        }

        //Get Dashboard widgets from module
        $module_widgets = $this->moduleUtil->getModuleData('dashboard_widget');

        $widgets = [];

        foreach ($module_widgets as $widget_array) {
            if (!empty($widget_array['position'])) {
                $widgets[$widget_array['position']][] = $widget_array['widget'];
            }
        }


        $bank_accounts_sql = Account::where('name', '!=', "Bonus Account")
            ->where('is_service', 0)
            ->where('is_closed', 0)
            ->where('business_id', $business_id)
            ->select(['name']);
        $bank_accounts = $bank_accounts_sql->get();
        $banks = [];
        foreach ($bank_accounts as $item) {
            $banks[] = $item->name;
        }


        $total_bank_sql = Account::leftjoin('account_transactions as AT', function ($join) {
            $join->on('AT.account_id', '=', 'accounts.id');
            $join->whereNull('AT.deleted_at');
        })
            ->where('is_service', 0)
            ->where('is_safe', 0)
            ->where('name', '!=', 'Bonus Account')
            ->where('business_id', $business_id)
            ->select(['name', 'account_number', 'accounts.note', 'accounts.id',
                'is_closed', DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")
                , DB::raw("SUM( IF(AT.type='credit', amount, 0) ) as total_deposit")
                , DB::raw("SUM( IF(AT.type='debit', amount, 0) ) as total_withdraw")]);

        $total_bank_sql->where(function ($q) {
            $q->where('account_type', '!=', 'capital');
            $q->orWhereNull('account_type');
        });

        $total_bank = $total_bank_sql->get()[0];

        $service_accounts_sql = Account::where('name', '!=', "Safe Kiosk Account")
            ->where('is_service', 1)
            ->where('is_closed', 0)
            ->where('business_id', $business_id)
            ->select(['name']);
        $service_accounts = $service_accounts_sql->get();
        $services = [];
        foreach ($service_accounts as $item) {
            $services[] = $item->name;
        }

//        return view('home.index', compact('date_filters', 'sells_chart_1', 'sells_chart_2', 'widgets', 'bank_accounts', 'service_accounts', 'total_bank'));
        return view('home.index', compact('date_filters', 'widgets', 'banks', 'services', 'total_bank'));
    }

    /**
     * Retrieves purchase and sell details for a given time period.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTotals()
    {
        if (request()->ajax()) {
            $start = request()->start;
            $end = request()->end;
            $business_id = request()->session()->get('user.business_id');

            $purchase_details = $this->transactionUtil->getPurchaseTotals($business_id, $start, $end);

            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                ->leftJoin('accounts', 'tp.account_id', '=', 'accounts.id')
                ->join(
                    'business_locations AS bl',
                    'transactions.location_id',
                    '=',
                    'bl.id'
                )
                ->leftJoin(
                    'transactions AS SR',
                    'transactions.id',
                    '=',
                    'SR.return_parent_id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where(function ($q) {
                    $q->where('transactions.payment_status', '!=', 'cancelled');
                    $q->orWhere('transactions.payment_status', '=', null);
                })
                ->where('accounts.is_service', 0)
                ->whereBetween(\Illuminate\Support\Facades\DB::raw('date(transactions.transaction_date)'), [$start, $end]);

            $withdraws = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                ->leftJoin('accounts', 'tp.account_id', '=', 'accounts.id')
                ->join(
                    'business_locations AS bl',
                    'transactions.location_id',
                    '=',
                    'bl.id'
                )
                ->leftJoin(
                    'transactions AS SR',
                    'transactions.id',
                    '=',
                    'SR.return_parent_id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell_return')
                ->where('transactions.status', 'final')
                ->where('accounts.is_service', 0)
                ->where(function ($q) {
                    $q->where('transactions.payment_status', '!=', 'cancelled');
                    $q->orWhere('transactions.payment_status', '=', null);
                })
                ->whereBetween(\Illuminate\Support\Facades\DB::raw('date(transactions.transaction_date)'), [$start, $end]);

            $sells->groupBy('transactions.id');
            $withdraws->groupBy('transactions.id');
            $deposit_count = count($sells->select('transactions.id')->get());
            $withdraw_count = count($withdraws->select('transactions.id')->get());


            $output = $purchase_details;

            $query = Contact::join('customer_groups AS g', 'g.id', 'contacts.customer_group_id')
                ->where('contacts.business_id', $business_id)->where('type', 'customer')
                ->whereBetween(\Illuminate\Support\Facades\DB::raw('date(contacts.created_at)'), [$start, $end])
                ->select(DB::raw('COUNT(contacts.id) as cnt'), 'g.name')
                ->groupBy('contacts.customer_group_id');

            $data = TransactionPayment::where('transaction_payments.business_id', $business_id)
                ->leftJoin('transactions as T', 'T.id', '=', 'transaction_payments.transaction_id')
                ->where(function ($q) {
                    $q->where('T.payment_status', '!=', 'cancelled');
                    $q->orWhere('T.payment_status', '=', null);
                })
                ->whereBetween(\Illuminate\Support\Facades\DB::raw('date(transaction_payments.created_at)'), [$start, $end])
                ->select(DB::raw("SUM(IF(method='free_credit', amount, 0)) as free_credit"), DB::raw("SUM(IF(method='basic_bonus', amount, 0)) as basic_bonus"))->get()[0];

            $bank_accounts_sql = Account::leftjoin('account_transactions as AT', function ($join) {
                $join->on('AT.account_id', '=', 'accounts.id');
                $join->whereNull('AT.deleted_at');
            })
                ->leftjoin( 'transactions as T',
                    'AT.transaction_id',
                    '=',
                    'T.id')
                ->where('accounts.is_service', 0)
                ->where('accounts.name', '!=', 'Bonus Account')
                ->where('accounts.business_id', $business_id)
                ->whereDate('AT.operation_date', '>=', $start)
               ->whereDate('AT.operation_date', '<=', $end)
                ->where(function ($q) {
                    $q->where('T.payment_status', '!=', 'cancelled');
                    $q->orWhere('T.payment_status', '=', null);
                })
                ->select(['accounts.name', 'accounts.account_number', 'accounts.note', 'accounts.id as account_id',
                    'accounts.is_closed', DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")
//                    , DB::raw("SUM( IF( AT.type='credit' AND (AT.sub_type IS NULL OR AT.`sub_type` != 'fund_transfer'), AT.amount, 0) ) as total_deposit")
                    , DB::raw("SUM( IF( AT.type='credit' AND (AT.sub_type IS NULL OR AT.`sub_type` != 'fund_transfer'), AT.amount, 0) ) as total_deposit")
                    , DB::raw("SUM( IF( AT.type='debit' AND (AT.sub_type IS NULL OR AT.`sub_type` != 'fund_transfer'), AT.amount, 0) ) as total_withdraw")]);

            $bank_accounts = $bank_accounts_sql->get();
            $output['total_deposit'] = $bank_accounts[0]->total_deposit;
            $output['total_withdraw'] = $bank_accounts[0]->total_withdraw;
            $output['deposit_count'] = $deposit_count;
            $output['withdraw_count'] = $withdraw_count;
//            $output['total_sell'] = $total_sell_inc_tax - $total_sell_return_inc_tax;
//            $output['total_deposit'] = $total_sell_inc_tax;
            $output['total_bonus'] = $data['basic_bonus'];
            $output['total_profit'] = $data['free_credit'];;
            $output['registration_arr'] = $query->get();

//            $output['invoice_due'] = $sell_details['invoice_due'];

            $bank_accounts_sql = Account::leftjoin('account_transactions as AT', function ($join) {
                $join->on('AT.account_id', '=', 'accounts.id');
                $join->whereNull('AT.deleted_at');
            })
                ->leftjoin( 'transactions as T',
                    'AT.transaction_id',
                    '=',
                    'T.id')
                ->where('accounts.is_service', 0)
                ->where('accounts.name', '!=', 'Bonus Account')
                ->where('accounts.is_closed', 0)
                ->where('accounts.business_id', $business_id)
                ->where(function ($q) {
                    $q->where('T.payment_status', '!=', 'cancelled');
                    $q->orWhere('T.payment_status', '=', null);
                })
//                ->whereBetween(\Illuminate\Support\Facades\DB::raw('date(AT.operation_date)'), [$start, $end])
                ->select(['accounts.name', 'accounts.account_number', 'accounts.note', 'accounts.id',
                    'accounts.is_closed', DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")
                    , DB::raw("SUM( IF(AT.type='credit' AND DATE_FORMAT(operation_date, '%Y-%m-%d') >='".$start."' AND DATE_FORMAT(operation_date, '%Y-%m-%d') <='".$end."', amount, 0) ) as total_deposit")
                    , DB::raw("SUM( IF(AT.type='debit'  AND DATE_FORMAT(operation_date, '%Y-%m-%d') >='".$start."' AND DATE_FORMAT(operation_date, '%Y-%m-%d') <='".$end."', amount, 0) ) as total_withdraw")])
                ->groupBy('accounts.id');

            $bank_accounts_sql->where(function ($q) {
                $q->where('account_type', '!=', 'capital');
                $q->orWhereNull('account_type');
            });
            $bank_accounts = $bank_accounts_sql->get();
            foreach ($bank_accounts as $item){
                $banks["balance"][] = empty($item["balance"]) ? 0 : $item["balance"];
                $banks["deposit"][] = empty($item["total_deposit"]) ? 0 : $item["total_deposit"];
                $banks["withdraw"][] = empty($item["total_withdraw"]) ? 0 : $item["total_withdraw"];
            }
    
            $service_accounts_sql = Account::leftjoin('account_transactions as AT', function ($join) {
                $join->on('AT.account_id', '=', 'accounts.id');
                $join->whereNull('AT.deleted_at');
            })
                ->where('is_service', 1)
                ->where('is_closed', 0)
                ->where('name', '!=', 'Safe Kiosk Account')
                ->where('business_id', $business_id)
//                ->whereBetween(\Illuminate\Support\Facades\DB::raw('date(AT.operation_date)'), [$start, $end])
                ->select(['name', 'account_number', 'accounts.note', 'accounts.id',
                    'is_closed', DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")
                    , DB::raw("SUM( IF(AT.type='credit' AND DATE_FORMAT(operation_date, '%Y-%m-%d') >='".$start."' AND DATE_FORMAT(operation_date, '%Y-%m-%d') <='".$end."', amount, 0) ) as total_deposit")
                    , DB::raw("SUM( IF(AT.type='debit' AND DATE_FORMAT(operation_date, '%Y-%m-%d') >='".$start."' AND DATE_FORMAT(operation_date, '%Y-%m-%d') <='".$end."', amount, 0) ) as total_withdraw")])
                ->groupBy('accounts.id');

            $service_accounts_sql->where(function ($q) {
                $q->where('account_type', '!=', 'capital');
                $q->orWhereNull('account_type');
            });
            $service_accounts = $service_accounts_sql->get();
            foreach ($service_accounts as $item){
                $services["balance"][] = empty($item["balance"]) ? 0 : $item["balance"];
                $services["deposit"][] = empty($item["total_deposit"]) ? 0 : $item["total_deposit"];
                $services["withdraw"][] = empty($item["total_withdraw"]) ? 0 : $item["total_withdraw"];
            }

            $output['bank_service_part_html'] = view('home.bank_service_part')->with(compact('bank_accounts', 'service_accounts'))->render();
            $output['banks'] = $banks;
            $output['services'] = $services;


            return $output;
        }
    }

    /**
     * Retrieves sell products whose available quntity is less than alert quntity.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProductStockAlert()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $query = VariationLocationDetails::join(
                'product_variations as pv',
                'variation_location_details.product_variation_id',
                '=',
                'pv.id'
            )
                    ->join(
                        'variations as v',
                        'variation_location_details.variation_id',
                        '=',
                        'v.id'
                    )
                    ->join(
                        'products as p',
                        'variation_location_details.product_id',
                        '=',
                        'p.id'
                    )
                    ->leftjoin(
                        'business_locations as l',
                        'variation_location_details.location_id',
                        '=',
                        'l.id'
                    )
                    ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                    ->where('p.business_id', $business_id)
                    ->where('p.enable_stock', 1)
                    ->where('p.is_inactive', 0)
                    ->whereRaw('variation_location_details.qty_available <= p.alert_quantity');

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('variation_location_details.location_id', $permitted_locations);
            }

            $products = $query->select(
                'p.name as product',
                'p.type',
                'pv.name as product_variation',
                'v.name as variation',
                'l.name as location',
                'variation_location_details.qty_available as stock',
                'u.short_name as unit'
            )
                    ->groupBy('variation_location_details.id')
                    ->orderBy('stock', 'asc');

            return Datatables::of($products)
                ->editColumn('product', function ($row) {
                    if ($row->type == 'single') {
                        return $row->product;
                    } else {
                        return $row->product . ' - ' . $row->product_variation . ' - ' . $row->variation;
                    }
                })
                ->editColumn('stock', function ($row) {
                    $stock = $row->stock ? $row->stock : 0 ;
                    return '<span data-is_quantity="true" class="display_currency" data-currency_symbol=false>'. (float)$stock . '</span> ' . $row->unit;
                })
                ->removeColumn('unit')
                ->removeColumn('type')
                ->removeColumn('product_variation')
                ->removeColumn('variation')
                ->rawColumns([2])
                ->make(false);
        }
    }

    /**
     * Retrieves payment dues for the purchases.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPurchasePaymentDues()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $today = \Carbon::now()->format("Y-m-d H:i:s");

            $query = Transaction::join(
                'contacts as c',
                'transactions.contact_id',
                '=',
                'c.id'
            )
                    ->leftJoin(
                        'transaction_payments as tp',
                        'transactions.id',
                        '=',
                        'tp.transaction_id'
                    )
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'purchase')
                    ->where('transactions.payment_status', '!=', 'paid')
                    ->whereRaw("DATEDIFF( DATE_ADD( transaction_date, INTERVAL IF(c.pay_term_type = 'days', c.pay_term_number, 30 * c.pay_term_number) DAY), '$today') <= 7");

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            $dues =  $query->select(
                'transactions.id as id',
                'c.name as supplier',
                'ref_no',
                'final_total',
                DB::raw('SUM(tp.amount) as total_paid')
            )
                        ->groupBy('transactions.id');

            return Datatables::of($dues)
                ->addColumn('due', function ($row) {
                    $total_paid = !empty($row->total_paid) ? $row->total_paid : 0;
                    $due = $row->final_total - $total_paid;
                    return '<span class="display_currency" data-currency_symbol="true">' .
                    $due . '</span>';
                })
                ->editColumn('ref_no', function ($row) {
                    if (auth()->user()->can('purchase.view')) {
                        return  '<a href="#" data-href="' . action('PurchaseController@show', [$row->id]) . '"
                                    class="btn-modal" data-container=".view_modal">' . $row->ref_no . '</a>';
                    }
                    return $row->ref_no;
                })
                ->removeColumn('id')
                ->removeColumn('final_total')
                ->removeColumn('total_paid')
                ->rawColumns([1, 2])
                ->make(false);
        }
    }

    /**
     * Retrieves payment dues for the purchases.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSalesPaymentDues()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $today = \Carbon::now()->format("Y-m-d H:i:s");

            $query = Transaction::join(
                'contacts as c',
                'transactions.contact_id',
                '=',
                'c.id'
            )
                    ->leftJoin(
                        'transaction_payments as tp',
                        'transactions.id',
                        '=',
                        'tp.transaction_id'
                    )
                    ->where('transactions.business_id', $business_id)
                    ->where('transactions.type', 'sell')
                    ->where('transactions.payment_status', '!=', 'paid')
                    ->whereNotNull('transactions.pay_term_number')
                    ->whereNotNull('transactions.pay_term_type')
                    ->whereRaw("DATEDIFF( DATE_ADD( transaction_date, INTERVAL IF(transactions.pay_term_type = 'days', transactions.pay_term_number, 30 * transactions.pay_term_number) DAY), '$today') <= 7");

            //Check for permitted locations of a user
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('transactions.location_id', $permitted_locations);
            }

            $dues =  $query->select(
                'transactions.id as id',
                'c.name as customer',
                'transactions.invoice_no',
                'final_total',
                DB::raw('SUM(tp.amount) as total_paid')
            )
                        ->groupBy('transactions.id');

            return Datatables::of($dues)
                ->addColumn('due', function ($row) {
                    $total_paid = !empty($row->total_paid) ? $row->total_paid : 0;
                    $due = $row->final_total - $total_paid;
                    return '<span class="display_currency" data-currency_symbol="true">' .
                    $due . '</span>';
                })
                ->editColumn('invoice_no', function ($row) {
                    if (auth()->user()->can('sell.view')) {
                        return  '<a href="#" data-href="' . action('SellController@show', [$row->id]) . '"
                                    class="btn-modal" data-container=".view_modal">' . $row->invoice_no . '</a>';
                    }
                    return $row->invoice_no;
                })
                ->removeColumn('id')
                ->removeColumn('final_total')
                ->removeColumn('total_paid')
                ->rawColumns([1, 2])
                ->make(false);
        }
    }

    public function loadMoreNotifications()
    {
        $notifications = auth()->user()->notifications()->orderBy('created_at', 'DESC')->paginate(10);

        if (request()->input('page') == 1) {
            auth()->user()->unreadNotifications->markAsRead();
        }

        $notifications_data = [];
        foreach ($notifications as $notification) {
            $data = $notification->data;
            if (in_array($notification->type, [\App\Notifications\RecurringInvoiceNotification::class])) {
                $msg = '';
                $icon_class = '';
                $link = '';
                if ($notification->type ==
                    \App\Notifications\RecurringInvoiceNotification::class) {
                    $msg = !empty($data['invoice_status']) && $data['invoice_status'] == 'draft' ?
                        __(
                            'lang_v1.recurring_invoice_error_message',
                            ['product_name' => $data['out_of_stock_product'], 'subscription_no' => !empty($data['subscription_no']) ? $data['subscription_no'] : '']
                        ) :
                        __(
                            'lang_v1.recurring_invoice_message',
                            ['invoice_no' => !empty($data['invoice_no']) ? $data['invoice_no'] : '', 'subscription_no' => !empty($data['subscription_no']) ? $data['subscription_no'] : '']
                        );
                    $icon_class = !empty($data['invoice_status']) && $data['invoice_status'] == 'draft' ? "fa fa-exclamation-triangle text-warning" : "fa fa-recycle text-green";
                    $link = action('SellPosController@listSubscriptions');
                }

                $notifications_data[] = [
                    'msg' => $msg,
                    'icon_class' => $icon_class,
                    'link' => $link,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans()
                ];
            } else {
                $module_notification_data = $this->moduleUtil->getModuleData('parse_notification', $notification);
                if (!empty($module_notification_data)) {
                    foreach ($module_notification_data as $module_data) {
                        if (!empty($module_data)) {
                            $notifications_data[] = $module_data;
                        }
                    }
                }
            }
        }

        return view('layouts.partials.notification_list', compact('notifications_data'));
    }
}
