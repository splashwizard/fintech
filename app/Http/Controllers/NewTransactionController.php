<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountTransaction;
use App\BusinessLocation;

use App\Category;
use App\Contact;
use App\CountryCode;
use App\CustomerGroup;
use App\GameId;
use App\InvoiceScheme;
use App\NewTransactions;
use App\NewTransactionTransfer;
use App\NewTransactionWithdraw;
use App\Product;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Transaction;
use App\TransactionSellLine;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\Utils\GameUtil;
use App\Utils\ModuleUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\NotificationUtil;

use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use \jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;
use App\Media;

class NewTransactionController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $contactUtil;
    protected $businessUtil;
    protected $transactionUtil;
    protected $productUtil;
    protected $cashRegisterUtil;
    protected $moduleUtil;
    protected $notificationUtil;
    protected $gameUtil;


    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ContactUtil $contactUtil, BusinessUtil $businessUtil, TransactionUtil $transactionUtil,
                                CashRegisterUtil $cashRegisterUtil, ModuleUtil $moduleUtil,NotificationUtil $notificationUtil, ProductUtil $productUtil, GameUtil $gameUtil)
    {
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->moduleUtil = $moduleUtil;
        $this->notificationUtil = $notificationUtil;
        $this->productUtil = $productUtil;
        $this->gameUtil = $gameUtil;

        $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
            'is_return' => 0, 'transaction_no' => ''];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('sell.create') && !auth()->user()->can('direct_sell.access') && !auth()->user()->can('view_own_sell_only')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $query = NewTransactions::join('contacts', 'contacts.id', 'new_transactions.client_id')
                ->join('products', 'products.id', 'new_transactions.product_id')
                ->where('contacts.business_id', $business_id)
                ->select( 'new_transactions.id',
                    'contacts.business_id as business_id',
                    DB::raw('CONCAT("(", contacts.contact_id, ") ", contacts.name) as contact_id'),
                    'new_transactions.invoice_no as request_number',
                    'new_transactions.bank_id',
                    'new_transactions.deposit_method',
                    'new_transactions.amount',
                    'new_transactions.reference_number',
                    'products.name as product_name',
                    'new_transactions.bonus_id as bank',
                    'new_transactions.receipt_url',
                    'new_transactions.status',
                    'new_transactions.created_at',
                );

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $query->whereDate('new_transactions.created_at', '>=', $start)
                    ->whereDate('new_transactions.created_at', '<=', $end);
            }
            $datatable = Datatables::of($query)
                ->addColumn(
                    'action',function ($row){
                    if($row->status == 'pending'){
                        $html = '<button class="btn btn-xs btn-success approve-deposit" style="margin-right:0.5em" href="' . action('NewTransactionController@approve', [$row->id]) . '">Approve</button>';
                        $html .= '<button class="btn btn-xs btn-danger reject-deposit" href="' . action('NewTransactionController@reject', [$row->id]) . '">Reject</button>';
                        return $html;
                    }
                    return null;
                })
                ->editColumn('status', function($row) {
                    if($row->status == 'pending')
                        return '<span class="badge btn-info">Pending</span>';
                    else if($row->status == 'approved')
                        return '<span class="badge btn-success">Approved</span>';
                    else
                        return '<span class="badge btn-danger">Rejected</span>';
                })
                ->addColumn('bonus', function ($row) {
//                    return $this->getBonusName($row->business_id, $row->bonus_id);
                    return 1;
                })
                ->addColumn('bank', function ($row) {
                    return Account::find($row->bank_id)->name;
                })
                ->addColumn('view_receipt', '<a href="#" data-href="{{ env(\'AWS_IMG_URL\').\'/uploads/receipt_images/\' . $receipt_url}}" class="btn btn-success view_uploaded_document"><i class="fa fa-picture-o" style="margin-right: 0.5em" aria-hidden="true"></i>@lang("new_transaction.view_receipt")</a>')
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->removeColumn('id')
                ->editColumn(
                    'amount',
                    '<span class="display_currency sell_amount" data-orig-value="{{$amount}}" data-highlight=true>{{($amount)}}</span>'
                );
            $rawColumns = ['amount', 'view_receipt', 'action', 'status'];

            return $datatable->rawColumns($rawColumns)
                ->make(true);
        }

