<?php
/* LICENSE: This source file belongs to The Web Fosters. The customer
 * is provided a licence to use it.
 * Permission is hereby granted, to any person obtaining the licence of this
 * software and associated documentation files (the "Software"), to use the
 * Software for personal or business purpose ONLY. The Software cannot be
 * copied, published, distribute, sublicense, and/or sell copies of the
 * Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. THE AUTHOR CAN FIX
 * ISSUES ON INTIMATION. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
 * BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH
 * THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author     The Web Fosters <thewebfosters@gmail.com>
 * @owner      The Web Fosters <thewebfosters@gmail.com>
 * @copyright  2018 The Web Fosters
 * @license    As attached in zip file.
 */

namespace App\Http\Controllers;

use App\Account;
use App\AccountTransaction;
use App\BankBrand;
use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\CashRegister;
use App\Category;
use App\Contact;
use App\CountryCode;
use App\CustomerGroup;
use App\GameId;
use App\Media;
use App\Membership;
use App\Product;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Transaction;
use App\TransactionPayment;
use App\TransactionSellLine;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\ContactUtil;
use App\Utils\GameUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Essentials\Entities\EssentialsRequest;
use Modules\Essentials\Entities\EssentialsRequestType;
use Yajra\DataTables\Facades\DataTables;
use \jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;

class SellPosDepositController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $contactUtil;
    protected $productUtil;
    protected $businessUtil;
    protected $transactionUtil;
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
    public function __construct(
        ContactUtil $contactUtil,
        ProductUtil $productUtil,
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        CashRegisterUtil $cashRegisterUtil,
        ModuleUtil $moduleUtil,
        NotificationUtil $notificationUtil,
        GameUtil $gameUtil
    ) {
        $this->contactUtil = $contactUtil;
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->moduleUtil = $moduleUtil;
        $this->notificationUtil = $notificationUtil;
        $this->gameUtil = $gameUtil;

        $this->dummyPaymentLine = ['method' => 'bank_transfer', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
        'is_return' => 0, 'transaction_no' => ''];
        $this->transactionTypes = [
            'sell' => __('sale.sale'),
            'purchase' => __('lang_v1.purchase'),
            'sell_return' => __('lang_v1.sell_return'),
            'purchase_return' =>  __('lang_v1.purchase_return'),
            'opening_balance' => __('lang_v1.opening_balance'),
            'payment' => __('lang_v1.payment')
        ];
        date_default_timezone_set('Asia/ShangHai');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
      
        $sales_representative = User::forDropdown($business_id, false, false, true);
        
        $is_cmsn_agent_enabled = request()->session()->get('business.sales_cmsn_agnt');
        $commission_agents = [];
        if (!empty($is_cmsn_agent_enabled)) {
            $commission_agents = User::forDropdown($business_id, false, true, true);
        }

        //Service staff filter
        $service_staffs = null;
        if ($this->productUtil->isModuleEnabled('service_staff')) {
            $service_staffs = $this->productUtil->serviceStaffDropdown($business_id);
        }

        return view('sale_pos_deposit.index')->with(compact('business_locations', 'customers', 'sales_representative', 'is_cmsn_agent_enabled', 'commission_agents', 'service_staffs'));
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
            DB::raw("SUM( IF(AT.type='credit', AT.amount, -1*AT.amount) ) as balance"),
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($transaction_id=0)
    {
        if (!auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for users quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('HomeController@index'));
        } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action('SellPosController@index'));
        }
        
        //Check if there is a open register, if no then redirect to Create Register screen.
        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
//            return redirect()->action('CashRegisterController@create');
            try {
                $initial_amount = 0;
                $user_id = request()->session()->get('user.id');
                $business_id = request()->session()->get('user.business_id');

                $register = CashRegister::create([
                    'business_id' => $business_id,
                    'user_id' => $user_id,
                    'status' => 'open'
                ]);
                $register->cash_register_transactions()->create([
                    'amount' => $initial_amount,
                    'pay_method' => 'cash',
                    'type' => 'credit',
                    'transaction_type' => 'initial'
                ]);
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            }
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        
        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);
        $payment_types = $this->productUtil->payment_types();

        $payment_lines[] = $this->dummyPaymentLine;

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $default_location = null;
        if (count($business_locations) == 1) {
            foreach ($business_locations as $id => $name) {
                $default_location = $id;
            }
        }

        //Shortcuts
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
        
        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id, false);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id, false);
        }

        //If brands, category are enabled then send else false.
        $bank_categories = (request()->session()->get('business.enable_category') == 1) ? Category::bankCatAndSubCategories($business_id) : false;
        $service_categories = (request()->session()->get('business.enable_category') == 1) ? Category::serviceCatAndSubCategories($business_id) : false;
        $bank_products = Product::forDropDown($business_id, 0);
        $service_products = Product::forDropDown($business_id, 1);

        $brands = (request()->session()->get('business.enable_brand') == 1) ? Brands::where('business_id', $business_id)
                    ->pluck('name', 'id')
                    ->prepend(__('lang_v1.all_brands'), 'all') : false;

        $change_return = $this->dummyPaymentLine;

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

        // Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }
        // Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);

        $services = Account::where('business_id', $business_id)->where('is_service', 1)->get();
        $memberships = Membership::forDropdown($business_id);
        $bank_brands  = BankBrand::forDropdown($business_id);

        // Bonuses

        $bonuses = $this->getBonuses($business_id);
        $country_codes = CountryCode::forDropdown(false);

        $selected_bank = 0;
        if($transaction_id != 0){
            $selected_bank = TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'bank_transfer')->first()->account_id;
        }


        // $bonus_types = [];
        // $values = [10, 30, 50, 100];
        // foreach($values as $value){
        //     $object = [];
        //     $object['type'] = 'percent';
        //     $object['amount'] = $value;
        //     $object['text'] = 'Bonus - '.$value.'%';
        //     $bonus_types[] = $object;
        // }
        // foreach($values as $value){
        //     $object = [];
        //     $object['type'] = 'fixed';
        //     $object['amount'] = $value;
        //     $object['text'] = 'CNY++ - RM'.$value;
        //     $bonus_types[] = $object;
        // }

        return view('sale_pos_deposit.create')
            ->with(compact(
                'business_details',
                'taxes',
                'payment_types',
                'walk_in_customer',
                'payment_lines',
                'business_locations',
                'bl_attributes',
                'default_location',
                'shortcuts',
                'commission_agent',
                'bank_categories',
                'service_categories',
                'bank_products',
                'service_products',
                'brands',
                'pos_settings',
                'change_return',
                'types',
                'customer_groups',
                'memberships',
                'bank_brands',
                'accounts',
                'price_groups',
                'services',
                'bonuses',
                'country_codes',
                'selected_bank',
                'transaction_id'
            ));
    }

    public function createSelectedTransaction($transaction_id)
    {
        return $this->create($transaction_id);
    }

    private function isShiftClosed($business_id){
        $start = date('Y-m-d H:i:s', strtotime('today'));
        $end = date('Y-m-d H:i:s', strtotime('now'));
        if(CashRegister::where('business_id', $business_id)->where('closed_at', '>=', $start)->where('closed_at', '<=', $end)->count() > 0)
            return 1;
        return 0;
    }

    public function checkShiftClosed(Request $request){
        $business_id = $request->session()->get('business.id');
        $is_shift_closed = $this->isShiftClosed($business_id);
        return ['is_shift_closed' => $is_shift_closed];
    }

    public function getNoBonus(Request $request){
        $customer_id = $request->get('customer_id');
        $no_bonus = Contact::find($customer_id)->no_bonus;
        return ['no_bonus' => $no_bonus];
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
//        exit;
        if (!auth()->user()->can('sell.create') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        $is_direct_sale = false;
        if (!empty($request->input('is_direct_sale'))) {
            $is_direct_sale = true;
        }

        //Check if there is a open register, if no then redirect to Create Register screen.
        if (!$is_direct_sale && $this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action('CashRegisterController@create');
        }
//        try {
            $input = $request->except('_token');
//            print_r($input['bonus_variation_id']);exit;


            //Check Customer credit limit
            $is_credit_limit_exeeded = $this->transactionUtil->isCustomerCreditLimitExeeded($input);

            if ($is_credit_limit_exeeded !== false) {
                $credit_limit_amount = $this->transactionUtil->num_f($is_credit_limit_exeeded, true);
                $output = ['success' => 0,
                            'msg' => __('lang_v1.cutomer_credit_limit_exeeded', ['credit_limit' => $credit_limit_amount])
                        ];
                if (!$is_direct_sale) {
                    return $output;
                } else {
                    return redirect()
                        ->action('SellController@index')
                        ->with('status', $output);
                }
            }

            //status is send as quotation from Add sales screen.
            if ($input['status'] == 'quotation') {
                $input['status'] = 'draft';
                $input['is_quotation'] = 1;
                $input['is_confirm_order'] = 0;
                $input['is_presale_note'] = 0;
            }
            else if ($input['status'] == 'order_conf') {
                $input['status'] = 'draft';
                $input['is_confirm_order'] = 1;
                $input['is_quotation'] = -1;
                $input['is_presale_note'] = 0;
            }
            else if ($input['status'] == 'presale_note') {
                $input['status'] = 'draft';
                $input['is_presale_note'] = 1;
                $input['is_quotation'] = -1;
                $input['is_confirm_order'] = 0;
            } else {
                $input['is_quotation'] = 0;
                $input['is_confirm_order'] = 0;
                $input['is_presale_note'] = 0;
            }

            if (!empty($input['products'])) {
                $business_id = $request->session()->get('user.business_id');

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

                if(empty($request->input('bank_changed'))){
                    $input['bank_in_time'] = null;
                }
                if (empty($request->input('transaction_date'))) {
                    $input['transaction_date'] =  \Carbon::now();
                } else {
                    $input['transaction_date'] = $this->productUtil->uf_date($request->input('transaction_date'), true);
                }
                if(!(auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Superadmin'))) {
                    if (!$this->isShiftClosed($business_id)) {
                        $input['transaction_date'] = date('Y-m-d H:i:s', strtotime('today') - 1);
                    }
                }
                if ($is_direct_sale) {
                    $input['is_direct_sale'] = 1;
                }

                //Set commission agent
                $input['commission_agent'] = !empty($request->input('commission_agent')) ? $request->input('commission_agent') : null;
                $commsn_agnt_setting = $request->session()->get('business.sales_cmsn_agnt');
                if ($commsn_agnt_setting == 'logged_in_user') {
                    $input['commission_agent'] = $user_id;
                }

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

                if ($is_direct_sale) {
                    $input['invoice_scheme_id'] = $request->input('invoice_scheme_id');
                }

                //payment start
                $products = $input['products'];
//                $contact_id = request()->get('customer_id');
                $bonus_rate = CountryCode::find(Contact::find($contact_id)->country_code_id)->basic_bonus_percent;
                $is_service = 0;
                $service_id = -1;
                //check if service
                foreach ($products as $product) {
                    if(Account::find($product['account_id'])->is_service){
                        $is_service = 1;
                        $service_id = $product['account_id'];
                        break;
                    }
                }
                $payments = [];
                // sum to one transaction
                $bonus_amount = 0;
                $bonus_name = '';
                $bonus_variation_id = $input['bonus_variation_id'];
                $bonuses = $this->getBonuses($business_id);
                foreach ($bonuses as $bonus){
                    if($bonus->id == $bonus_variation_id) {
                        $bonus_name = $bonus->name;
                        $bonus_amount = $bonus->selling_price;
                    }
                }
                $no_bonus = Contact::find($contact_id)->no_bonus;
                $service_id_arr = [];
//                bonus_variation_id
                if($is_service) {
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

                    $basic_bonus = 0;
                    $special_bonus = 0;
                    if($bonus_variation_id != -1){
                        if($bonus_name === 'Bonus') {
                            $special_bonus = $total_credit * $bonus_amount / 100;
                        } else {
                            $special_bonus = $bonus_amount;
                        }
                    } else if($no_bonus == 0 && Contact::find($contact_id)->name != 'Unclaimed Trans') {
                        $basic_bonus = floor($total_credit * $bonus_rate / 100);
//                        $basic_bonus = 0;
                    }

                    // Deposit to XE88 vendor
                    $connected_kiosk_id = Account::find($service_id)->connected_kiosk_id;
                    if($connected_kiosk_id != 0){ // Kiosk Game
                        $deposit_amount = $total_credit + $basic_bonus + $special_bonus;
                        $username = Contact::find($contact_id)->name;
                        $resp = $this->gameUtil->deposit($connected_kiosk_id, $username, $deposit_amount);
                        if($resp['success'] == false) { // Player name exist
                            return $resp;
                        }
                    }

                    //end Deposit

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
                } else {
                    foreach($products as $product){
                        $basic_bonus = 0;
                        $special_bonus = 0;
                        if($bonus_variation_id != -1){
                            if($bonus_name === 'Bonus') {
                                $special_bonus = $product['amount'] * $bonus_amount / 100;
                            } else {
                                $special_bonus = $bonus_amount;
                            }
                        } else if($no_bonus == 0 && Contact::find($contact_id)->name != 'Unclaimed Trans')  {
                            $basic_bonus = $product['amount'] * $bonus_rate / 100;
//                            $basic_bonus = 0;
                        }
                        $payments = [];
                        $payments[] = ['account_id' => $product['account_id'], 'method' => 'bank_transfer', 'amount' => $product['amount'], 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => 'credit', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
                            'is_return' => 0, 'transaction_no' => '', 'category_name' => 'Banking'];
                        if(!empty($basic_bonus) || !empty($special_bonus)){
                            if($bonus_variation_id == -1) {
                                $bonus_key = Account::where('business_id', $business_id)->where('name', 'Bonus Account')->get()[0]->id;
                                $query = Category::where('name', 'Banking');
                                $data = $query->get()[0];
                                $payments[] = ['account_id' => $bonus_key, 'method' => 'basic_bonus', 'amount' => $basic_bonus, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => 'credit', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
                                    'is_return' => 0, 'transaction_no' => '', 'category_name' => $data->name];
                            } else {
                                $bonus_key = Account::where('business_id', $business_id)->where('name', 'Bonus Account')->get()[0]->id;
                                $query = Category::where('name', 'Banking');
                                $data = $query->get()[0];
                                $payments[] = ['account_id' => $bonus_key, 'method' => 'free_credit', 'amount' => $special_bonus, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => 'credit', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
                                    'is_return' => 0, 'transaction_no' => '', 'category_name' => $data->name];
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
                        $temp_products = [];
                        $temp_products[] = $product;
                        $this->transactionUtil->createOrUpdateSellLines($transaction, $temp_products, $input['location_id']);

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
//                                    foreach( $input['payment'] as $i => $payment){
//                                        $input['payment'][$i]['paid_on'] = date('Y-m-d H:i:s', strtotime('today') - 1);
//                                    }
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
                            foreach ($temp_products as $product) {
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
                    }
                }

                $msg = '';
                $receipt = '';
                if ($input['status'] == 'draft' && $input['is_quotation'] == 0) {
                    $msg = trans("sale.draft_added");
                } else if ($input['status'] == 'draft' && $input['is_quotation'] == 1) {
                    $msg = trans("lang_v1.quotation_added");
                    if (!$is_direct_sale) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id);
                    } else {
                        $receipt = '';
                    }
                } else if ($input['status'] == 'draft' && $input['is_confirm_order'] == 1) {
                    $msg = trans("lang_v1.order_confirmed");
                    if (!$is_direct_sale) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id, null, false, true, 'confirm_order');
                    } else {
                        $receipt = '';
                    }
                } else if ($input['status'] == 'draft' && $input['is_presale_note'] == 1) {
                    $msg = trans("lang_v1.presale_noted");
                    if (!$is_direct_sale) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id, null, false, true, 'presale_note');
                    } else {
                        $receipt = '';
                    }
                } else if ($input['status'] == 'final') {
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
                }

                $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt ];
            } else {
                $output = ['success' => 0,
                            'msg' => trans("messages.something_went_wrong")
                        ];
            }