//        $categories = Category::forDropdownBankOrService($business_id);
        $accounts = Account::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
        $sales_representative = User::forDropdown($business_id, false, false, true);

        //Commission agent filter
        $is_cmsn_agent_enabled = request()->session()->get('business.sales_cmsn_agnt');
        $commission_agents = [];
        if (!empty($is_cmsn_agent_enabled)) {
            $commission_agents = User::forDropdown($business_id, false, true, true);
        }

        $transaction_data = NewTransactionTransfer::join('products AS p', 'p.id', 'new_transaction_transfers.from_product_id')
            ->where('new_transaction_transfers.business_id', $business_id)
            ->select('p.name AS from_name','to_product_id', 'amount')->get();
        foreach ($transaction_data as $key => $row){
            $transaction_data[$key]->to_name = Product::where('id', $row->to_product_id)->first()->name;
        }
        return view('newtransaction.index')
            ->with(compact('accounts', 'customers', 'sales_representative', 'is_cmsn_agent_enabled', 'commission_agents', 'transaction_data'));
    }

    public function transfer(Request $request) {
        $business_id = $request->session()->get('user.business_id');
        $query = NewTransactionTransfer::join('products AS p', 'p.id', 'new_transaction_transfers.from_product_id')
            ->join('contacts', 'contacts.id', 'new_transaction_transfers.client_id')
            ->where('new_transaction_transfers.business_id', $business_id);
        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start = request()->start_date;
            $end =  request()->end_date;
            $query->whereDate('new_transaction_transfers.created_at', '>=', $start)
                ->whereDate('new_transaction_transfers.created_at', '<=', $end);
        }
        $transaction_data = $query->select('p.name AS from_name','to_product_id', 'amount',DB::raw('CONCAT("(", contacts.contact_id, ") ", contacts.name) as contact_id'), 'new_transaction_transfers.created_at', 'invoice_no')->get();
        foreach ($transaction_data as $key => $row){
            $transaction_data[$key]->to_name = Product::where('id', $row->to_product_id)->first()->name;
        }
        return ['html' => view('newtransaction.partials.transfer_table')->with(compact('transaction_data'))->render()];
    }

    public function withdraw()
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('sell.create') && !auth()->user()->can('direct_sell.access') && !auth()->user()->can('view_own_sell_only')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $query = NewTransactionWithdraw::join('contacts', 'contacts.id', 'new_transaction_withdraws.client_id')
                ->join('products', 'products.id', 'new_transaction_withdraws.product_id')
                ->where('contacts.business_id', $business_id)
                ->select('new_transaction_withdraws.*',
                    'new_transaction_withdraws.invoice_no as request_number',
                    DB::raw('CONCAT("(", contacts.contact_id, ") ", contacts.name) as contact_id'),
                    'products.name as product_name'
                );

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $query->whereDate('new_transaction_withdraws.created_at', '>=', $start)
                    ->whereDate('new_transaction_withdraws.created_at', '<=', $end);
            }
            $datatable = Datatables::of($query)
                ->addColumn(
                    'action',function ($row){
                    if($row->status == 'pending'){
                        $account_id = Product::find($row->product_id)->account_id;
//                        $html = '<button class="btn btn-xs btn-success approve-deposit" style="margin-right:0.5em" href="' . action('NewTransactionController@approveWithdraw', [$row->id]) . '">Approve</button>';
                        $html = '<button data-href="'.action('ServiceController@getWithdraw', $account_id).'" data-amount="'.$row->amount.'"  data-client_id="'.$row->client_id.'" style="margin: 5px 5px 5px 0" class="btn btn-xs btn-primary btn-modal btn-edit-withdraw" data-container=".view_modal">edit</button>';
                        $html .= '<button class="btn btn-xs btn-danger reject-deposit" href="' . action('NewTransactionController@rejectWithdraw', [$row->id]) . '">Reject</button>';
                        return $html;
                    }
                    return null;
                })
                ->editColumn('status', function($row) {
                    if($row->status == 'pending')
                        return '<span class="badge btn-info">Pending</span>';
                    else if($row->status == 'approved')
                        return '<span class="badge btn-success">Approved</span>';
                    else
                        return '<span class="badge btn-danger">Rejected</span>';
                })
                ->addColumn('bank', function ($row) {
                    return Account::find($row->bank_id)->name;
                })
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->removeColumn('id')
                ->editColumn(
                    'amount',
                    '<span class="display_currency sell_amount" data-orig-value="{{$amount}}" data-highlight=true>{{($amount)}}</span>'
                );
            $rawColumns = ['amount', 'action', 'status'];

            return $datatable->rawColumns($rawColumns)
                ->make(true);
        }

//        $categories = Category::forDropdownBankOrService($business_id);
        $accounts = Account::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
        $sales_representative = User::forDropdown($business_id, false, false, true);

        //Commission agent filter
        $is_cmsn_agent_enabled = request()->session()->get('business.sales_cmsn_agnt');
        $commission_agents = [];
        if (!empty($is_cmsn_agent_enabled)) {
            $commission_agents = User::forDropdown($business_id, false, true, true);
        }

        return view('newtransaction.index')
            ->with(compact('accounts', 'customers', 'sales_representative', 'is_cmsn_agent_enabled', 'commission_agents'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action('SellController@index'));
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $default_location = null;
        if (count($business_locations) == 1) {
            foreach ($business_locations as $id => $name) {
                $default_location = $id;
            }
        }

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id);
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

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->transactionUtil->payment_types();

        //Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);

        $default_datetime = $this->businessUtil->format_date('now', true);

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $invoice_schemes = InvoiceScheme::forDropdown($business_id);
        $default_invoice_schemes = InvoiceScheme::getDefault($business_id);

        return view('newtransaction.create')
            ->with(compact(
                'business_details',
                'taxes',
                'walk_in_customer',
                'business_locations',
                'bl_attributes',
                'default_location',
                'commission_agent',
                'types',
                'customer_groups',
                'payment_line',
                'payment_types',
                'price_groups',
                'default_datetime',
                'pos_settings',
                'invoice_schemes',
                'default_invoice_schemes'
            ));
    }


    public function approve(Request $request, $id)
    {
        if (request()->ajax()) {
//            try {
            $newTransaction = NewTransactions::find($id);

            $username = Contact::find($newTransaction->client_id)->name;
            $to_game = Product::find($newTransaction->product_id)->name;
            $deposit_amount = $this->getDepositAmount($request->session()->get('user.business_id'), $newTransaction->client_id, $newTransaction->amount, $newTransaction->bonus_id);
            $resp = $this->gameUtil->deposit($to_game, $username, $deposit_amount);
            if($resp['success'] == false) { // Player name exist
                return $resp;
            }

            $result = $this->createDeposit($request, $newTransaction->client_id, $newTransaction->bank_id, $newTransaction->amount, $newTransaction->product_id, $newTransaction->bonus_id);
            $newTransaction->status = 'approved';
            $newTransaction->save();
            $output = ['success' => true,
                'data' => $result,
                'msg' => __("lang_v1.new_transaction_approve_success")
            ];
//            } catch (\Exception $e) {
//                DB::rollBack();
//                \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
//
//                $output = ['success' => false,
//                    'msg' => __("messages.something_went_wrong")
//                ];
//            }
            return $output;
        }
    }

    private function getDepositAmount($business_id, $contact_id, $total_credit, $bonus_id){
        $bonus_amount = 0;
        $bonus_name = '';
        $bonus_variation_id = $bonus_id;
        $bonuses = $this->getBonuses($business_id);
        foreach ($bonuses as $bonus){
            if($bonus->id == $bonus_variation_id) {
                $bonus_name = $bonus->name;
                $bonus_amount = $bonus->selling_price;
            }
        }
        $no_bonus = Contact::find($contact_id)->no_bonus;
        $basic_bonus = 0;
        $special_bonus = 0;
        $bonus_rate = CountryCode::find(Contact::find($contact_id)->country_code_id)->basic_bonus_percent;
        if($bonus_variation_id != -1){
            if($bonus_name === 'Bonus') {
                $special_bonus = $total_credit * $bonus_amount / 100;
            } else {
                $special_bonus = $bonus_amount;
            }
        } else if($no_bonus == 0 && Contact::find($contact_id)->name != 'Unclaimed Trans') {
            $basic_bonus = floor($total_credit * $bonus_rate / 100);
        }
        $total_deposit = $total_credit + $basic_bonus + $special_bonus;
        return $total_deposit;
    }

    private function getBonuses($business_id){
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $default_location = null;
        if (count($business_locations) == 1) {
            foreach ($business_locations as $id => $name) {
                $default_location = $id;
            }
        }
        $location_id = $default_location;
        $bonuses_query = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
            ->leftjoin(
                'variation_location_details AS VLD',
                function ($join) use ($location_id) {
                    $join->on('variations.id', '=', 'VLD.variation_id');

                    //Include Location
                    if (!empty($location_id)) {
                        $join->where(function ($query) use ($location_id) {
                            $query->where('VLD.location_id', '=', $location_id);
                            //Check null to show products even if no quantity is available in a location.
                            //TODO: Maybe add a settings to show product not available at a location or not.
                            $query->orWhereNull('VLD.location_id');
                        });
                        ;
                    }
                }
            )
            ->join('accounts', 'p.account_id', 'accounts.id')
            ->leftjoin('account_transactions as AT', function ($join) {
                $join->on('AT.account_id', '=', 'accounts.id');
                $join->whereNull('AT.deleted_at');
            })
            ->groupBy('accounts.id')
            ->groupBy('variations.id')
            ->where('accounts.business_id', $business_id)
            ->where('p.type', '!=', 'modifier')
            ->where('p.is_inactive', 0)
            ->where('p.not_for_selling', 0);
        $bonuses_query->where('accounts.name', '=', 'Bonus Account');

        $no_bonus = (object)['id' => -1, 'name' => '', 'selling_price' => 0, 'variation' => 'No Bonus'];
        $bonuses = [];
        $bonuses[] = $no_bonus;
        $bonuses_data = $bonuses_query->select(
            \Illuminate\Support\Facades\DB::raw("SUM( IF(AT.type='credit', AT.amount, -1*AT.amount) ) as balance"),
            'p.id as product_id',
            'p.name',
            'p.type',
            'p.enable_stock',
            'variations.id',
            'p.account_id',
            'p.category_id',
            'variations.name as variation',
            'variations.default_sell_price as selling_price'
        )
            ->orderBy('p.name', 'asc')
            ->get();
        foreach ($bonuses_data as $item) {
            $bonuses[] = $item;
        }
        return $bonuses;
    }

    private function getBonusName($business_id, $bonus_variation_id){
        if($bonus_variation_id == -1)
            return 'Basic Bonus';
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $business_locations = $business_locations['locations'];

        $default_location = null;
        if (count($business_locations) == 1) {
            foreach ($business_locations as $id => $name) {
                $default_location = $id;
            }
        }
        $location_id = $default_location;
        $bonuses_query = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
            ->leftjoin(
                'variation_location_details AS VLD',
                function ($join) use ($location_id) {
                    $join->on('variations.id', '=', 'VLD.variation_id');

                    //Include Location
                    if (!empty($location_id)) {
                        $join->where(function ($query) use ($location_id) {
                            $query->where('VLD.location_id', '=', $location_id);
                            //Check null to show products even if no quantity is available in a location.
                            //TODO: Maybe add a settings to show product not available at a location or not.
                            $query->orWhereNull('VLD.location_id');
                        });
                        ;
                    }
                }
            )
            ->join('accounts', 'p.account_id', 'accounts.id')
            ->leftjoin('account_transactions as AT', function ($join) {
                $join->on('AT.account_id', '=', 'accounts.id');
                $join->whereNull('AT.deleted_at');
            })
            ->groupBy('accounts.id')
            ->groupBy('variations.id')
            ->where('accounts.business_id', $business_id)
            ->where('variations.id', $bonus_variation_id);
        $bonuses_query->where('accounts.name', '=', 'Bonus Account');
        $bonuses_data = $bonuses_query->select(
            DB::raw("CONCAT(p.name, ' - ', variations.name) AS name"),
            'variations.id as variation_id'
        )
            ->orderBy('p.name', 'asc')
            ->first();
        return $bonuses_data->name;
    }

    private function createDeposit(Request $request, $contact_id, $bank_id, $total_credit, $game_id, $bonus_id){
        $business_id = $request->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $business_locations = $business_locations['locations'];

        $default_location = null;
        if (count($business_locations) == 1) {
            foreach ($business_locations as $id => $name) {
                $default_location = $id;
            }
        }
        $products = [];
        $business_details = $this->businessUtil->getDetails($business_id);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $check_qty = !empty($pos_settings['allow_overselling']) ? false : true;
        $data = Variation::join('products', 'products.id', 'variations.product_id')
            ->where('products.account_id', $bank_id)
            ->where('default_sell_price', $total_credit)->select('variations.id as variation_id')->get();
        if(count($data) == 0)
            return;
        $bank_product = $this->productUtil->getDetailsFromVariation($data[0]->variation_id, $business_id, $default_location, $check_qty);
        $products[] = [
            'product_type'=> $bank_product['product_type'],
            'account_id'=> $bank_product['account_id'],
            'no_bonus'=> $bank_product['no_bonus'],
            'category_id'=> $bank_product['category_id'],
            'p_name'=> $bank_product['p_name'],
            'unit_price'=> $bank_product['default_sell_price'],
            'item_tax'=> 0.00,
            'tax_id'=> null,
            'payment_for'=> 0,
            'product_id'=> $bank_product['product_id'],
            'variation_id'=> $bank_product['variation_id'],
            'enable_stock'=> 0,
            'quantity'=> 1.00,
            'product_unit_id'=> $bank_product['unit_id'],
            'sub_unit_id'=> $bank_product['unit_id'],
            'base_unit_multiplier'=> 1,
            'unit_price_inc_tax'=> $bank_product['sell_price_inc_tax'],
            'amount'=> $bank_product['default_sell_price']
        ];

        //bonus start
        $bonus_amount = 0;
        $bonus_name = '';
        $bonus_variation_id = $bonus_id;
        $bonuses = $this->getBonuses($business_id);
        foreach ($bonuses as $bonus){
            if($bonus->id == $bonus_variation_id) {
                $bonus_name = $bonus->name;
                $bonus_amount = $bonus->selling_price;
            }
        }
        $no_bonus = Contact::find($contact_id)->no_bonus;
        $basic_bonus = 0;
        $special_bonus = 0;
        $bonus_rate = CountryCode::find(Contact::find($contact_id)->country_code_id)->basic_bonus_percent;
        if($bonus_variation_id != -1){
            if($bonus_name === 'Bonus') {
                $special_bonus = $total_credit * $bonus_amount / 100;
            } else {
                $special_bonus = $bonus_amount;
            }
        } else if($no_bonus == 0 && Contact::find($contact_id)->name != 'Unclaimed Trans') {
            $basic_bonus = floor($total_credit * $bonus_rate / 100);
        }
        //bonus end


        $data = Variation::where('product_id', $game_id)->select('variations.id as variation_id')->get();
        if(count($data) == 0)
            return null;

        $service_product = $this->productUtil->getDetailsFromVariation($data[0]->variation_id, $business_id, $default_location, $check_qty);
        $products[] = [
            'product_type'=> $service_product['product_type'],
            'account_id'=> $service_product['account_id'],
            'no_bonus'=> $service_product['no_bonus'],
            'category_id'=> $service_product['category_id'],
            'p_name'=> $service_product['p_name'],
            'unit_price'=> $service_product['default_sell_price'],
            'item_tax'=> 0.00,
            'tax_id'=> null,
            'payment_for'=> 0,
            'product_id'=> $service_product['product_id'],
            'variation_id'=> $service_product['variation_id'],
            'enable_stock'=> 0,
            'quantity'=> 1.00,
            'product_unit_id'=> $service_product['unit_id'],
            'sub_unit_id'=> $service_product['unit_id'],
            'base_unit_multiplier'=> 1,
            'unit_price_inc_tax'=> $service_product['sell_price_inc_tax'],
            'amount'=> $basic_bonus + $special_bonus + $total_credit
        ];


        $input = [
            'discount_type' =>  'percentage',
            'discount_amount' =>  0.00,
            'location_id' => $default_location,
            'products' => $products,
            'tax_rate_id' => null,
            'final_total' => 0,
            'sale_note' => null,
            'staff_note' => null,
            'contact_id' => $contact_id,
            'bonus_variation_id' => -1,
            'change_return' => 0,
            'recur_interval' => null,
            'recur_interval_type' => 'days',
            'recur_repetitions' => null,
            'status' => 'final',
        ];

        $is_direct_sale = false;
        //Check if subscribed or not, then check for users quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action('SellPosController@index'));
        }


        $user_id = $request->session()->get('user.id');

        $discount = ['discount_type' => $input['discount_type'],
            'discount_amount' => $input['discount_amount']
        ];
        $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount);

        DB::beginTransaction();

        $input['bank_in_time'] = null;
        $input['transaction_date'] =  \Carbon::now();
        if(!(auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Superadmin'))) {
            if (!$this->isShiftClosed($business_id)) {
                $input['transaction_date'] = date('Y-m-d H:i:s', strtotime('today') - 1);
            }
        }


        //Set commission agent
        $input['commission_agent'] = !empty($request->input('commission_agent')) ? $request->input('commission_agent') : null;

        if (isset($input['exchange_rate']) && $this->transactionUtil->num_uf($input['exchange_rate']) == 0) {
            $input['exchange_rate'] = 1;
        }

        //Customer group details
        $contact_id = $request->get('contact_id', null);
        $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
        $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;

        //set selling price group id
        if ($request->has('price_group')) {
            $input['selling_price_group_id'] = $request->input('price_group');
        }

        $input['is_suspend'] = isset($input['is_suspend']) && 1 == $input['is_suspend']  ? 1 : 0;
        if ($input['is_suspend']) {
            $input['sale_note'] = !empty($input['additional_notes']) ? $input['additional_notes'] : null;
        }

        //Generate reference number
        if (!empty($input['is_recurring'])) {
            //Update reference count
            $ref_count = $this->transactionUtil->setAndGetReferenceCount('subscription');
            $input['subscription_no'] = $this->transactionUtil->generateReferenceNumber('subscription', $ref_count);
        }

        //payment start
        $products = $input['products'];
        $service_id = -1;
        //check if service
        foreach ($products as $product) {
            if(Account::find($product['account_id'])->is_service){
                $service_id = $product['account_id'];
                break;
            }
        }
        $payments = [];
        // sum to one transaction
        $service_id_arr = [];
        $input['game_id'] = '';
        if(GameId::where('service_id', $service_id)->where('contact_id', $contact_id)->count() > 0)
            $input['game_id'] = GameId::where('service_id', $service_id)->where('contact_id', $contact_id)->get()->first()->cur_game_id;
        $total_credit = 0;
        $payment_data = [];
        $new_payment_item = [];

        foreach ($products as $key => $product) {
            if(empty($product['payment_for']))
                $products[$key]['payment_for'] = $contact_id;
        }
        foreach ($products as $product) {
            if($product['category_id'] == 66) {
                $total_credit += $product['amount'];
            } else if($product['category_id'] == 67) {
                $service_id_arr[] = $product['account_id'];
            }
            if($product['no_bonus'] == 1)
                $no_bonus = 1;
            $if_contact_account_same = false;
            foreach ($payment_data as $key => $payment_item) {
                if($payment_item['account_id'] == $product['account_id'] && $payment_item['payment_for'] == $product['payment_for']){
                    $payment_data[$key]['amount'] += $product['amount'];
                    $if_contact_account_same = true;
                    break;
                }
            }
            if($if_contact_account_same == false){
                $new_payment_item['amount'] = $product['amount'];
                $new_payment_item['category_id'] = $product['category_id'];
                $new_payment_item['p_name'] = $product['p_name'];
                $new_payment_item['account_id'] = $product['account_id'];
                $new_payment_item['payment_for'] = $product['payment_for'];
                $payment_data[] = $new_payment_item;
            }
        }
        foreach ($payment_data as $payment){
            $data = Category::where('id', $payment['category_id'])->get()->first();
            if($data->name == 'Banking'){
                $card_type = 'credit';
                $method = 'bank_transfer';
                $payments[] = ['account_id' => $payment['account_id'], 'method' => $method, 'amount' => $payment['amount'], 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => $card_type, 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
                    'is_return' => 0, 'transaction_no' => '', 'category_name' => $data->name, 'payment_for' => $payment['payment_for']];
            }

        }
        if(!empty($basic_bonus) || !empty($special_bonus)){
            if($bonus_variation_id == -1) {
                $bonus_key = Account::where('business_id', $business_id)->where('name', 'Bonus Account')->get()[0]->id;
                $query = Category::where('name', 'Banking');
                $data = $query->get()[0];
                $payments[] = ['account_id' => $bonus_key, 'method' => 'basic_bonus', 'amount' => $basic_bonus, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => 'credit', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
                    'is_return' => 0, 'transaction_no' => '', 'category_name' => $data->name, 'payment_for' => $contact_id];
            } else {
                $bonus_key = Account::where('business_id', $business_id)->where('name', 'Bonus Account')->get()[0]->id;
                $query = Category::where('name', 'Banking');
                $data = $query->get()[0];
                $payments[] = ['account_id' => $bonus_key, 'method' => 'free_credit', 'amount' => $special_bonus, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => 'credit', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
                    'is_return' => 0, 'transaction_no' => '', 'category_name' => $data->name, 'payment_for' => $contact_id];
            }
        }
        foreach ($payment_data as $payment){
            $data = Category::where('id', $payment['category_id'])->get()->first();
            if($data->name != 'Banking'){
                $method = 'service_transfer';
                $card_type = 'debit';
                $payments[] = ['account_id' => $payment['account_id'], 'method' => $method, 'amount' => $payment['amount'], 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => $card_type, 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
                    'is_return' => 0, 'transaction_no' => '', 'category_name' => $data->name, 'payment_for' => $payment['payment_for']];
            }
        }
        $input['payment'] = $payments;
        //payment end

        if ($request->session()->get('business.enable_rp') == 1) {
            $earned = $redeemed = 0;
            foreach ($input['payment'] as $key => $payment_item){
                //                        if($key < count($input['payment']) - 1 ){
                $product_category = $payment_item['category_name'];
                if($product_category == 'Banking') {
                    $earned += $payment_item['amount'];
                } else if($product_category == 'Service List') {
                    $redeemed += $payment_item['amount'];
                }
                //                        }
            }
            $input['final_total'] = $earned;
            $input['rp_redeemed'] = $redeemed;
        }

        $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id);
        ActivityLogger::activity("Created transaction, ticket # ".$transaction->invoice_no);

        $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id']);

        if (!$is_direct_sale) {
            //Add change return
            $change_return = $this->dummyPaymentLine;
            $change_return['amount'] = $input['change_return'];
            $change_return['is_return'] = 1;
            $input['payment'][] = $change_return;
        }


        if (!$transaction->is_suspend && !empty($input['payment'])) {
            if(!(auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Superadmin'))){
                if(!$this->isShiftClosed($business_id)){
                    foreach( $input['payment'] as $i => $payment){
                        $input['payment'][$i]['paid_on'] = date('Y-m-d H:i:s', strtotime('today') - 1);
                    }
                }
            }
            $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
        }

        $update_transaction = false;
        if ($this->transactionUtil->isModuleEnabled('tables')) {
            $transaction->res_table_id = request()->get('res_table_id');
            $update_transaction = true;
        }
        if ($this->transactionUtil->isModuleEnabled('service_staff')) {
            $transaction->res_waiter_id = request()->get('res_waiter_id');
            $update_transaction = true;
        }
        if ($update_transaction) {
            $transaction->save();
        }

        //Check for final and do some processing.
        if ($input['status'] == 'final') {
            //update product stock
            foreach ($input['products'] as $product) {
                $decrease_qty = $this->productUtil
                    ->num_uf($product['quantity']);
                if (!empty($product['base_unit_multiplier'])) {
                    $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
                }

                if ($product['enable_stock']) {
                    $this->productUtil->decreaseProductQuantity(
                        $product['product_id'],
                        $product['variation_id'],
                        $input['location_id'],
                        $decrease_qty
                    );
                }

                if ($product['product_type'] == 'combo') {
                    //Decrease quantity of combo as well.
                    $this->productUtil
                        ->decreaseProductQuantityCombo(
                            $product['combo'],
                            $input['location_id']
                        );
                }
            }

            //Add payments to Cash Register
            if (!$is_direct_sale && !$transaction->is_suspend && !empty($input['payment'])) {
                $this->cashRegisterUtil->addSellPayments($transaction, $input['payment']);
            }

            //Update payment status
            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

            if ($request->session()->get('business.enable_rp') == 1) {
                $this->transactionUtil->updateCustomerRewardPoints($contact_id, $transaction->rp_earned, 0, $transaction->rp_redeemed);
            }

            //Allocate the quantity from purchase and add mapping of
            //purchase & sell lines in
            //transaction_sell_lines_purchase_lines table
            $business_details = $this->businessUtil->getDetails($business_id);
            $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

            $business = ['id' => $business_id,
                'accounting_method' => $request->session()->get('business.accounting_method'),
                'location_id' => $input['location_id'],
                'pos_settings' => $pos_settings
            ];
            $this->transactionUtil->mapPurchaseSell($business, $transaction->sell_lines, 'purchase');

            //Auto send notification
            $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);
        }

        //Set Module fields
        if (!empty($input['has_module_data'])) {
            $this->moduleUtil->getModuleData('after_sale_saved', ['transaction' => $transaction, 'input' => $input]);
        }

        Media::uploadMedia($business_id, $transaction, $request, 'documents');

        DB::commit();
        if (empty($input['sub_type'])) {
            if (!$is_direct_sale && !$transaction->is_suspend) {
                $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id);
            } else {
                $receipt = '';
            }
        } else {
            $receipt = '';
        }
        $msg = trans("sale.pos_sale_added");
        if(!empty($service_id_arr)){
            $msg .= "<br/><br/>Updated balance for";
            foreach ($service_id_arr as $service_id) {
                $account = Account::leftjoin('account_transactions as AT', function ($join) {
                    $join->on('AT.account_id', '=', 'accounts.id');
                    $join->whereNull('AT.deleted_at');
                })
                    ->leftjoin( 'transactions as T',
                        'AT.transaction_id',
                        '=',
                        'T.id')
                    ->where('accounts.id', $service_id)
                    ->where('accounts.business_id', $business_id)
                    ->where(function ($q) {
                        $q->where('T.payment_status', '!=', 'cancelled');
                        $q->orWhere('T.payment_status', '=', null);
                    })
                    ->select(['name', \Illuminate\Support\Facades\DB::raw("SUM( IF( (accounts.shift_closed_at IS NULL OR AT.operation_date >= accounts.shift_closed_at) AND (!accounts.is_special_kiosk OR AT.sub_type IS NULL OR AT.sub_type != 'opening_balance'),  IF( AT.type='credit', AT.amount, -1*AT.amount), 0) )
                 * (1 - accounts.is_special_kiosk * 2) as balance")])
                    ->groupBy('accounts.id')->get()->first();
                $msg .= "<br/>".$account->name.' = <span style="font-weight: 800;color: black;">'.number_format( $account->balance, 2, '.', '').'</span>';
            }
        }

        $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt ];
    }

    public function reject($id)
    {
        if (request()->ajax()) {
            try {
                $newTransaction = NewTransactions::find($id);
                $newTransaction->status = 'rejected';
                $newTransaction->save();
                $output = ['success' => true,
                    'msg' => __("lang_v1.new_transaction_reject_success")
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

                $output = ['success' => false,
                    'msg' => __("messages.something_went_wrong")
                ];
            }
            return $output;
        }
    }

    public function approveWithdraw(Request $request, $id)
    {
        if (request()->ajax()) {
//            try {
            $newTransaction = NewTransactionWithdraw::find($id);
            $newTransaction->status = 'approved';
            $newTransaction->save();

            $result = $this->createWithdraw($request, $newTransaction->client_id, $newTransaction->bank_id, $newTransaction->amount, $newTransaction->product_id);
            if($result['success'])
                $output = ['success' => true,
                    'msg' => __("lang_v1.new_transaction_approve_success")
                ];
            else{
                $output = $result;
            }
//            } catch (\Exception $e) {
//                DB::rollBack();
//                \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
//
//                $output = ['success' => false,
//                    'msg' => __("messages.something_went_wrong")
//                ];
//            }
            return $output;
        }
    }

    private function createWithdraw($request, $contact_id, $bank_account_id, $amount, $product_id){
        $business_id = session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $request->validate([
            'document' => 'file|max:'. (config('constants.document_size_limit') / 1000)
        ]);

        $account_id = Product::find($product_id)->account_id;
        $accounts = Account::leftjoin('account_transactions as AT', function ($join) {
            $join->on('AT.account_id', '=', 'accounts.id');
            $join->whereNull('AT.deleted_at');
        })
            ->leftjoin('currencies', 'currencies.id', 'accounts.currency_id')
            ->where('accounts.id', $bank_account_id)
            ->select([DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")])
            ->groupBy('accounts.id');
        if($accounts->get()->first()->balance < $amount){
            $output = ['success' => false,
//                'msg' => 'Insufficient Bank Balance, please top up bank credit!'
                'msg' => json_encode($accounts->get())
            ];
            return $output;
        }

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $business_locations = $business_locations['locations'];
        $input = [];
        if (count($business_locations) >= 1) {
            foreach ($business_locations as $id => $name) {
                $input['location_id'] = $id;
            }
        }
        $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
        $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;
        $input['contact_id'] = $contact_id;
        $input['ref_no'] = 0;
        $now = new \DateTime('now');
        $input['transaction_date'] = $now->format('Y-m-d H:i:s');
        $input['discount_type'] = 'percentage';
        $input['discount_amount'] = 0;
        $input['final_total'] = $amount;
        $input['additional_notes'] = '';
        $invoice_total = ['total_before_tax' => $amount, 'tax' => 0];
        //                $transaction = $this->transactionUtil->createSellReturnTransaction($business_id, $input, $invoice_total, $user_id);
        //upload document
        $document_name = $this->transactionUtil->uploadFile($request, 'document', 'service_documents');
        if (!empty($document_name)) {
            $input['document'] = $document_name;
        }
        $sub_type = 'withdraw_to_customer';
        $transaction = $this->transactionUtil->createSellReturnTransaction($business_id, $input, $invoice_total, $user_id, $sub_type);
        ActivityLogger::activity("Created transaction, ticket # ".$transaction->invoice_no);
        $this->transactionUtil->createWithDrawPaymentLine($transaction, $user_id, $account_id, 1, 'credit');
        $this->transactionUtil->updateCustomerRewardPoints($contact_id, $amount, 0, 0);

        $credit_data = [
            'amount' => $amount,
            'account_id' => $account_id,
            'type' => 'credit',
            'sub_type' => 'withdraw',
            'operation_date' => $now->format('Y-m-d H:i:s'),
            'created_by' => session()->get('user.id'),
            'transaction_id' => $transaction->id,
            'shift_closed_at' => Account::find($account_id)->shift_closed_at
        ];

        AccountTransaction::createAccountTransaction($credit_data);
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $business_locations = $business_locations['locations'];
        $input = [];
        if (count($business_locations) >= 1) {
            foreach ($business_locations as $id => $name) {
                $input['location_id'] = $id;
            }
        }
        $bank_account_id = $request->input('bank_account_id');

        $contact_id = $request->input('withdraw_to');
        $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
        $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;
        $input['contact_id'] = $contact_id;
        $input['ref_no'] = 0;
        $date = new \DateTime('now');
        $input['transaction_date'] = $date->format('Y-m-d H:i:s');
        $input['discount_type'] = 'percentage';
        $input['discount_amount'] = 0;
        $input['final_total'] = $amount;
        $input['additional_notes'] = $request->input('note');
        $is_service = 0;
        $this->transactionUtil->createWithDrawPaymentLine($transaction, $user_id, $bank_account_id, $is_service, 'debit');
        $this->transactionUtil->updateCustomerRewardPoints($contact_id, 0, 0, $amount);
        if(!$is_service){
            $account = Account::where('business_id', $business_id)
                ->findOrFail($bank_account_id);
            $amount += $account->service_charge;
        }
        $credit_data = [
            'amount' => $amount,
            'account_id' => $bank_account_id,
            'type' => 'debit',
            'sub_type' => 'withdraw',
            'created_by' => session()->get('user.id'),
            'note' => $request->input('note'),
            'transfer_account_id' => $account_id,
            'transfer_transaction_id' => $transaction->id,
            'operation_date' => $now->format('Y-m-d H:i:s'),
            'transaction_id' => $transaction->id,
            'shift_closed_at' => Account::find($bank_account_id)->shift_closed_at
        ];

        $credit = AccountTransaction::createAccountTransaction($credit_data);

        $output = ['success' => true,
            'msg' => __("account.withdrawn_successfully")
        ];
        return $output;
    }

    public function rejectWithdraw($id)
    {
        if (request()->ajax()) {
            try {
                $newTransaction = NewTransactionWithdraw::find($id);
                $newTransaction->status = 'rejected';
                $newTransaction->save();
                $output = ['success' => true,
                    'msg' => __("lang_v1.new_transaction_reject_success")
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

                $output = ['success' => false,
                    'msg' => __("messages.something_went_wrong")
                ];
            }
            return $output;
        }
    }

    private function receiptContent(
        $business_id,
        $location_id,
        $transaction_id,
        $printer_type = null,
        $is_package_slip = false,
        $from_pos_screen = true,
        $content_type = 'default'
        // $is_order_confirm = false
    ) {
        $output = ['is_enabled' => false,
            'print_type' => 'browser',
            'html_content' => null,
            'printer_config' => [],
            'data' => []
        ];


        $business_details = $this->businessUtil->getDetails($business_id);
        $location_details = BusinessLocation::find($location_id);

        if ($from_pos_screen && $location_details->print_receipt_on_invoice != 1) {
            return $output;
        }
        //Check if printing of invoice is enabled or not.
        //If enabled, get print type.
        $output['is_enabled'] = true;

        if($content_type == 'default'){
            $layout_id = $location_details->invoice_layout_id;
        } else if($content_type == 'confirm_order'){
            $layout_id = $location_details->invoice_layout_order_conf_id;
        } else if($content_type == 'presale_note'){
            $layout_id = $location_details->invoice_layout_presale_note_id;
        }
        $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $location_id, $layout_id);

        //Check if printer setting is provided.
        $receipt_printer_type = is_null($printer_type) ? $location_details->receipt_printer_type : $printer_type;

        $receipt_details = $this->transactionUtil->getReceiptDetails($transaction_id, $location_id, $invoice_layout, $business_details, $location_details, $receipt_printer_type);

        $currency_details = [
            'symbol' => $business_details->currency_symbol,
            'thousand_separator' => $business_details->thousand_separator,
            'decimal_separator' => $business_details->decimal_separator,
        ];
        $receipt_details->currency = $currency_details;

        if ($is_package_slip) {
            $output['html_content'] = view('sale_pos_deposit.receipts.packing_slip', compact('receipt_details'))->render();
            return $output;
        }
        //If print type browser - return the content, printer - return printer config data, and invoice format config
        if ($receipt_printer_type == 'printer') {
            $output['print_type'] = 'printer';
            $output['printer_config'] = $this->businessUtil->printerConfig($business_id, $location_details->printer_id);
            $output['data'] = $receipt_details;
        } else {
            $layout = !empty($receipt_details->design) ? 'sale_pos.receipts.' . $receipt_details->design : 'sale_pos.receipts.classic';
            $output['html_content'] = view($layout, compact('receipt_details'))->render();
        }

        return $output;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access') && !auth()->user()->can('view_own_sell_only')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $taxes = TaxRate::where('business_id', $business_id)
            ->pluck('name', 'id');
        $query = Transaction::where('business_id', $business_id)
            ->where('id', $id)
            ->with(['contact', 'sell_lines' => function ($q) {
                $q->whereNull('parent_sell_line_id');
            },'sell_lines.product', 'sell_lines.product.unit', 'sell_lines.variations', 'sell_lines.variations.product_variation', 'payment_lines', 'sell_lines.modifiers', 'sell_lines.lot_details', 'tax', 'sell_lines.sub_unit', 'table', 'service_staff', 'sell_lines.service_staff']);

        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
            $query->where('transactions.created_by', request()->session()->get('user.id'));
        }

        $sell = $query->firstOrFail();

        foreach ($sell->sell_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
                $sell->sell_lines[$key] = $formated_sell_line;
            }
        }

        $payment_types = $this->transactionUtil->payment_types();

        $order_taxes = [];
        if (!empty($sell->tax)) {
            if ($sell->tax->is_tax_group) {
                $order_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($sell->tax, $sell->tax_amount));
            } else {
                $order_taxes[$sell->tax->name] = $sell->tax_amount;
            }
        }

        $business_details = $this->businessUtil->getDetails($business_id);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        return view('sale_pos.show')
            ->with(compact('taxes', 'sell', 'payment_types', 'order_taxes', 'pos_settings'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
            return back()
                ->with('status', ['success' => 0,
                    'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])]);
        }

        //Check if return exist then not allowed
        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', ['success' => 0,
                'msg' => __('lang_v1.return_exist')]);
        }

        $business_id = request()->session()->get('user.business_id');

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $transaction = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->findorfail($id);

        $location_id = $transaction->location_id;
        $location_printer_type = BusinessLocation::find($location_id)->receipt_printer_type;

        $sell_details = TransactionSellLine::
        join(
            'products AS p',
            'transaction_sell_lines.product_id',
            '=',
            'p.id'
        )
            ->join(
                'variations AS variations',
                'transaction_sell_lines.variation_id',
                '=',
                'variations.id'
            )
            ->join(
                'product_variations AS pv',
                'variations.product_variation_id',
                '=',
                'pv.id'
            )
            ->leftjoin('variation_location_details AS vld', function ($join) use ($location_id) {
                $join->on('variations.id', '=', 'vld.variation_id')
                    ->where('vld.location_id', '=', $location_id);
            })
            ->leftjoin('units', 'units.id', '=', 'p.unit_id')
            ->where('transaction_sell_lines.transaction_id', $id)
            ->select(
                DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name, ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
                'p.id as product_id',
                'p.enable_stock',
                'p.name as product_actual_name',
                'pv.name as product_variation_name',
                'pv.is_dummy as is_dummy',
                'variations.name as variation_name',
                'variations.sub_sku',
                'p.barcode_type',
                'p.enable_sr_no',
                'variations.id as variation_id',
                'units.short_name as unit',
                'units.allow_decimal as unit_allow_decimal',
                'transaction_sell_lines.tax_id as tax_id',
                'transaction_sell_lines.item_tax as item_tax',
                'transaction_sell_lines.unit_price as default_sell_price',
                'transaction_sell_lines.unit_price_inc_tax as sell_price_inc_tax',
                'transaction_sell_lines.unit_price_before_discount as unit_price_before_discount',
                'transaction_sell_lines.id as transaction_sell_lines_id',
                'transaction_sell_lines.quantity as quantity_ordered',
                'transaction_sell_lines.sell_line_note as sell_line_note',
                'transaction_sell_lines.lot_no_line_id',
                'transaction_sell_lines.line_discount_type',
                'transaction_sell_lines.line_discount_amount',
                'transaction_sell_lines.res_service_staff_id',
                'units.id as unit_id',
                'transaction_sell_lines.sub_unit_id',
                DB::raw('vld.qty_available + transaction_sell_lines.quantity AS qty_available')
            )
            ->get();
        if (!empty($sell_details)) {
            foreach ($sell_details as $key => $value) {
                if ($transaction->status != 'final') {
                    $actual_qty_avlbl = $value->qty_available - $value->quantity_ordered;
                    $sell_details[$key]->qty_available = $actual_qty_avlbl;
                    $value->qty_available = $actual_qty_avlbl;
                }

                $sell_details[$key]->formatted_qty_available = $this->transactionUtil->num_f($value->qty_available);
                $lot_numbers = [];
                if (request()->session()->get('business.enable_lot_number') == 1) {
                    $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($value->variation_id, $business_id, $location_id);
                    foreach ($lot_number_obj as $lot_number) {
                        //If lot number is selected added ordered quantity to lot quantity available
                        if ($value->lot_no_line_id == $lot_number->purchase_line_id) {
                            $lot_number->qty_available += $value->quantity_ordered;
                        }

                        $lot_number->qty_formated = $this->transactionUtil->num_f($lot_number->qty_available);
                        $lot_numbers[] = $lot_number;
                    }
                }
                $sell_details[$key]->lot_numbers = $lot_numbers;

                if (!empty($value->sub_unit_id)) {
                    $value = $this->productUtil->changeSellLineUnit($business_id, $value);
                    $sell_details[$key] = $value;
                }

                $sell_details[$key]->formatted_qty_available = $this->transactionUtil->num_f($value->qty_available);
            }
        }

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id);
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

        //Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);

        $transaction->transaction_date = $this->transactionUtil->format_date($transaction->transaction_date, true);

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $waiters = null;
        if ($this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff'])) {
            $waiters = $this->productUtil->serviceStaffDropdown($business_id);
        }

        $invoice_schemes = [];
        $default_invoice_schemes = null;

        if ($transaction->status == 'draft') {
            $invoice_schemes = InvoiceScheme::forDropdown($business_id);
            $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        }

        $redeem_details = [];
        if (request()->session()->get('business.enable_rp') == 1) {
            $redeem_details = $this->transactionUtil->getRewardRedeemDetails($business_id, $transaction->contact_id);

            $redeem_details['points'] += $transaction->rp_redeemed;
            $redeem_details['points'] -= $transaction->rp_earned;
        }

        $edit_discount = auth()->user()->can('edit_product_discount_from_sale_screen');
        $edit_price = auth()->user()->can('edit_product_price_from_sale_screen');
        return view('newtransaction.edit')
            ->with(compact('business_details', 'taxes', 'sell_details', 'transaction', 'commission_agent', 'types', 'customer_groups', 'price_groups', 'pos_settings', 'waiters', 'invoice_schemes', 'default_invoice_schemes', 'redeem_details', 'edit_discount', 'edit_price'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, $id)
    // {
    //     //
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function destroy($id)
    // {
    //     //
    // }

    /**
     * Display a listing sell drafts.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDrafts()
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        return view('sale_pos.draft');
    }

    /**
     * Display a listing sell quotations.
     *
     * @return \Illuminate\Http\Response
     */
    public function getQuotations()
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        return view('sale_pos.quotations');
    }

    /**
     * Send the datatable response for draft or quotations.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDraftDatables()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $is_quotation = request()->only('is_quotation', 0);

            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->join(
                    'business_locations AS bl',
                    'transactions.location_id',
                    '=',
                    'bl.id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'draft')
                ->where('is_quotation', $is_quotation)
                ->select(
                    'transactions.id',
                    'transaction_date',
                    'invoice_no',
                    'contacts.name',
                    'bl.name as business_location',
                    'is_direct_sale'
                );

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $sells->whereDate('transaction_date', '>=', $start)
                    ->whereDate('transaction_date', '<=', $end);
            }
            $sells->groupBy('transactions.id');

            return Datatables::of($sells)
                ->addColumn(
                    'action',
                    '<a href="#" data-href="{{action(\'SellController@show\', [$id])}}" class="btn btn-xs btn-success btn-modal" data-container=".view_modal"><i class="fa fa-external-link" aria-hidden="true"></i> @lang("messages.view")</a>
                    &nbsp;
                    @if($is_direct_sale == 1)
                        <a target="_blank" href="{{action(\'SellController@edit\', [$id])}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.edit")</a>
                    @else
                    <a target="_blank" href="{{action(\'SellPosController@edit\', [$id])}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.edit")</a>
                    @endif

                    &nbsp; 
                    <a href="#" class="print-invoice btn btn-xs btn-info" data-href="{{route(\'sell.printInvoice\', [$id])}}"><i class="fa fa-print" aria-hidden="true"></i> @lang("messages.print")</a>

                    &nbsp; <a href="{{action(\'SellPosController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete-sale"><i class="fa fa-trash"></i>  @lang("messages.delete")</a>
                    '
                )
                ->removeColumn('id')
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("sell.view")) {
                            return  action('SellController@show', [$row->id]) ;
                        } else {
                            return '';
                        }
                    }])
                ->rawColumns(['action', 'invoice_no', 'transaction_date'])
                ->make(true);
        }
    }

    /**
     * Creates copy of the requested sale.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function duplicateSell($id)
    {
        if (!auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $transaction = Transaction::where('business_id', $business_id)
                ->where('type', 'sell')
                ->findorfail($id);
            $duplicate_transaction_data = [];
            foreach ($transaction->toArray() as $key => $value) {
                if (!in_array($key, ['id', 'created_at', 'updated_at'])) {
                    $duplicate_transaction_data[$key] = $value;
                }
            }
            $duplicate_transaction_data['status'] = 'draft';
            $duplicate_transaction_data['payment_status'] = null;
            $duplicate_transaction_data['transaction_date'] =  \Carbon::now();
            $duplicate_transaction_data['created_by'] = $user_id;

            DB::beginTransaction();
            $duplicate_transaction_data['invoice_no'] = $this->transactionUtil->getInvoiceNumber($business_id, 'draft', $duplicate_transaction_data['location_id']);

            //Create duplicate transaction
            $duplicate_transaction = Transaction::create($duplicate_transaction_data);

            //Create duplicate transaction sell lines
            $duplicate_sell_lines_data = [];

            foreach ($transaction->sell_lines as $sell_line) {
                $new_sell_line = [];
                foreach ($sell_line->toArray() as $key => $value) {
                    if (!in_array($key, ['id', 'transaction_id', 'created_at', 'updated_at', 'lot_no_line_id'])) {
                        $new_sell_line[$key] = $value;
                    }
                }

                $duplicate_sell_lines_data[] = $new_sell_line;
            }

            $duplicate_transaction->sell_lines()->createMany($duplicate_sell_lines_data);

            DB::commit();

            $output = ['success' => 0,
                'msg' => trans("lang_v1.duplicate_sell_created_successfully")
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0,
                'msg' => trans("messages.something_went_wrong")
            ];
        }

        if (!empty($duplicate_transaction)) {
            if ($duplicate_transaction->is_direct_sale == 1) {
                return redirect()->action('SellController@edit', [$duplicate_transaction->id])->with(['status', $output]);
            } else {
                return redirect()->action('SellPosController@edit', [$duplicate_transaction->id])->with(['status', $output]);
            }
        } else {
            abort(404, 'Not Found.');
        }
    }
}