//        } catch (\Exception $e) {
//            DB::rollBack();
//
//            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
//            $msg = trans("messages.something_went_wrong");
//
//            if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
//                $msg = $e->getMessage();
//            }
//
//            $output = ['success' => 0,
//                            'msg' => $msg
//                        ];
//        }

        if (!$is_direct_sale) {
            return $output;
        } else {
            if ($input['status'] == 'draft') {
                if (isset($input['is_quotation']) && $input['is_quotation'] == 1) {
                    return redirect()
                        ->action('SellController@getQuotations')
                        ->with('status', $output);
                } else {
                    return redirect()
                        ->action('SellController@getDrafts')
                        ->with('status', $output);
                }
            } else {
                if (!empty($input['sub_type']) && $input['sub_type'] == 'repair') {
                    $redirect_url = $input['print_label'] == 1 ? action('\Modules\Repair\Http\Controllers\RepairController@printLabel', [$transaction->id]) : action('\Modules\Repair\Http\Controllers\RepairController@index');
                    return redirect($redirect_url)
                        ->with('status', $output);
                }
                return redirect()
                    ->action('SellController@index')
                    ->with('status', $output);
            }
        }
    }

    /**
     * Returns the content for the receipt
     *
     * @param  int  $business_id
     * @param  int  $location_id
     * @param  int  $transaction_id
     * @param string $printer_type = null
     *
     * @return array
     */
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // if (!auth()->user()->can('sell.update')) {
        //     abort(403, 'Unauthorized action.');
        // }

        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
            return back()
                ->with('status', ['success' => 0,
                    'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])]);
        }

        //Check if there is a open register, if no then redirect to Create Register screen.
        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action('CashRegisterController@create');
        }
        
        //Check if return exist then not allowed
        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', ['success' => 0,
                    'msg' => __('lang_v1.return_exist')]);
        }

        $business_id = request()->session()->get('user.business_id');
        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        
        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);
        $payment_types = $this->productUtil->payment_types();

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
                            // 'p.name as product_actual_name',
                            'p.name as p_name',
                            'p.account_id as account_id',
                            'p.category_id as category_id',
                            'p.type as product_type',
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
                            'transaction_sell_lines.unit_price_before_discount as unit_price_before_discount',
                            'transaction_sell_lines.unit_price_inc_tax as sell_price_inc_tax',
                            'transaction_sell_lines.id as transaction_sell_lines_id',
                            'transaction_sell_lines.quantity as quantity_ordered',
                            'transaction_sell_lines.sell_line_note as sell_line_note',
                            'transaction_sell_lines.parent_sell_line_id',
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
                $sell_details[$key]->account_name = Account::find($value->account_id)->name;
                $sell_details[$key]->is_service = Account::find($value->account_id)->is_service;

                //If modifier or combo sell line then unset
                if (!empty($sell_details[$key]->parent_sell_line_id)) {
                    unset($sell_details[$key]);
                } else {
                    if ($transaction->status != 'final') {
                        $actual_qty_avlbl = $value->qty_available - $value->quantity_ordered;
                        $sell_details[$key]->qty_available = $actual_qty_avlbl;
                        $value->qty_available = $actual_qty_avlbl;
                    }

                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);

                    //Add available lot numbers for dropdown to sell lines
                    $lot_numbers = [];
                    if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                        $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($value->variation_id, $business_id, $location_id);
                        foreach ($lot_number_obj as $lot_number) {
                            //If lot number is selected added ordered quantity to lot quantity available
                            if ($value->lot_no_line_id == $lot_number->purchase_line_id) {
                                $lot_number->qty_available += $value->quantity_ordered;
                            }

                            $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                            $lot_numbers[] = $lot_number;
                        }
                    }
                    $sell_details[$key]->lot_numbers = $lot_numbers;
                    
                    if (!empty($value->sub_unit_id)) {
                        $value = $this->productUtil->changeSellLineUnit($business_id, $value);
                        $sell_details[$key] = $value;
                    }

                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);

                    if ($this->transactionUtil->isModuleEnabled('modifiers')) {
                        //Add modifier details to sel line details
                        $sell_line_modifiers = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
                            ->where('children_type', 'modifier')
                            ->get();
                        $modifiers_ids = [];
                        if (count($sell_line_modifiers) > 0) {
                            $sell_details[$key]->modifiers = $sell_line_modifiers;
                            foreach ($sell_line_modifiers as $sell_line_modifier) {
                                $modifiers_ids[] = $sell_line_modifier->variation_id;
                            }
                        }
                        $sell_details[$key]->modifiers_ids = $modifiers_ids;

                        //add product modifier sets for edit
                        $this_product = Product::find($sell_details[$key]->product_id);
                        if (count($this_product->modifier_sets) > 0) {
                            $sell_details[$key]->product_ms = $this_product->modifier_sets;
                        }
                    }

                    //Get details of combo items
                    if ($sell_details[$key]->product_type == 'combo') {
                        $sell_line_combos = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
                            ->where('children_type', 'combo')
                            ->get()
                            ->toArray();
                        if (!empty($sell_line_combos)) {
                            $sell_details[$key]->combo_products = $sell_line_combos;
                        }

                        //calculate quantity available if combo product
                        $combo_variations = [];
                        foreach ($sell_line_combos as $combo_line) {
                            $combo_variations[] = [
                                'variation_id' => $combo_line['variation_id'],
                                'quantity' => $combo_line['quantity'] / $sell_details[$key]->quantity_ordered,
                                'unit_id' => null
                            ];
                        }
                        $sell_details[$key]->qty_available =
                        $this->productUtil->calculateComboQuantity($location_id, $combo_variations);
                        
                        if ($transaction->status == 'final') {
                            $sell_details[$key]->qty_available = $sell_details[$key]->qty_available + $sell_details[$key]->quantity_ordered;
                        }
                        
                        $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($sell_details[$key]->qty_available, false, null, true);
                    }
                }
            }
        }

        $payment_lines = $this->transactionUtil->getPaymentDetails($id);
        //If no payment lines found then add dummy payment line.
        if (empty($payment_lines)) {
            $payment_lines[] = $this->dummyPaymentLine;
        }

        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $default_location = null;
        if (count($business_locations) == 1) {
            foreach ($business_locations as $id => $name) {
                $default_location = $id;
            }
        }

        //If brands, category are enabled then send else false.
        $bank_categories = (request()->session()->get('business.enable_category') == 1) ? Category::bankCatAndSubCategories($business_id) : false;
        $service_categories = (request()->session()->get('business.enable_category') == 1) ? Category::serviceCatAndSubCategories($business_id) : false;
        $bank_products = Product::forDropDown($business_id, 0);
        $service_products = Product::forDropDown($business_id, 1);

        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id, false);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id, false);
        }

        //If brands, category are enabled then send else false.
        $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
        $brands = (request()->session()->get('business.enable_brand') == 1) ? Brands::where('business_id', $business_id)
                    ->pluck('name', 'id')
                    ->prepend(__('lang_v1.all_brands'), 'all') : false;

        $change_return = $this->dummyPaymentLine;

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

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }
        //Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);

        $waiters = [];
        if ($this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff'])) {
            $waiters_enabled = true;
            $waiters = $this->productUtil->serviceStaffDropdown($business_id);
        }
        $redeem_details = [];
        if (request()->session()->get('business.enable_rp') == 1) {
            $redeem_details = $this->transactionUtil->getRewardRedeemDetails($business_id, $transaction->contact_id);

            $redeem_details['points'] += $transaction->rp_redeemed;
            $redeem_details['points'] -= $transaction->rp_earned;
        }

        $edit_discount = auth()->user()->can('edit_product_discount_from_pos_screen');
        $edit_price = auth()->user()->can('edit_product_price_from_pos_screen');

        $services = Account::where('business_id', $business_id)->where('is_service', 1)->get();
        $memberships = Membership::forDropdown($business_id);
        $bank_brands  = BankBrand::forDropdown($business_id);

        // Bonuses
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
            DB::raw("SUM( IF(AT.type='credit', AT.amount, -1*AT.amount) ) as balance"),
            'p.id as product_id',
            'p.name',
            'p.type',
            'p.enable_stock',
            'variations.id',
            'p.account_id',
            'p.category_id',
            'variations.name as variation',
            'variations.default_sell_price as selling_price',
        )
            ->orderBy('p.name', 'asc')
            ->get();
        foreach ($bonuses_data as $item) {
            $bonuses[] = $item;
        }

        $country_codes = CountryCode::forDropdown(false);

        return view('sale_pos_deposit.edit')
            ->with(compact('business_details', 'taxes', 'payment_types', 'default_location', 'walk_in_customer', 'sell_details', 'transaction', 'payment_lines', 'location_printer_type', 'shortcuts', 'commission_agent', 'bank_categories',
            'service_categories',
            'bank_products', 'services', 'memberships', 'bank_brands',
            'service_products', 'pos_settings', 'change_return', 'types', 'customer_groups', 'brands', 'accounts', 'price_groups', 'waiters', 'redeem_details', 'edit_price', 'edit_discount', 'bonuses', 'country_codes'));
    }

    /**
     * Update the specified resource in storage.
     * TODO: Add edit log.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
//        exit;
        // return 0;
        // if (!auth()->user()->can('sell.update') && !auth()->user()->can('direct_sell.access')) {
        //     abort(403, 'Unauthorized action.');
        // }
        
//        try {
            $input = $request->except('_token');


            $is_direct_sale = false;
            if (!empty($input['products'])) {
                //Get transaction value before updating.
                $transaction_before = Transaction::find($id);
                
                $business_id = $request->session()->get('user.business_id');
                $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);
                if(strtotime($transaction_before->transaction_date) < strtotime('1 day ago') && !$is_admin){
                    $output = ['success' => 0,
                            'msg' => trans("messages.transaction_date_error")
                        ];
                }
                else{

                    $status_before =  $transaction_before->status;
                    $rp_earned_before = $transaction_before->rp_earned;
                    $rp_redeemed_before = $transaction_before->rp_redeemed;

                    if ($transaction_before->is_direct_sale == 1) {
                        $is_direct_sale = true;
                    }

                    if(empty($input['bank_changed'])){
                        $input['bank_in_time'] = null;
                    }

                    //Check Customer credit limit
                    $is_credit_limit_exeeded = $this->transactionUtil->isCustomerCreditLimitExeeded($input, $id);

                    if ($is_credit_limit_exeeded !== false) {
                        $credit_limit_amount = $this->transactionUtil->num_f($is_credit_limit_exeeded, true);
                        $output = ['success' => 0,
                                    'msg' => __('lang_v1.cutomer_credit_limit_exeeded', ['credit_limit' => $credit_limit_amount])
                                ];
                        if (!$is_direct_sale) {
                            return $output;
                        } else {
                            return redirect()
                                ->action('SellController@index')
                                ->with('status', $output);
                        }
                    }

                    //Check if there is a open register, if no then redirect to Create Register screen.
                    if (!$is_direct_sale && $this->cashRegisterUtil->countOpenedRegister() == 0) {
                        return redirect()->action('CashRegisterController@create');
                    }

                    // $business_id = $request->session()->get('user.business_id');
                    $user_id = $request->session()->get('user.id');
                    $commsn_agnt_setting = $request->session()->get('business.sales_cmsn_agnt');

                    $discount = ['discount_type' => $input['discount_type'],
                                    'discount_amount' => $input['discount_amount']
                                ];
                    $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount);

                    if (!empty($request->input('transaction_date'))) {
                        $input['transaction_date'] = $this->productUtil->uf_date($request->input('transaction_date'), true);
                    }

                    $input['commission_agent'] = !empty($request->input('commission_agent')) ? $request->input('commission_agent') : null;
                    if ($commsn_agnt_setting == 'logged_in_user') {
                        $input['commission_agent'] = $user_id;
                    }

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

                    if ($is_direct_sale && $status_before == 'draft') {
                        $input['invoice_scheme_id'] = $request->input('invoice_scheme_id');
                    }
                    //check if service
                    $service_id = -1;
                    foreach ($input['products'] as $product) {
                        if(Account::find($product['account_id'])->is_service){
                            $service_id = $product['account_id'];
                            break;
                        }
                    }

                    $input['game_id'] = GameId::where('service_id', $service_id)->where('contact_id', $contact_id)->get()->first()->cur_game_id;

                    //Begin transaction
                    DB::beginTransaction();

                    $transaction = $this->transactionUtil->updateSellTransaction($id, $business_id, $input, $invoice_total, $user_id);
                    ActivityLogger::activity("Edited transaction, ticket # ".$transaction->invoice_no);

                    // //Update Sell lines
                    $deleted_lines = $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id'], true, $status_before);
                    //Update update lines
                    $service_id_arr = [];
                    if (!$is_direct_sale && !$transaction->is_suspend) {
                        $products = $input['products'];
                        $contact_id = request()->get('customer_id');
                        $bonus_rate = CountryCode::find(Contact::find($contact_id)->country_code_id)->basic_bonus_percent;
                        $payments = [];
                        // sum to one transaction
                        $bonus_amount = 0;
                        $bonus_name = '';
                        $bonus_variation_id = $input['bonus_variation_id'];
                        $bonuses = $this->getBonuses($business_id);
                        foreach ($bonuses as $bonus){
                            if($bonus->id == $bonus_variation_id) {
                                $bonus_name = $bonus->name;
                                $bonus_amount = $bonus->selling_price;
                            }
                        }
                        $no_bonus = Contact::find($contact_id)->no_bonus;
                        $total_credit = 0;


//                        $payment_data = [];
//                        foreach ($products as $product) {
//                            if($product['category_id'] == 66) {
//                                $total_credit += $product['amount'];
//                            }
//                            if(!isset($payment_data[$product['account_id']]['amount'] )){
//                                $p_name = $product['p_name'];
//                                $payment_data[$product['account_id']]['amount'] = $product['amount'];
//                                $payment_data[$product['account_id']]['category_id'] = $product['category_id'];
//                                $payment_data[$product['account_id']]['p_name'] = $p_name;
//                            }
//                            else{
//                                $payment_data[$product['account_id']]['amount'] += $product['amount'];
//                            }
//                        }
                        $payment_data = [];
                        $new_payment_item = [];

                        foreach ($products as $key => $product) {
                            if(empty($product['payment_for']) || Contact::find($product['payment_for'])->is_default == 1)
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
//                        print_r($payment_data);exit;


                        $basic_bonus = 0;
                        $special_bonus = 0;
                        if($bonus_variation_id != -1){
                            if($bonus_name === 'Bonus') {
                                $special_bonus = $total_credit * $bonus_amount / 100;
                            } else {
                                $special_bonus = $bonus_amount;
                            }
                        } else if($no_bonus == 0 && Contact::find($contact_id)->name != 'Unclaimed Trans') {
                            $basic_bonus = floor($total_credit * $bonus_rate / 100);
//                            $basic_bonus = 0;
                        }
                        foreach ($payment_data as $payment){
                            $data = Category::where('id', $payment['category_id'])->get()->first();
                            if($data->name == 'Banking'){
                                $card_type = 'credit';
                                $method = 'bank_transfer';
                                $payment_id = TransactionPayment::where('transaction_id', $transaction->id)->where('method', 'bank_transfer')->where('account_id', $payment['account_id'])->get()->first()->id;
                                $payments[] = ['account_id' => $payment['account_id'], 'method' => $method, 'amount' => $payment['amount'], 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => $card_type, 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
                                    'is_return' => 0, 'transaction_no' => '', 'category_name' => $data->name, 'payment_for' => $payment['payment_for'] , 'payment_id' => $payment_id];
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

                        //Add change return
                        $change_return = $this->dummyPaymentLine;
                        $change_return['amount'] = $input['change_return'];
                        $change_return['is_return'] = 1;
                        if (!empty($input['change_return_id'])) {
                            $change_return['id'] = $input['change_return_id'];
                        }
                        $input['payment'][] = $change_return;


                        if(!(auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Admin') || auth()->user()->hasRole('Superadmin'))){
                            if(!$this->isShiftClosed($business_id)){
                                foreach( $input['payment'] as $i => $payment){
                                    $input['payment'][$i]['paid_on'] = date('Y-m-d H:i:s', strtotime('today') - 1);
                                }
                            }
                        }

                        $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
//                        exit;

                        //Update cash register
                        // $this->cashRegisterUtil->updateSellPayments($status_before, $transaction, $input['payment']);
                    }

                    if ($request->session()->get('business.enable_rp') == 1) {
                        $this->transactionUtil->updateCustomerRewardPoints($contact_id, $transaction->rp_earned, $rp_earned_before, $transaction->rp_redeemed, $rp_redeemed_before);
                    }

                    //Update payment status
                    $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

                    //Update product stock
                    $this->productUtil->adjustProductStockForInvoice($status_before, $transaction, $input);

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
                    $this->transactionUtil->adjustMappingPurchaseSell($status_before, $transaction, $business, $deleted_lines);
                    
                    if ($this->transactionUtil->isModuleEnabled('tables')) {
                        $transaction->res_table_id = request()->get('res_table_id');
                        $transaction->save();
                    }
                    if ($this->transactionUtil->isModuleEnabled('service_staff')) {
                        $transaction->res_waiter_id = request()->get('res_waiter_id');
                        $transaction->save();
                    }
                    $log_properties = [];
                    if (isset($input['repair_completed_on'])) {
                        $completed_on = !empty($input['repair_completed_on']) ? $this->transactionUtil->uf_date($input['repair_completed_on'], true) : null;
                        if ($transaction->repair_completed_on != $completed_on) {
                            $log_properties['completed_on_from'] = $transaction->repair_completed_on;
                            $log_properties['completed_on_to'] = $completed_on;
                        }
                    }

                    //Set Module fields
                    if (!empty($input['has_module_data'])) {
                        $this->moduleUtil->getModuleData('after_sale_saved', ['transaction' => $transaction, 'input' => $input]);
                    }

                    if (!empty($input['update_note'])) {
                        $log_properties['update_note'] = $input['update_note'];
                    }

                    Media::uploadMedia($business_id, $transaction, $request, 'documents');

                    activity()
                    ->performedOn($transaction)
                    ->withProperties($log_properties)
                    ->log('edited');

                    DB::commit();
                        
                    $msg = '';
                    $receipt = '';

                    if ($input['status'] == 'draft' && $input['is_quotation'] == 0) {
                        $msg = trans("sale.draft_added");
                    } elseif ($input['status'] == 'draft' && $input['is_quotation'] == 1) {
                        $msg = trans("lang_v1.quotation_updated");
                        if (!$is_direct_sale) {
                            $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id);
                        } else {
                            $receipt = '';
                        }
                    } elseif ($input['status'] == 'final') {
                        $msg = trans("sale.pos_sale_updated"). "<br/> Ticket number " . $transaction->invoice_no;
                        $msg .= "<br/><br/>Updated balance for";
                        foreach ($service_id_arr as $service_id) {
                            $account = Account::leftjoin('account_transactions as AT', function ($join) {
                                $join->on('AT.account_id', '=', 'accounts.id');
                                $join->whereNull('AT.deleted_at');
                            })
                                ->where('accounts.id', $service_id)
                                ->where('business_id', $business_id)
                                ->select(['name', DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")])
                                ->groupBy('accounts.id')->get()->first();
                            $msg .= "<br/>".$account->name.' = <span style="font-weight: 800;color: black;">'.number_format( $account->balance, 2, '.', '').'</span>';
                        }

                        if (!$is_direct_sale && !$transaction->is_suspend) {
                            $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id);
                        } else {
                            $receipt = '';
                        }
                    }

                    $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt ];
                }
            } else {
                $output = ['success' => 0,
                            'msg' => trans("messages.something_went_wrong")
                        ];
            }
//        } catch (\Exception $e) {
//            DB::rollBack();
//            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
//            $output = ['success' => 0,
//                            'msg' => __('messages.something_went_wrong')
//                        ];
//        }

        if (!$is_direct_sale) {
            return $output;
        } else {
            if ($input['status'] == 'draft') {
                if (isset($input['is_quotation']) && $input['is_quotation'] == 1) {
                    return redirect()
                        ->action('SellController@getQuotations')
                        ->with('status', $output);
                } else {
                    return redirect()
                        ->action('SellController@getDrafts')
                        ->with('status', $output);
                }
            } else {
                if (!empty($transaction->sub_type) && $transaction->sub_type == 'repair') {
                    return redirect()
                        ->action('\Modules\Repair\Http\Controllers\RepairController@index')
                        ->with('status', $output);
                }
                return $output;
                // return redirect()
                //     ->action('SellController@index')
                //     ->with('status', $output);
            }
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
        if (!auth()->user()->can('sell.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                //Check if return exist then not allowed
                if ($this->transactionUtil->isReturnExist($id)) {
                    $output = [
                        'success' => false,
                        'msg' => __('lang_v1.return_exist')
                    ];
                    return $output;
                }

                $business_id = request()->session()->get('user.business_id');
                $transaction = Transaction::where('id', $id)
                            ->where('business_id', $business_id)
                            ->where('type', 'sell')
                            ->with(['sell_lines'])
                            ->first();

                //Begin transaction
                DB::beginTransaction();

                if (!empty($transaction)) {
                    //If status is draft direct delete transaction
                    if ($transaction->status == 'draft') {
                        $transaction->delete();
                    } else {
                        $deleted_sell_lines = $transaction->sell_lines;
                        $deleted_sell_lines_ids = $deleted_sell_lines->pluck('id')->toArray();
                        $this->transactionUtil->deleteSellLines(
                            $deleted_sell_lines_ids,
                            $transaction->location_id
                        );

                        $this->transactionUtil->updateCustomerRewardPoints($transaction->contact_id, 0, $transaction->rp_earned, 0, $transaction->rp_redeemed);

                        $transaction->status = 'draft';
                        $business = ['id' => $business_id,
                                'accounting_method' => request()->session()->get('business.accounting_method'),
                                'location_id' => $transaction->location_id
                            ];

                        $this->transactionUtil->adjustMappingPurchaseSell('final', $transaction, $business, $deleted_sell_lines_ids);

                        //Delete Cash register transactions
                        $transaction->cash_register_payments()->delete();

                        $transaction->delete();
                    }
                }

                //Delete account transactions
                AccountTransaction::where('transaction_id', $transaction->id)->delete();

                DB::commit();
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.sale_delete_success')
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output['success'] = false;
                $output['msg'] = trans("messages.something_went_wrong");
            }

            return $output;
        }
    }

    /**
     * Returns the HTML row for a product in POS
     *
     * @param  int  $variation_id
     * @param  int  $location_id
     * @return \Illuminate\Http\Response
     */
    public function getProductRow($variation_id, $location_id)
    {
        $output = [];

        try {
            $is_product_any = request()->get('is_product_any');
            $row_count = request()->get('product_row');
            $row_count = $row_count + 1;
            $is_direct_sell = false;
            if (request()->get('is_direct_sell') == 'true') {
                $is_direct_sell = true;
            }

            $business_id = request()->session()->get('user.business_id');

            $business_details = $this->businessUtil->getDetails($business_id);
            $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

            $check_qty = !empty($pos_settings['allow_overselling']) ? false : true;
            $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id, $location_id, $check_qty);
            $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available, false, null, true);

            $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit_id, false, $product->product_id);

            //Get customer group and change the price accordingly
            $customer_id = request()->get('customer_id', null);
            $cg = $this->contactUtil->getCustomerGroup($business_id, $customer_id);
            $percent = (empty($cg) || empty($cg->amount)) ? 0 : $cg->amount;
            $product->default_sell_price = $product->default_sell_price + ($percent * $product->default_sell_price / 100);
            $product->sell_price_inc_tax = $product->sell_price_inc_tax + ($percent * $product->sell_price_inc_tax / 100);

            $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);

            $enabled_modules = $this->transactionUtil->allModulesEnabled();

            //Get lot number dropdown if enabled
            $lot_numbers = [];
            if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($variation_id, $business_id, $location_id, true);
                foreach ($lot_number_obj as $lot_number) {
                    $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                    $lot_numbers[] = $lot_number;
                }
            }
            $product->lot_numbers = $lot_numbers;

            $price_group = request()->input('price_group');
            if (!empty($price_group)) {
                $variation_group_prices = $this->productUtil->getVariationGroupPrice($variation_id, $price_group, $product->tax_id);
                
                if (!empty($variation_group_prices['price_inc_tax'])) {
                    $product->sell_price_inc_tax = $variation_group_prices['price_inc_tax'];
                    $product->default_sell_price = $variation_group_prices['price_exc_tax'];
                }
            }

            $output['success'] = true;

            $waiters = null;
            if ($this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff'])) {
                $waiters_enabled = true;
                $waiters = $this->productUtil->serviceStaffDropdown($business_id, $location_id);
            }

            $output['no_bonus'] = $product->no_bonus;

            if (request()->get('type') == 'sell-return') {
                $output['html_content'] =  view('sell_return.partials.product_row')
                            ->with(compact('product', 'row_count', 'tax_dropdown', 'enabled_modules', 'sub_units'))
                            ->render();
            } else {
                $is_cg = !empty($cg->id) ? true : false;
                $is_pg = !empty($price_group) ? true : false;
                $discount = $this->productUtil->getProductDiscount($product, $business_id, $location_id, $is_cg, $is_pg);
                
                if ($is_direct_sell) {
                    $edit_discount = auth()->user()->can('edit_product_discount_from_sale_screen');
                    $edit_price = auth()->user()->can('edit_product_price_from_sale_screen');
                } else {
                    $edit_discount = auth()->user()->can('edit_product_discount_from_pos_screen');
                    $edit_price = auth()->user()->can('edit_product_price_from_pos_screen');
                }
                $amount = 0;
                if($product->no_bonus)
                    $amount = request()->get('credit');
                else if(request()->get('product_type') != 0){
                    $amount = request()->get('amount');
                }
                $cnt = GameId::where('service_id', $product->account_id)->where('contact_id', $customer_id)->count();
                // print_r($game_id);exit;
                if($cnt > 0)
                    $game_id = GameId::where('service_id', $product->account_id)->where('contact_id', $customer_id)->get()->first()->cur_game_id;
                else
                    $game_id = null;
                $account_name = Account::find($product->account_id)->name;
                $is_service = Account::find($product->account_id)->is_service;
                $is_first_service = request()->get('is_first_service');

                $output['html_content'] =  view('sale_pos_deposit.product_row')
                            ->with(compact('product', 'account_name', 'is_service', 'is_first_service', 'is_product_any', 'row_count', 'tax_dropdown', 'enabled_modules', 'pos_settings', 'sub_units', 'discount', 'waiters', 'edit_discount', 'edit_price', 'amount', 'game_id'))
                            ->render();
            }
            
            $output['enable_sr_no'] = $product->enable_sr_no;

            if ($this->transactionUtil->isModuleEnabled('modifiers')  && !$is_direct_sell) {
                $this_product = Product::where('business_id', $business_id)
                                        ->find($product->product_id);
                if (count($this_product->modifier_sets) > 0) {
                    $product_ms = $this_product->modifier_sets;
                    $output['html_modifier'] =  view('restaurant.product_modifier_set.modifier_for_product')
                    ->with(compact('product_ms', 'row_count'))->render();
                }
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output['success'] = false;
            $output['msg'] = __('lang_v1.item_out_of_stock');
        }

        return $output;
    }

    public function getUpdatePosRow($transaction_id) {
        $transaction_payment_id = request()->get('transaction_payment_id');
        $is_first_service = request()->get('is_first_service');
        $business_id = request()->session()->get('user.business_id');
        $service_accounts = Account::where('business_id', $business_id)
            ->where('is_service', 1)
            ->NotClosed()
            ->pluck('name', 'id')
            ->prepend(__('lang_v1.none'), '');
        $contact_id = Transaction::find($transaction_id)->contact_id;
        $disabled_data = null;
        if(Contact::find($contact_id)->contact_id == "UNCLAIM"){
            $pos_type = 'unclaimed';
            $default_request_type = 2;
        }
        else{
            $default_request_type = 1;
//            $request_types = EssentialsRequestType::forDropdown($business_id);
            $transaction = Transaction::find($transaction_id);
            if($transaction->type == 'sell'){
                $pos_type = 'deposit';
                $disabled_data = [ 'credit' => false, 'debit' => true, 'free_credit' => true, 'basic_bonus' => true, 'service_credit' => true, 'service_debit' => true];
            }
            else{
                if($transaction->sub_type == 'withdraw_to_customer'){
                    $pos_type = 'withdraw';
                    $disabled_data = [ 'credit' => true, 'debit' => false, 'free_credit' => true, 'basic_bonus' => true, 'service_credit' => true, 'service_debit' => true];
                } else if($transaction->sub_type == 'game_credit_transfer'){
                    $pos_type = 'GTransfer';
                    $disabled_data = [ 'credit' => true, 'debit' => true, 'free_credit' => true, 'basic_bonus' => true, 'service_credit' => false, 'service_debit' => true];
                    $default_request_type = 4;
                } else{
                    $pos_type = 'Deduction';
                    $default_request_type = 5;
                    $disabled_data = [ 'credit' => true, 'debit' => true, 'free_credit' => true, 'basic_bonus' => true, 'service_credit' => false, 'service_debit' => true];
                }
            }
        }
        $request_types = EssentialsRequestType::forDropdown($business_id, $pos_type);

        $user_id = session()->get('user.id');
        $contacts = Contact::where('business_id', $business_id);
        $selected_contacts = User::isSelectedContacts($user_id);

        if ($selected_contacts) {
            $contacts->join('user_contact_access AS uca', 'contacts.id', 'uca.contact_id')
                ->where('uca.user_id', $user_id);
        }
        $to_users = $contacts->pluck('contact_id', 'id');

        if($pos_type == 'deposit' || $pos_type == 'withdraw') {
            $sql = Account::where('business_id', $business_id)
                ->where('is_service', 0)
                ->where('name', '!=', 'Bonus Account')
                ->NotClosed();
            $sql->where('is_safe', 0);
            $bank_accounts = $sql->pluck('name', 'id');
            $selected_bank_id = TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'bank_transfer')->get()->first()->account_id;
            $html = view('sale_pos_deposit.update_pos_row')->with(compact('transaction_id', 'transaction_payment_id', 'is_first_service', 'bank_accounts', 'selected_bank_id', 'request_types', 'service_accounts', 'pos_type',
                'disabled_data', 'default_request_type', 'to_users'))->render();
        } else {
            $html = view('sale_pos_deposit.update_pos_row')->with(compact('transaction_id', 'transaction_payment_id', 'is_first_service', 'request_types', 'service_accounts', 'pos_type',
                'disabled_data', 'default_request_type', 'to_users'))->render();
        }

        return $html;
    }

    public function getUpdatePosData($transaction_id){
        $total_credit = request()->get('total_credit');
        $bonus_amount = 0;
        $bonus_name = '';
        $bonus_variation_id = Transaction::find($transaction_id)->bonus_variation_id;
        $business_id = request()->session()->get('user.business_id');
        $bonuses = $this->getBonuses($business_id);
        foreach ($bonuses as $bonus){
            if($bonus->id == $bonus_variation_id) {
                $bonus_name = $bonus->name;
                $bonus_amount = $bonus->selling_price;
            }
        }
        $contact_id = Transaction::find($transaction_id)->contact_id;
        $no_bonus = Contact::find($contact_id)->no_bonus;
        $data = ['basic_bonus' => 0, 'special_bonus' => 0, 'service_debit' => 0];
        if($bonus_variation_id != -1){
            if($bonus_name === 'Bonus') {
                $data['special_bonus'] = $total_credit * $bonus_amount / 100;
            } else {
                $data['special_bonus'] = $bonus_amount;
            }
        } else if($no_bonus == 0) {
            $bonus_rate = CountryCode::find(Contact::find($contact_id)->country_code_id)->basic_bonus_percent;
            $data['basic_bonus'] = floor($total_credit * $bonus_rate / 100);
        }
        $data['service_debit'] = $total_credit + $data['basic_bonus'] + $data['special_bonus'];
        return ['data' => $data];
    }

    public function getEnablePosData($transaction_id){
        $data = [];
        if(TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'bank_transfer')->where('card_type','credit')->count() > 0){
            $data[] = ['key' => 'credit', 'amount' => number_format((float)TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'bank_transfer')->where('card_type','credit')->get()->first()->amount, 2, '.', '')];
        }
        if(TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'free_credit')->where('card_type','credit')->count() > 0){
            $data[] = ['key' => 'free_credit', 'amount' => number_format((float)TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'free_credit')->where('card_type','credit')->get()->first()->amount, 2, '.', '')];
        }
        if(TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'basic_bonus')->where('card_type','credit')->count() > 0){
            $data[] = ['key' => 'basic_bonus', 'amount' => number_format((float)TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'basic_bonus')->where('card_type','credit')->get()->first()->amount, 2, '.', '')];
        }
        if(TransactionPayment::where('transaction_id', $transaction_id)->where('method', '!=', 'service_transfer')->where('card_type','debit')->count() > 0){
            $data[] = ['key' => 'debit', 'amount' => number_format((float)TransactionPayment::where('transaction_id', $transaction_id)->where('method', '!=', 'service_transfer')->where('card_type','debit')->get()->first()->amount, 2, '.', '')];
        }
//        if(TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'service_transfer')->where('card_type','credit')->count() > 0){
//            $data[] = 'service_credit';
//        }
        if(TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'service_transfer')->where('card_type','debit')->count() > 0){
            $data[] = ['key' => 'service_debit', 'amount' => 0];
        }
        return ['data' => $data];
    }

    public function getGameIds(){
        $contact_id = request()->get('contact_id');
        $service_id = request()->get('service_id');
        $data = [];
        if($contact_id != -1 && $service_id != -1){
            $row = GameId::where('contact_id', $contact_id)->where('service_id', $service_id)->get()->first();
            if(!empty($row->cur_game_id)){
                $data[] = [ 'type' => 'cur_game_id', 'game_id' => $row->cur_game_id];
            }
            if(!empty($row->old_game_id)){
                $data[] = [ 'type' => 'old_game_id', 'game_id' => $row->old_game_id];
            }
        }
        return ['data' => $data];
    }

    /**
     * Returns the HTML row for a payment in POS
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getPaymentRow(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        
        $row_index = $request->input('row_index');
        $removable = true;
        $payment_types = $this->productUtil->payment_types();

        $payment_line = $this->dummyPaymentLine;

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }

        return view('sale_pos_deposit.partials.payment_row')
            ->with(compact('payment_types', 'row_index', 'removable', 'payment_line', 'accounts'));
    }

    public function getPaymentRows(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $input = $request->except('token');

        $products = $input['products'];
        $payment_data = [];
        $bonus_amount = 0;
        $contact_id = request()->get('customer_id');
        $bonus_rate = CountryCode::find(Contact::find($contact_id)->country_code_id)->basic_bonus_percent;
        $is_service = 1;
        //check if service
        foreach ($products as $product) {
            if(Account::find($product['account_id'])->is_service){
                $is_service = 1;
                break;
            }
        }
        $payment_lines = [];
        // sum to one transaction
        foreach ($products as $product) {
            if(!isset($payment_data[$product['account_id']]['amount'] )){
                $p_name = $product['p_name'];
                $payment_data[$product['account_id']]['amount'] = $product['amount'];
                $payment_data[$product['account_id']]['category_id'] = $product['category_id'];
                $payment_data[$product['account_id']]['p_name'] = $p_name;
                if($product['category_id'] == 66){
                    $bonus_amount += $product['amount'] * $bonus_rate / 100;
                }
            }
            else{
                $p_name = $product['p_name'];
                $payment_data[$product['account_id']]['amount'] += $product['amount'];
                if($p_name != 'Bonus'){
                    $bonus_amount += $product['amount'] * $bonus_rate / 100;
                }
            }
        }
        $is_free_credit = 0;
        foreach ($payment_data as $key => $payment){
            $query = Category::where('id', $payment['category_id']);
            $data = $query->get()[0];
            $account_data = Account::find($key);
            if($data->name == 'Banking'){
                $card_type = 'credit';
                if($account_data->name == 'Bonus Account'){
                    $method = 'free_credit';
                    $is_free_credit = 1;
                }
                else
                    $method = 'bank_transfer';
            } else {
                $method = 'service_transfer';
                $card_type = 'debit';
            }
            $payment_lines[] = ['account_id' => $key, 'method' => $method, 'amount' => $payment['amount'], 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => $card_type, 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
                'is_return' => 0, 'transaction_no' => '', 'category_name' => $data->name];
        }
        if(!empty($bonus_amount) && !$is_free_credit){
            $bonus_key = Account::where('business_id', $business_id)->where('name', 'Bonus Account')->get()[0]->id;
            $query = Category::where('name', 'Banking');
            $data = $query->get()[0];
            $payment_lines[] = ['account_id' => $bonus_key, 'method' => 'basic_bonus', 'amount' => $bonus_amount, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
                'is_return' => 0, 'transaction_no' => '', 'category_name' => $data->name];
        }

        $payment_types = $this->productUtil->payment_types();
        //Accounts
        $accounts = Account::forDropdown($business_id, true, false);

        return view('sale_pos_deposit.partials.payment_rows')->with(compact('payment_lines', 'payment_types', 'accounts'));
    }


    /**
     * Returns recent transactions
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getRecentTransactions(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $transaction_status = $request->get('status');

        $register = $this->cashRegisterUtil->getCurrentCashRegister($user_id);

        $query = Transaction::where('business_id', $business_id)
                        ->where('transactions.created_by', $user_id)
                        ->where('transactions.type', 'sell')
                        ->where('is_direct_sale', 0);

        if ($transaction_status == 'final') {
            if (!empty($register->id)) {
                $query->leftjoin('cash_register_transactions as crt', 'transactions.id', '=', 'crt.transaction_id')
                ->where('crt.cash_register_id', $register->id);
            }
        }

        if ($transaction_status == 'quotation') {
            $query->where('transactions.status', 'draft')
                ->where('is_quotation', 1);
        } elseif ($transaction_status == 'draft') {
            $query->where('transactions.status', 'draft')
                ->where('is_quotation', 0);
        } else {
            $query->where('transactions.status', $transaction_status);
        }

        $transactions = $query->orderBy('transactions.created_at', 'desc')
                            ->groupBy('transactions.id')
                            ->select('transactions.*')
                            ->with(['contact'])
                            ->limit(10)
                            ->get();

        return view('sale_pos_deposit.partials.recent_transactions')
            ->with(compact('transactions'));
    }

    /**
     * Prints invoice for sell
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function printInvoice(Request $request, $transaction_id)
    {
        if (request()->ajax()) {
            try {
                $output = ['success' => 0,
                        'msg' => trans("messages.something_went_wrong")
                        ];

                $business_id = $request->session()->get('user.business_id');
            
                $transaction = Transaction::where('business_id', $business_id)
                                ->where('id', $transaction_id)
                                ->with(['location'])
                                ->first();

                if (empty($transaction)) {
                    return $output;
                }

                $printer_type = 'browser';
                if (!empty(request()->input('check_location')) && request()->input('check_location') == true) {
                    $printer_type = $transaction->location->receipt_printer_type;
                }

                $is_package_slip = !empty($request->input('package_slip')) ? true : false;

                $receipt = $this->receiptContent($business_id, $transaction->location_id, $transaction_id, $printer_type, $is_package_slip, false);

                if (!empty($receipt)) {
                    $output = ['success' => 1, 'receipt' => $receipt];
                }
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
                
                $output = ['success' => 0,
                        'msg' => trans("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }

    /**
     * Gives suggetion for product based on category
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getBankProductSuggestion(Request $request)
    {
        if ($request->ajax()) {
            $category_id = $request->get('category_id');
            $location_id = $request->get('location_id');
            $term = $request->get('term');

            $check_qty = false;
            $business_id = $request->session()->get('user.business_id');

            $products = Product::join('accounts', 'products.account_id', 'accounts.id')
                ->leftjoin('account_transactions as AT', function ($join) {
                    $join->on('AT.account_id', '=', 'accounts.id');
                    $join->whereNull('AT.deleted_at');
                })
                ->leftjoin( 'transactions as T',
                    'AT.transaction_id',
                    '=',
                    'T.id')
                ->groupBy('accounts.id')
                ->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier')
                ->where('products.is_inactive', 0)
                ->where('accounts.name', '!=', 'Bonus Account')
                ->where('products.not_for_selling', 0)
                ->orderBy('priority', 'ASC');

            //Include search
            if (!empty($term)) {
                $products->where(function ($query) use ($term) {
                    $query->where('products.name', 'like', '%' . $term .'%');
                    $query->orWhere('sku', 'like', '%' . $term .'%');
                    $query->orWhere('sub_sku', 'like', '%' . $term .'%');
                });
            }


            if ($category_id != 'all') {
                $products->where(function ($query) use ($category_id) {
                    $query->where('products.category_id', $category_id);
                    $query->orWhere('products.sub_category_id', $category_id);
                });
            }

            $products = $products->where(function ($q) {
                $q->where('T.payment_status', '!=', 'cancelled');
                $q->orWhere('T.payment_status', '=', null);
            });

            $products = $products->select(
                DB::raw("SUM( IF( accounts.shift_closed_at IS NULL OR AT.operation_date >= accounts.shift_closed_at,  IF( AT.type='credit', AT.amount, -1*AT.amount), 0) ) as balance"),
                'products.id',
                'products.name'
            )
                ->orderBy('products.name', 'asc')
                ->paginate(40);

            $business_details = $this->businessUtil->getDetails($business_id);
            $bonus_decimal = $business_details->bonus_decimal;
            return view('sale_pos_deposit.partials.bank_product_list')
                ->with(compact('products', 'bonus_decimal'));
        }
    }

    /**
     * Gives suggetion for product based on category
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getProductSuggestion(Request $request)
    {
        if ($request->ajax()) {
            $category_id = $request->get('category_id');
            $product_id = $request->get('product_id');
            $brand_id = $request->get('brand_id');
            $location_id = $request->get('location_id');
            $term = $request->get('term');
            $is_unclaimed = $request->get('is_unclaimed');
            $edit_page = $request->get('edit_page');

            $check_qty = false;
            $business_id = $request->session()->get('user.business_id');

            $products = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
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
                ->leftjoin( 'transactions as T',
                    'AT.transaction_id',
                    '=',
                    'T.id')
                ->groupBy('accounts.id')
                ->groupBy('variations.id')
                ->where('p.business_id', $business_id)
                ->where('p.type', '!=', 'modifier')
                ->where('p.is_inactive', 0)
                ->where('accounts.name', '!=', 'Bonus Account')
                ->where('p.not_for_selling', 0)
                ->where(function ($q) {
                    $q->where('T.payment_status', '!=', 'cancelled');
                    $q->orWhere('T.payment_status', '=', null);
                })
                ->orderBy('p.priority', 'ASC');

            //Include search
            if (!empty($term)) {
                $products->where(function ($query) use ($term) {
                    $query->where('p.name', 'like', '%' . $term .'%');
                    $query->orWhere('sku', 'like', '%' . $term .'%');
                    $query->orWhere('sub_sku', 'like', '%' . $term .'%');
                });
            }

            //Include check for quantity
            if ($check_qty) {
                $products->where('VLD.qty_available', '>', 0);
            }

            if ($category_id != 'all') {
                $products->where(function ($query) use ($category_id) {
                    $query->where('p.category_id', $category_id);
                    $query->orWhere('p.sub_category_id', $category_id);
                });
            }
            if ($brand_id != 'all') {
                $products->where('p.brand_id', $brand_id);
            }
            if (!empty($product_id)) {
                $products->where('p.id', $product_id);
            }



            $products = $products->select(
                DB::raw("SUM( IF( accounts.shift_closed_at IS NULL OR AT.operation_date >= accounts.shift_closed_at AND (!accounts.is_special_kiosk OR AT.sub_type IS NULL OR AT.sub_type != 'opening_balance'),  IF( AT.type='credit', AT.amount, -1*AT.amount), 0) )
                  * (1 - accounts.is_special_kiosk * 2) as balance"),
                'p.id as product_id',
                'p.name',
                'p.type',
                'p.enable_stock',
                'variations.id',
                'p.account_id',
                'p.category_id',
                'p.no_bonus',
                'variations.name as variation',
                'VLD.qty_available',
                'variations.default_sell_price as selling_price',
                'variations.sub_sku'
            )
                ->with(['media'])
                ->orderBy('p.name', 'asc')
                ->paginate(40);
//            return $products;
            $business_details = $this->businessUtil->getDetails($business_id);
            $bonus_decimal = $business_details->bonus_decimal;
            return view('sale_pos_deposit.partials.product_list')
                ->with(compact('products', 'bonus_decimal'));
        }
    }


    public function getBonusSuggestion(Request $request)
    {
        if ($request->ajax()) {
            $location_id = $request->get('location_id');
            $term = $request->get('term');
            $is_unclaimed = $request->get('is_unclaimed');
            $edit_page = $request->get('edit_page');

            $check_qty = false;
            $business_id = $request->session()->get('user.business_id');

            $products = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
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
            $products->where('accounts.name', '=', 'Bonus Account');

            //Include search
            if (!empty($term)) {
                $products->where(function ($query) use ($term) {
                    $query->where('p.name', 'like', '%' . $term .'%');
                    $query->orWhere('sku', 'like', '%' . $term .'%');
                    $query->orWhere('sub_sku', 'like', '%' . $term .'%');
                });
            }

            //Include check for quantity
            if ($check_qty) {
                $products->where('VLD.qty_available', '>', 0);
            }


            $products = $products->select(
                DB::raw("SUM( IF(AT.type='credit', AT.amount, -1*AT.amount) ) as balance"),
                'p.id as product_id',
                'p.name',
                'p.type',
                'p.enable_stock',
                'variations.id',
                'p.account_id',
                'p.category_id',
                'variations.name as variation',
                'VLD.qty_available',
                'variations.default_sell_price as selling_price',
                'variations.sub_sku'
            )
                ->with(['media'])
                ->orderBy('p.name', 'asc')
                ->paginate(40);

            return view('sale_pos_deposit.partials.product_list')
                ->with(compact('products'));
        }
    }

    public function updateGameID(){
        $contact_id = request()->get('contact_id');
        $service_id = request()->get('service_id');
        $game_id = request()->get('game_id');
        if(!empty($game_id)){
            if(GameId::where('contact_id', $contact_id)->where('service_id', $service_id)->count()){
                GameId::where('contact_id', $contact_id)->where('service_id', $service_id)->update(['cur_game_id' => $game_id]);
            }
            else
                GameId::create(['contact_id' => $contact_id, 'service_id' => $service_id, 'cur_game_id' => $game_id]);
        }
        return 1;
    }

    public function getGameID(){
        $contact_id = request()->get('contact_id');
        $service_id = request()->get('service_id');
        $game_id = null;
        if(GameId::where('contact_id', $contact_id)->where('service_id', $service_id)->count()){
            $game_id = GameId::where('contact_id', $contact_id)->where('service_id', $service_id)->get()->first()->cur_game_id;
        }
        if($game_id == null)
            $game_id = "UNDEFINED";
        return ['game_id' => $game_id];
    }

    public function getRemarks(){
        try{
            $customer = Contact::find(request()->get('customer_id'));
            $remarks1 = isset($customer->remarks1) ? $customer->remarks1 : null;
            $remarks2 = isset($customer->remarks2) ? $customer->remarks2 : null;
            $remarks3 = isset($customer->remarks3) ? $customer->remarks3 : null;
            $output = ['success' => 1, 'remarks1' => $remarks1, 'remarks2' => $remarks2, 'remarks3' => $remarks3];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0];
        }
        return $output;
    }

    public function getBasicBonusRate(){
        try{
            $customer = Contact::find(request()->get('customer_id'));
            $output = ['success' => 1, 'basic_bonus_rate' => CountryCode::find($customer->country_code_id)->basic_bonus_percent];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => 0];
        }
        return $output;
    }

    public function getLedger()
    {
        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $contact_id = request()->input('contact_id');
        $selected_bank = request()->input('selected_bank');
        $bank_list = Account::where('is_service', 0)->where('business_id', $business_id)->where('is_safe', 0)->where('is_closed', 0)->where('name', '!=', 'Bonus Account')->where('name', '!=', 'HQ')->get();
        if(empty($selected_bank))
            $selected_bank = $bank_list[0]->id;
        $transaction_types = explode(',', request()->input('transaction_types'));
//        $show_payments = request()->input('show_payments') == 'true' ? true : false;

        //Get transactions
        $query1 = Transaction::where('transactions.business_id', $business_id)
//            where('transactions.contact_id', $contact_id)
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
            ->join('contacts as c', 'c.id', 'transaction_payments.payment_for')
            ->join('accounts as a', 'a.id', 'transaction_payments.account_id')
            ->leftJoin('business_locations as bl', 't.location_id', '=', 'bl.id')
//            ->join('contacts as c', 'c.id', 't.contact_id')
            ->where('t.business_id', $business_id)
            ->where('t.status', '!=', 'draft');

        $start = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $end = date('Y-m-d H:i:s', strtotime('now'));
        $query1->where('transactions.transaction_date', '>=', $start)
            ->where('transactions.transaction_date', '<=', $end);

        if($selected_bank == 'GTransfer')
            $query2->where('t.sub_type', 'game_credit_transfer');
        else if($selected_bank == 'Deduction')
            $query2->whereIn('t.sub_type', ['game_credit_deduct', 'game_credit_addict']);
        $query2->where('transaction_date', '>=', $start)
            ->where('transaction_date', '<=', $end);
        $query2->orderBy('transaction_date', 'DESC');
        $query2->orderBy('invoice_no', 'DESC');
        $query2->orderBy('transaction_payments.id', 'ASC');

        $payments = $query2->select('transaction_payments.*', 't.id as transaction_id', 't.sub_type as sub_type', 't.document as document', 't.bank_in_time as bank_in_time', 't.transaction_date as transaction_date', 'bl.name as location_name', 't.type as transaction_type', 't.ref_no', 't.invoice_no'
            , 'transaction_payments.id as tp_id', 'transaction_payments.game_id as tp_game_id', 'c.id as contact_primary_key', 'c.contact_id as contact_id', 'c.is_default as is_default', 'a.id as account_id', 'a.name as account_name', 't.created_by as created_by')->get();

//        $total_deposit = $query2->where('t.type', 'sell')->where('transaction_payments.method', '!=', 'service_transfer')->where('transaction_payments.method','!=', 'bonus')->sum('transaction_payments.amount');
        $paymentTypes = $this->transactionUtil->payment_types();
        if($selected_bank == 'GTransfer' || $selected_bank == 'Deduction') {
            foreach ($payments as $payment) {
                $ref_no = in_array($payment->transaction_type, ['sell', 'sell_return']) ? $payment->invoice_no : $payment->ref_no;
                $user = User::find($payment->created_by);


                $game_data = GameId::where('contact_id', $payment->contact_primary_key)->where('service_id', $payment->account_id)->get();
                $game_id = null;
                if(count($game_data) >= 1){
                    $game_id = $game_data[0]->cur_game_id;
                }
                $item = [
                    'date' => date("Y-m-d H:i:s", strtotime($payment->transaction_date) ),
                    'ref_no' => $payment->payment_ref_no,
                    'type' => $this->transactionTypes['payment'],
                    'location' => $payment->location_name,
                    'contact_id' => $payment->contact_id,
                    'payment_method' => !empty($paymentTypes[$payment->method]) ? $paymentTypes[$payment->method] : '',
                    'others' => '<small>' . $ref_no . '</small>',
                    'bank_in_time' => $payment->bank_in_time,
                    'user' => $user['first_name'] . ' ' . $user['last_name'],
                    'service_name' => $payment->account_name,
                    'game_id' => $payment->tp_game_id,
                    'transaction_id' => $payment->transaction_id,
                    'is_default' => 0,
                    'is_edit_request' => 0
                ];
                if($payment->card_type == 'debit')
                    $item['service_debit'] = $payment->amount;
                else if($payment->card_type == 'credit')
                    $item['service_credit'] = $payment->amount;
                $document = $payment->document;
                if($document && isFileImage($document)) {
                    $document_path = env('AWS_IMG_URL').'/uploads/service_documents/' . $document;
                    $item['document_path'] = $document_path;
                }
                $ledger[] = $item;
            }
        } else {
            $ledger_by_payment = [];
            $payment_item = [];
            foreach ($payments as $payment) {
                if(empty($payment_item) || $payment_item['transaction_id'] != $payment->transaction_id ||
                    (!empty($payment_item['service_debit']) && $payment->card_type == 'debit' && $payment->method == 'service_transfer')){
                    if(!empty($payment_item)){
                        $ledger_by_payment[] = $payment_item;
                    }
                    if(!empty($payment_item['service_debit']) && $payment->card_type == 'debit' && $payment->method == 'service_transfer'){
                        $payment_item = [
                            'bank_id' => isset($payment_item['bank_id']) ? $payment_item['bank_id'] : -1,
                            'transaction_id' => $payment_item['transaction_id'],
                            'date' => $payment_item['date'],
                            'contact_id' => $payment->contact_id,
                            'account_name' => $payment_item['account_name'],
                            'is_first_service' => 0
                        ];
                    }
                    else {
                        $ref_no = in_array($payment->transaction_type, ['sell', 'sell_return']) ?  $payment->invoice_no :  $payment->ref_no;
                        $user = User::find($payment->created_by);
                        $payment_item = [
                            'date' => $payment->transaction_date,
                            'ref_no' => $payment->payment_ref_no,
                            'type' => $this->transactionTypes['payment'],
                            'location' => $payment->location_name,
                            'contact_id' => $payment->contact_id,
                            'payment_method' => !empty($paymentTypes[$payment->method]) ? $paymentTypes[$payment->method] : '',
                            'others' => '<small>' . $ref_no . '</small>',
                            'bank_in_time' => $payment->bank_in_time,
                            'user' => $user['first_name'].' '.$user['last_name'],
                            'is_default' => $payment->is_default,
                            'account_name' => $payment->account_name,
                            'transaction_id' => $payment->transaction_id
                        ];
                        if($payment->transaction_type == 'sell_return') {
                            $document = $payment->document;
                            if($document && isFileImage($document)) {
                                if ($payment->sub_type == null)
                                    $document_path = env('AWS_IMG_URL').'/uploads/account_documents/' . $document;
                                else
                                    $document_path = env('AWS_IMG_URL').'/uploads/service_documents/' . $document;
                                $payment_item['document_path'] = $document_path;
                            }
                        }
                    }
                }
                if($payment->card_type == 'credit'){
                    if($payment->method == 'bank_transfer')
                        $payment_item['credit'] = $payment->amount;
                    else if($payment->method == 'free_credit')
                        $payment_item['free_credit'] = $payment->amount;
                    else if($payment->method == 'basic_bonus')
                        $payment_item['basic_bonus'] = $payment->amount;
                    else if($payment->method == 'service_transfer')
                        $payment_item['service_credit'] = $payment->amount;
                }
                else if ($payment->card_type == 'debit'){
                    if($payment->method != 'service_transfer')
                        $payment_item['debit'] = $payment->amount;
                    else{
                        $payment_item['service_debit'] = $payment->amount;
                        $payment_item['transaction_payment_id'] = $payment->tp_id;
                        if(!isset($payment_item['is_first_service'])){
                            $payment_item['is_first_service'] = 1;
                        }
                    }
                }
                if(($payment->transaction_type == 'sell' || $payment->transaction_type == 'sell_return' ) && $payment->method == 'service_transfer'){
                    $payment_item['service_name'] = $payment->account_name;
                    $payment_item['game_id'] = $payment->tp_game_id;
                }
                if($payment->method == 'bank_transfer') {
                    $payment_item['bank_id'] = $payment->account_id;
                }
            }
            $ledger_by_payment[] = $payment_item;
//            print_r($ledger_by_payment);exit;


            foreach ($ledger_by_payment as $item){
                if( ( $selected_bank == 'free_credit' && isset($item['free_credit'])) || (isset($item['bank_id']) && $item['bank_id'] == $selected_bank)){
//                    $item['transaction_id'] = $transaction_id;
                    if(EssentialsRequest::where('transaction_id', $item['transaction_id'])->where('status', 'pending')->count() > 0)
                        $item['is_edit_request'] = 1;
                    else
                        $item['is_edit_request'] = 0;
                    $ledger[] = $item;
                }
            }
//            print_r($ledger);exit;
        }
        $user_id = request()->session()->get('user.id');
        $data = CashRegister::join('users as u', 'u.id', 'user_id')->join('business as b', 'b.id', 'u.business_id')->where('u.business_id', $business_id)->where('closed_at', '>=', $start)->where('closed_at', '<=', $end )->get();
        foreach($data as $row){
            $ledger[] = ['date' => $row->closed_at,
                'user' => $row['first_name'].' '.$row['last_name']];
        }
        //Sort by date
        if (!empty($ledger)) {
            usort($ledger, function ($a, $b) {
                $t1 = strtotime($a['date']);
                $t2 = strtotime($b['date']);
                return $t2 - $t1;
            });
        }

        $business_details = $this->businessUtil->getDetails($business_id);
        $bonus_decimal = $business_details->bonus_decimal;
        return view('sale_pos_deposit.ledger')
            ->with(compact('ledger', 'bank_list', 'selected_bank', 'bonus_decimal'));
    }


    public function getPastWithdrawLedger($contact_id) {
        $business_id = request()->session()->get('user.business_id');
        $start_date = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $end_date = date('Y-m-d H:i:s', strtotime('now'));
        // get transaction info
        $query2 = TransactionPayment::join(
            'transactions as t',
            'transaction_payments.transaction_id',
            '=',
            't.id'
        )
            ->join('accounts as a', 'a.id', 'transaction_payments.account_id')
            ->leftJoin('business_locations as bl', 't.location_id', '=', 'bl.id')
            ->join('contacts as c', 'c.id', 't.contact_id')
            ->where('t.contact_id', $contact_id)
            ->where('t.type', 'sell_return')
            ->where('t.business_id', $business_id)
            ->whereBetween(DB::raw('date(transaction_date)'), [$start_date, $end_date])
            ->orderBy('t.transaction_date', 'DESC');

        $payments = $query2->select('transaction_payments.*', 't.id as transaction_id', 't.bank_in_time as bank_in_time', 'bl.name as location_name', 't.type as transaction_type', 't.ref_no', 't.invoice_no'
            ,'transaction_payments.game_id as tp_game_id', 'c.id as contact_primary_key', 'c.contact_id as contact_id', 'c.is_default as is_default', 'a.id as account_id', 'a.name as account_name', 't.created_by as created_by')->get();

        $ledger_by_payment = [];
        foreach ($payments as $payment) {
            if (empty($ledger_by_payment[$payment->transaction_id])) {
                $ref_no = $payment->invoice_no;
                $user = User::find($payment->created_by);
                $ledger_by_payment[$payment->transaction_id] = [
                    'date' => $payment->paid_on,
                    'ref_no' => $payment->payment_ref_no,
                    'type' => $this->transactionTypes['payment'],
                    'location' => $payment->location_name,
                    'contact_id' => $payment->contact_id,
                    'payment_method' => !empty($paymentTypes[$payment->method]) ? $paymentTypes[$payment->method] : '',
                    'debit' => ($payment->card_type == 'debit' && $payment->method != 'service_transfer') ? $payment->amount : 0,
                    'credit' => ($payment->card_type == 'credit' && $payment->method == 'bank_transfer') ? $payment->amount : 0,
                    'free_credit' => ($payment->card_type == 'credit' && $payment->method == 'free_credit') ? $payment->amount : 0,
                    'service_debit' => ($payment->card_type == 'debit' && $payment->method == 'service_transfer') ? $payment->amount : 0,
                    'service_credit' => ($payment->card_type == 'credit' && $payment->method == 'service_transfer') ? $payment->amount : 0,
                    'others' => '<small>' . $ref_no . '</small>',
                    'bank_in_time' => $payment->bank_in_time,
                    'user' => $user['first_name'] . ' ' . $user['last_name'],
                    'is_default' => $payment->is_default,
                    'account_name' => $payment->account_name,
                    'game_id' => $payment->tp_game_id
                ];
            } else {
                $ledger_by_payment[$payment->transaction_id]['debit'] += ($payment->card_type == 'debit' && $payment->method != 'service_transfer') ? $payment->amount : 0;
                $ledger_by_payment[$payment->transaction_id]['credit'] += ($payment->card_type == 'credit' && $payment->method == 'bank_transfer') ? $payment->amount : 0;
                $ledger_by_payment[$payment->transaction_id]['free_credit'] += ($payment->card_type == 'credit' && $payment->method == 'free_credit') ? $payment->amount : 0;
                $ledger_by_payment[$payment->transaction_id]['service_debit'] += ($payment->card_type == 'debit' && $payment->method == 'service_transfer') ? $payment->amount : 0;
                $ledger_by_payment[$payment->transaction_id]['service_credit'] += ($payment->card_type == 'credit' && $payment->method == 'service_transfer') ? $payment->amount : 0;
            }
//            if (($payment->transaction_type == 'sell' || $payment->transaction_type == 'sell_return') && $payment->method == 'service_transfer') {
//                $ledger_by_payment[$payment->transaction_id]['service_name'] = $payment->account_name;
//                $game_data = GameId::where('contact_id', $payment->contact_primary_key)->where('service_id', $payment->account_id)->get();
//                if (count($game_data) >= 1) {
//                    $game_id = $game_data[0]->game_id;
//                    $ledger_by_payment[$payment->transaction_id]['game_id'] = $game_id;
//                }
//            }
            if ($payment->method == 'bank_transfer') {
                $ledger_by_payment[$payment->transaction_id]['bank_id'] = $payment->account_id;
            }
        }
        $html = view('service.withdraw_ledger', compact('ledger_by_payment'))->render();
        return ['html' => $html];
    }

    /**
     * Shows invoice url.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showInvoiceUrl($id)
    {
        if (!auth()->user()->can('sell.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $transaction = Transaction::where('business_id', $business_id)
                                   ->findorfail($id);
            $url = $this->transactionUtil->getInvoiceUrl($id, $business_id);

            return view('sale_pos_deposit.partials.invoice_url_modal')
                    ->with(compact('transaction', 'url'));
        }
    }

    /**
     * Shows invoice to guest user.
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function showInvoice($token)
    {
        $transaction = Transaction::where('invoice_token', $token)->with(['business'])->first();

        if (!empty($transaction)) {
            $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser');

            $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
            return view('sale_pos_deposit.partials.show_invoice')
                    ->with(compact('receipt', 'title'));
        } else {
            die(__("messages.something_went_wrong"));
        }
    }

    /**
     * Display a listing of the recurring invoices.
     *
     * @return \Illuminate\Http\Response
     */
    public function listSubscriptions()
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                ->join(
                    'business_locations AS bl',
                    'transactions.location_id',
                    '=',
                    'bl.id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_recurring', 1)
                ->select(
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.is_direct_sale',
                    'transactions.invoice_no',
                    'contacts.name',
                    'transactions.subscription_no',
                    'bl.name as business_location',
                    'transactions.recur_parent_id',
                    'transactions.recur_stopped_on',
                    'transactions.is_recurring',
                    'transactions.recur_interval',
                    'transactions.recur_interval_type',
                    'transactions.recur_repetitions'
                )->with(['subscription_invoices']);



            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }
            $datatable = Datatables::of($sells)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '' ;

                        if ($row->is_recurring == 1 && auth()->user()->can("sell.update")) {
                            $link_text = !empty($row->recur_stopped_on) ? __('lang_v1.start_subscription') : __('lang_v1.stop_subscription');
                            $link_class = !empty($row->recur_stopped_on) ? 'btn-success' : 'btn-danger';

                            $html .= '<a href="' . action('SellPosController@toggleRecurringInvoices', [$row->id]) . '" class="toggle_recurring_invoice btn btn-xs ' . $link_class . '"><i class="fa fa-power-off"></i> ' . $link_text . '</a>';

                            if ($row->is_direct_sale == 0) {
                                $html .= '<a target="_blank" class="btn btn-xs btn-primary" href="' . action('SellPosController@edit', [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a>';
                            } else {
                                $html .= '<a target="_blank" class="btn btn-xs btn-primary" href="' . action('SellController@edit', [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a>';
                            }
                        }

                        return $html;
                    }
                )
                ->removeColumn('id')
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->editColumn('recur_interval', function ($row) {
                    $type = $row->recur_interval == 1 ? str_singular(__('lang_v1.' . $row->recur_interval_type)) : __('lang_v1.' . $row->recur_interval_type);
                    return $row->recur_interval . $type;
                })
                ->addColumn('subscription_invoices', function ($row) {
                    $invoices = [];
                    if (!empty($row->subscription_invoices)) {
                        $invoices = $row->subscription_invoices->pluck('invoice_no')->toArray();
                    }

                    $html = '';
                    $count = 0;
                    if (!empty($invoices)) {
                        $imploded_invoices = '<span class="label bg-info">' . implode('</span>, <span class="label bg-info">', $invoices) . '</span>';
                        $count = count($invoices);
                        $html .= '<small>' . $imploded_invoices . '</small>';
                    }
                    if ($count > 0) {
                        $html .= '<br><small class="text-muted">' .
                    __('sale.total') . ': ' . $count . '</small>';
                    }

                    return $html;
                })
                ->addColumn('last_generated', function ($row) {
                    if (!empty($row->subscription_invoices)) {
                        $last_generated_date = $row->subscription_invoices->max('created_at');
                    }
                    return !empty($last_generated_date) ? $last_generated_date->diffForHumans() : '';
                })
                ->addColumn('upcoming_invoice', function ($row) {
                    if (empty($row->recur_stopped_on)) {
                        $last_generated = !empty($row->subscription_invoices) ? \Carbon::parse($row->subscription_invoices->max('transaction_date')) : \Carbon::parse($row->transaction_date);
                        if ($row->recur_interval_type == 'days') {
                            $upcoming_invoice = $last_generated->addDays($row->recur_interval);
                        } elseif ($row->recur_interval_type == 'months') {
                            $upcoming_invoice = $last_generated->addMonths($row->recur_interval);
                        } elseif ($row->recur_interval_type == 'years') {
                            $upcoming_invoice = $last_generated->addYears($row->recur_interval);
                        }
                    }
                    return !empty($upcoming_invoice) ? $this->transactionUtil->format_date($upcoming_invoice) : '';
                })
                ->rawColumns(['action', 'subscription_invoices'])
                ->make(true);
                
            return $datatable;
        }
        return view('sale_pos_deposit.subscriptions');
    }

    /**
     * Starts or stops a recurring invoice.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleRecurringInvoices($id)
    {
        if (!auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $transaction = Transaction::where('business_id', $business_id)
                            ->where('type', 'sell')
                            ->where('is_recurring', 1)
                            ->findorfail($id);

            if (empty($transaction->recur_stopped_on)) {
                $transaction->recur_stopped_on = \Carbon::now();
            } else {
                $transaction->recur_stopped_on = null;
            }
            $transaction->save();

            $output = ['success' => 1,
                    'msg' => trans("lang_v1.updated_success")
                ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => trans("messages.something_went_wrong")
                        ];
        }

        return $output;
    }

    public function getRewardDetails(Request $request)
    {
        if ($request->session()->get('business.enable_rp') != 1) {
            return '';
        }

        $business_id = request()->session()->get('user.business_id');
        
        $customer_id = $request->input('customer_id');

        $redeem_details = $this->transactionUtil->getRewardRedeemDetails($business_id, $customer_id);
        
        return json_encode($redeem_details);
    }

    public function placeOrdersApi(Request $request)
    {
        try {
            $api_token = $request->header('API-TOKEN');
            $api_settings = $this->moduleUtil->getApiSettings($api_token);

            $business_id = $api_settings->business_id;
            $location_id = $api_settings->location_id;

            $input = $request->only(['products', 'customer_id', 'addresses']);

            //check if all stocks are available
            $variation_ids = [];
            foreach ($input['products'] as $product_data) {
                $variation_ids[] = $product_data['variation_id'];
            }

            $variations_details = $this->getVariationsDetails($business_id, $location_id, $variation_ids);
            $is_valid = true;
            $error_messages = [];
            $sell_lines = [];
            $final_total = 0;
            foreach ($variations_details as $variation_details) {
                if ($variation_details->product->enable_stock == 1) {
                    if (empty($variation_details->variation_location_details[0]) || $variation_details->variation_location_details[0]->qty_available < $input['products'][$variation_details->id]['quantity']) {
                        $is_valid = false;
                        $error_messages[] = 'Only ' . $variation_details->variation_location_details[0]->qty_available . ' ' . $variation_details->product->unit->short_name . ' of '. $input['products'][$variation_details->id]['product_name'] . ' available';
                    }
                }

                //Create product line array
                $sell_lines[] = [
                    'product_id' => $variation_details->product->id,
                    'unit_price_before_discount' => $variation_details->unit_price_inc_tax,
                    'unit_price' => $variation_details->unit_price_inc_tax,
                    'unit_price_inc_tax' => $variation_details->unit_price_inc_tax,
                    'variation_id' => $variation_details->id,
                    'quantity' => $input['products'][$variation_details->id]['quantity'],
                    'item_tax' => 0,
                    'enable_stock' => $variation_details->product->enable_stock,
                    'tax_id' => null,
                ];

                $final_total += ($input['products'][$variation_details->id]['quantity'] * $variation_details->unit_price_inc_tax);
            }

            if (!$is_valid) {
                return $this->respond([
                    'success' => false,
                    'error_messages' => $error_messages
                ]);
            }

            $business = Business::find($business_id);
            $user_id = $business->owner_id;

            $business_data = [
                'id' => $business_id,
                'accounting_method' => $business->accounting_method,
                'location_id' => $location_id
            ];

            $customer = Contact::where('business_id', $business_id)
                            ->whereIn('type', ['customer', 'both'])
                            ->find($input['customer_id']);

            $order_data = [
                'business_id' => $business_id,
                'location_id' => $location_id,
                'contact_id' => $input['customer_id'],
                'final_total' => $final_total,
                'created_by' => $user_id,
                'status' => 'final',
                'payment_status' => 'due',
                'additional_notes' => '',
                'transaction_date' => \Carbon::now(),
                'customer_group_id' => $customer->customer_group_id,
                'tax_rate_id' => null,
                'sale_note' => null,
                'commission_agent' => null,
                'order_addresses' => json_encode($input['addresses']),
                'products' => $sell_lines,
                'is_created_from_api' => 1,
                'discount_type' => 'fixed',
                'discount_amount' => 0,
            ];

            $invoice_total = [
                'total_before_tax' => $final_total,
                'tax' => 0,
            ];

            DB::beginTransaction();


            $transaction = $this->transactionUtil->createSellTransaction($business_id, $order_data, $invoice_total, $user_id, false);

            //Create sell lines
            $this->transactionUtil->createOrUpdateSellLines($transaction, $order_data['products'], $order_data['location_id'], false, null, [], false);

            //update product stock
            foreach ($order_data['products'] as $product) {
                if ($product['enable_stock']) {
                    $this->productUtil->decreaseProductQuantity(
                        $product['product_id'],
                        $product['variation_id'],
                        $order_data['location_id'],
                        $product['quantity']
                    );
                }
            }

            $this->transactionUtil->mapPurchaseSell($business_data, $transaction->sell_lines, 'purchase');
            //Auto send notification
            $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);

            DB::commit();

            $receipt = $this->receiptContent($business_id, $transaction->location_id, $transaction->id);

            $output = [
                'success' => 1,
                'transaction' => $transaction,
                'receipt' => $receipt
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $msg = trans("messages.something_went_wrong");
                
            if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                $msg = $e->getMessage();
            }

            $output = ['success' => 0,
                        'error_messages' => [$msg]
                    ];
        }

        return $this->respond($output);
    }

    private function getVariationsDetails($business_id, $location_id, $variation_ids)
    {
        $variation_details = Variation::whereIn('id', $variation_ids)
                            ->with([
                                'product' => function ($q) use ($business_id) {
                                    $q->where('business_id', $business_id);
                                },
                                'product.unit',
                                'variation_location_details' => function ($q) use ($location_id) {
                                    $q->where('location_id', $location_id);
                                }
                            ])->get();

        return $variation_details;
    }
}
