<?php

namespace App\Utils;

use App\Account;
use App\BankBrand;
use App\Barcode;

use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\Currency;
use App\CustomerGroup;
use App\DisplayGroup;
use App\InvoiceLayout;
use App\InvoiceScheme;
use App\Media;
use App\NotificationTemplate;
use App\Printer;
use App\Product;
use App\ProductVariation;
use App\Unit;
use App\User;
use App\Variation;
use App\VariationTemplate;
use App\VariationValueTemplate;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class BusinessUtil extends Util
{

    /**
     * Adds a default settings/resources for a new business
     *
     * @param int $business_id
     * @param int $user_id
     *
     * @return boolean
     */
    public function newBusinessDefaultResources($business_id, $user_id)
    {
        $user = User::find($user_id);

        //create Admin role and assign to user
        $role = Role::create([ 'name' => 'Admin#' . $business_id,
                            'business_id' => $business_id,
                            'guard_name' => 'web', 'is_default' => 1
                        ]);
        $user->assignRole($role->name);

        //Create Cashier role for a new business
        $cashier_role = Role::create([ 'name' => 'Cashier#' . $business_id,
                            'business_id' => $business_id,
                            'guard_name' => 'web'
                        ]);
        $cashier_role->syncPermissions(['sell.view', 'sell.create', 'sell.update', 'sell.delete', 'access_all_locations']);

        $business = Business::findOrFail($business_id);

        //Update reference count
//        $ref_count = $this->setAndGetReferenceCount('contacts', $business_id);
//        $contact_id = $this->generateReferenceNumber('contacts', $ref_count, $business_id);

        //Add Default/Walk-In Customer for new business
        $customer = [
                        'business_id' => $business_id,
                        'type' => 'customer',
                        'name' => 'Unclaimed Trans',
                        'created_by' => $user_id,
                        'is_default' => 1,
                        'contact_id' => 'UNCLAIM'
                    ];
        Contact::create($customer);

        //create default invoice setting for new business
        InvoiceScheme::create(['name' => 'Default',
                            'scheme_type' => 'blank',
                            'prefix' => '',
                            'start_number' => 1,
                            'total_digits' => 4,
                            'is_default' => 1,
                            'business_id' => $business_id
                        ]);
        //create default invoice layour for new business
        InvoiceLayout::create(['name' => 'Default',
                        'header_text' => null,
                        'invoice_no_prefix' => 'Invoice No.',
                        'invoice_heading' => 'Invoice',
                        'sub_total_label' => 'Subtotal',
                        'discount_label' => 'Discount',
                        'tax_label' => 'Tax',
                        'total_label' => 'Total',
                        'show_landmark' => 1,
                        'show_city' => 1,
                        'show_state' => 1,
                        'show_zip_code' => 1,
                        'show_country' => 1,
                        'highlight_color' => '#000000',
                        'footer_text' => '',
                        'is_default' => 1,
                        'business_id' => $business_id,
                        'invoice_heading_not_paid' => '',
                        'invoice_heading_paid' => '',
                        'total_due_label' => 'Total Due',
                        'paid_label' => 'Total Paid',
                        'show_payments' => 1,
                        'show_customer' => 1,
                        'customer_label' => 'Customer',
                        'table_product_label' => 'Product',
                        'table_qty_label' => 'Quantity',
                        'table_unit_price_label' => 'Unit Price',
                        'table_subtotal_label' => 'Subtotal',
                        'date_label' => 'Date'
                    ]);

        //create default barcode setting for new business
        // Barcode::create(['name' => 'Default',
        //                 'description' => '',
        //                 'width' => 37.29,
        //                 'height' => 25.93,
        //                 'top_margin' => 5,
        //                 'left_margin' => 5,
        //                 'row_distance' => 1,
        //                 'col_distance' => 1,
        //                 'stickers_in_one_row' => 4,
        //                 'is_default' => 1,
        //                 'business_id' => $business_id
        //             ]);
        
        //Add Default Unit for new business
//        $unit = [
//                    'business_id' => $business_id,
//                    'actual_name' => 'Pieces',
//                    'short_name' => 'Pc(s)',
//                    'allow_decimal' => 0,
//                    'created_by' => $user_id
//                ];
//        Unit::create($unit);

        $unit = [
            'business_id' => $business_id,
            'actual_name' => 'BankDeposits',
            'short_name' => 'BTrans',
            'allow_decimal' => 0,
            'created_by' => $user_id
        ];
        $btrans_unit = Unit::create($unit);

        $unit = [
            'business_id' => $business_id,
            'actual_name' => 'GameTransactions',
            'short_name' => 'GTrans',
            'allow_decimal' => 0,
            'created_by' => $user_id
        ];
        $gtrans_unit = Unit::create($unit);

        $unit = [
            'business_id' => $business_id,
            'actual_name' => 'SpecialBonus',
            'short_name' => 'Bonus',
            'allow_decimal' => 0,
            'created_by' => $user_id
        ];
        $special_bonus_unit = Unit::create($unit);

        // Add Default Display Groups
        $display_group = [
            'business_id' => $business_id,
            'name' => 'Malaysia Group',
            'created_by' => $user_id
        ];
        DisplayGroup::create($display_group);

        // Create Bonus, Bank, kiosk Account
        $account = [
            'business_id' => $business_id,
            'name' => 'Bonus Account',
            'currency_id' => 75,
            'account_number' => '',
            'created_by' => $user_id,
            'account_type' => 'saving_current'
        ];
        $bonus_account = Account::create($account);

        $account = [
            'business_id' => $business_id,
            'name' => 'OCBC',
            'currency_id' => 75,
            'account_number' => '11111',
            'created_by' => $user_id,
            'account_type' => 'saving_current'
        ];
        $first_bank_account = Account::create($account);

        $account = [
            'business_id' => $business_id,
            'name' => '3Win8',
            'currency_id' => 75,
            'account_number' => '3Win8',
            'is_service' => 1,
            'created_by' => $user_id,
            'account_type' => 'saving_current'
        ];
        $first_kiosk_account = Account::create($account);

        // Add default variation
        $input = [
            'name' => 'RM20,30,50,100',
            'business_id' => $business_id
        ];
        $rm_variation = VariationTemplate::create($input);

        //craete variation values
        $values = ['RM20', 'RM30', 'RM50', 'RM100'];
        $data = [];
        foreach ($values as $value) {
            $data[] = [ 'name' => $value];
        }
        $rm_variation_value_templates = $rm_variation->values()->createMany($data);

        $input = [
            'name' => 'Percentage20,30,50,100',
            'business_id' => $business_id
        ];
        $percent_variation = VariationTemplate::create($input);

        //craete variation values
        $values = ['20%', '30%', '50%', '100%'];
        $data = [];
        foreach ($values as $value) {
            $data[] = [ 'name' => $value];
        }
        $percent_variation_value_templates = $percent_variation->values()->createMany($data);
        // create bonus product
        $product_details = [
            'name' => 'Bonus' ,'brand_id' => null,'unit_id' => $special_bonus_unit->id ,'account_id' => $bonus_account->id ,'category_id' => 66 ,'tax' => null,
            'type' => 'variable' ,'barcode_type' => 'C128' ,'sku' => '','alert_quantity' => 0 ,'tax_type' => 'exclusive' ,'weight' => '',
            'product_description' => '','business_id' => $business_id ,'created_by' => $user_id ,'enable_stock' => 0 ,'not_for_selling' => 0
        ];
        $bonus_product = Product::create($product_details);
        $bonus_input_variations = [[
                'variation_template_id' => $percent_variation->id,
                'variations' => [
                    ['sub_sku' => '', 'variation_value_id' => $percent_variation_value_templates[0]->id, 'value' => '20%', 'default_purchase_price' => 20, 'dpp_inc_tax' => 20.00, 'profit_percent' => 0, 'default_sell_price' => 20.00, 'sell_price_inc_tax' => 20.00],
                    ['sub_sku' => '', 'variation_value_id' => $percent_variation_value_templates[1]->id, 'value' => '30%', 'default_purchase_price' => 30, 'dpp_inc_tax' => 30.00, 'profit_percent' => 0, 'default_sell_price' => 30.00, 'sell_price_inc_tax' => 30.00],
                    ['sub_sku' => '', 'variation_value_id' => $percent_variation_value_templates[2]->id, 'value' => '50%', 'default_purchase_price' => 50, 'dpp_inc_tax' => 50.00, 'profit_percent' => 0, 'default_sell_price' => 50.00, 'sell_price_inc_tax' => 50.00],
                    ['sub_sku' => '', 'variation_value_id' => $percent_variation_value_templates[3]->id, 'value' => '100%', 'default_purchase_price' => 100, 'dpp_inc_tax' => 100.00, 'profit_percent' => 0, 'default_sell_price' => 100.00, 'sell_price_inc_tax' => 100.00]
                ]
        ]];
        $this->createVariableProductVariations($bonus_product->id, $bonus_input_variations);

        // create bank product
        $product_details = [
            'name' => 'OCBC' ,'brand_id' => null,'unit_id' => $btrans_unit->id ,'account_id' => $first_bank_account->id ,'category_id' => 66 ,'tax' => null,
            'type' => 'variable' ,'barcode_type' => 'C128' ,'sku' => '','alert_quantity' => 0 ,'tax_type' => 'exclusive' ,'weight' => '',
            'product_description' => '','business_id' => $business_id ,'created_by' => $user_id ,'enable_stock' => 0 ,'not_for_selling' => 0
        ];
        $bank_product = Product::create($product_details);
        $bank_input_variations = [[
            'variation_template_id' => $rm_variation->id,
            'variations' => [
                ['sub_sku' => '', 'variation_value_id' => $rm_variation_value_templates[0]->id, 'value' => '20%', 'default_purchase_price' => 20, 'dpp_inc_tax' => 20.00, 'profit_percent' => 0, 'default_sell_price' => 20.00, 'sell_price_inc_tax' => 20.00],
                ['sub_sku' => '', 'variation_value_id' => $rm_variation_value_templates[1]->id, 'value' => '30%', 'default_purchase_price' => 30, 'dpp_inc_tax' => 30.00, 'profit_percent' => 0, 'default_sell_price' => 30.00, 'sell_price_inc_tax' => 30.00],
                ['sub_sku' => '', 'variation_value_id' => $rm_variation_value_templates[2]->id, 'value' => '50%', 'default_purchase_price' => 50, 'dpp_inc_tax' => 50.00, 'profit_percent' => 0, 'default_sell_price' => 50.00, 'sell_price_inc_tax' => 50.00],
                ['sub_sku' => '', 'variation_value_id' => $rm_variation_value_templates[3]->id, 'value' => '100%', 'default_purchase_price' => 100, 'dpp_inc_tax' => 100.00, 'profit_percent' => 0, 'default_sell_price' => 100.00, 'sell_price_inc_tax' => 100.00]
            ]
        ]];
        $this->createVariableProductVariations($bank_product->id, $bank_input_variations);

        // create kiosk product
        $product_details = [
            'name' => '3Win8' ,'brand_id' => null,'unit_id' => $gtrans_unit->id ,'account_id' => $first_kiosk_account->id ,'category_id' => 67 ,'tax' => null,
            'type' => 'single' ,'barcode_type' => 'C128' ,'sku' => '','alert_quantity' => 0 ,'tax_type' => 'exclusive' ,'weight' => '',
            'product_description' => '','business_id' => $business_id ,'created_by' => $user_id ,'enable_stock' => 0 ,'not_for_selling' => 0
        ];
        $kiosk_product = Product::create($product_details);

        $sku_prefix = Business::where('id', $business_id)->value('sku_prefix');
        $sku = $sku_prefix . str_pad($kiosk_product->id, 4, '0', STR_PAD_LEFT);
        $kiosk_product->sku = $sku;
        $kiosk_product->save();
        $this->createSingleProductVariation($kiosk_product->id, $kiosk_product->sku, 10, 10, 0, 10, 10);

        // Create default Customer group
        CustomerGroup::create([
            'business_id' => $business_id,
            'created_by' => $user_id,
            'name' => 'Catcher',
            'amount' => 0
        ]);

        CustomerGroup::create([
            'business_id' => $business_id,
            'created_by' => $user_id,
            'name' => 'FB',
            'amount' => 0
        ]);

        CustomerGroup::create([
            'business_id' => $business_id,
            'created_by' => $user_id,
            'name' => 'Friend',
            'amount' => 0
        ]);

        CustomerGroup::create([
            'business_id' => $business_id,
            'created_by' => $user_id,
            'name' => 'Google',
            'amount' => 0
        ]);

        BankBrand::create([
            'business_id' => $business_id,
            'created_by' => $user_id,
            'name' => 'Public Bank'
        ]);
        //Create default notification templates
        $notification_templates = NotificationTemplate::defaultNotificationTemplates($business_id);
        foreach ($notification_templates as $notification_template) {
            NotificationTemplate::create($notification_template);
        }

        return true;
    }




    /**
     * Create variable type product variation
     *
     * @param (int or object) $product
     * @param $input_variations
     *
     * @return boolean
     */
    public function createVariableProductVariations($product, $input_variations, $business_id = null)
    {
        if (!is_object($product)) {
            $product = Product::find($product);
        }

        //create product variations
        foreach ($input_variations as $key => $value) {
            $images = [];
            $variation_template_name = !empty($value['name']) ? $value['name'] : null;
            $variation_template_id = !empty($value['variation_template_id']) ? $value['variation_template_id'] : null;

            if (empty($variation_template_id)) {
                if ($variation_template_name != 'DUMMY') {
                    $variation_template = VariationTemplate::where('business_id', $business_id)
                        ->whereRaw('LOWER(name)="' . strtolower($variation_template_name) . '"')
                        ->with(['values'])
                        ->first();
                    if (empty($variation_template)) {
                        $variation_template = VariationTemplate::create([
                            'name' => $variation_template_name,
                            'business_id' => $business_id
                        ]);
                    }
                    $variation_template_id = $variation_template->id;
                }
            } else {
                $variation_template = VariationTemplate::with(['values'])->find($value['variation_template_id']);
                $variation_template_id = $variation_template->id;
                $variation_template_name = $variation_template->name;
            }

            $product_variation_data = [
                'name' => $variation_template_name,
                'product_id' => $product->id,
                'is_dummy' => 0,
                'variation_template_id' => $variation_template_id
            ];
            $product_variation = ProductVariation::create($product_variation_data);

            //create variations
            if (!empty($value['variations'])) {
                $variation_data = [];

                $c = Variation::withTrashed()
                        ->where('product_id', $product->id)
                        ->count() + 1;

                foreach ($value['variations'] as $k => $v) {
                    $sub_sku = empty($v['sub_sku'])? $this->generateSubSku($product->sku, $c, $product->barcode_type) :$v['sub_sku'];
                    $variation_value_id = !empty($v['variation_value_id']) ? $v['variation_value_id'] : null;
                    $variation_value_name = !empty($v['value']) ? $v['value'] : null;

                    if (!empty($variation_value_id)) {
                        $variation_value = $variation_template->values->filter(function ($item) use ($variation_value_id) {
                            return $item->id == $variation_value_id;
                        })->first();
                        $variation_value_name = $variation_value->name;
                    } else {
                        if (!empty($variation_template)) {
                            $variation_value =  VariationValueTemplate::where('variation_template_id', $variation_template->id)
                                ->whereRaw('LOWER(name)="' . $variation_value_name . '"')
                                ->first();
                            if (empty($variation_value)) {
                                $variation_value =  VariationValueTemplate::create([
                                    'name' => $variation_value_name,
                                    'variation_template_id' => $variation_template->id
                                ]);
                            }
                            $variation_value_id = $variation_value->id;
                            $variation_value_name = $variation_value->name;
                        } else {
                            $variation_value_id = null;
                            $variation_value_name = $variation_value_name;
                        }
                    }

                    $variation_data[] = [
                        'name' => $variation_value_name,
                        'variation_value_id' => $variation_value_id,
                        'product_id' => $product->id,
                        'sub_sku' => $sub_sku,
                        'default_purchase_price' => $this->num_uf($v['default_purchase_price']),
                        'dpp_inc_tax' => $this->num_uf($v['dpp_inc_tax']),
                        'profit_percent' => $this->num_uf($v['profit_percent']),
                        'default_sell_price' => $this->num_uf($v['default_sell_price']),
                        'sell_price_inc_tax' => $this->num_uf($v['sell_price_inc_tax'])
                    ];
                    $c++;
                    $images[] = 'variation_images_' . $key . '_' . $k;
                }
                $variations = $product_variation->variations()->createMany($variation_data);

                $i = 0;
                foreach ($variations as $variation) {
                    Media::uploadMedia($product->business_id, $variation, request(), $images[$i]);
                    $i++;
                }
            }
        }
    }

    public function generateSubSku($sku, $c, $barcode_type)
    {
        $sub_sku = $sku . $c;

        if (in_array($barcode_type, ['C128', 'C39'])) {
            $sub_sku = $sku . '-' . $c;
        }

        return $sub_sku;
    }


    /**
     * Create single type product variation
     *
     * @param $product
     * @param $sku
     * @param $purchase_price
     * @param $dpp_inc_tax (default purchase pric including tax)
     * @param $profit_percent
     * @param $selling_price
     * @param $selling_price_inc_tax
     * @param array $combo_variations = []
     *
     * @return boolean
     */
    public function createSingleProductVariation($product, $sku, $purchase_price, $dpp_inc_tax, $profit_percent, $selling_price, $selling_price_inc_tax, $combo_variations = [])
    {
        if (!is_object($product)) {
            $product = Product::find($product);
        }

        //create product variations
        $product_variation_data = [
            'name' => 'DUMMY',
            'is_dummy' => 1
        ];
        $product_variation = $product->product_variations()->create($product_variation_data);

        //create variations
        $variation_data = [
            'name' => 'DUMMY',
            'product_id' => $product->id,
            'sub_sku' => $sku,
            'default_purchase_price' => $this->num_uf($purchase_price),
            'dpp_inc_tax' => $this->num_uf($dpp_inc_tax),
            'profit_percent' => $this->num_uf($profit_percent),
            'default_sell_price' => $this->num_uf($selling_price),
            'sell_price_inc_tax' => $this->num_uf($selling_price_inc_tax),
            'combo_variations' => $combo_variations
        ];
        $variation = $product_variation->variations()->create($variation_data);

//        Media::uploadMedia($product->business_id, $variation, request(), 'variation_images');

        return true;
    }
    /**
     * Gives a list of all currencies
     *
     * @return array
     */
    public function allCurrencies()
    {
        $currencies = Currency::select('id', DB::raw("concat(country, ' - ',currency, '(', code, ') ') as info"))
                ->orderBy('country')
                ->pluck('info', 'id');

        return $currencies;
    }

    /**
     * Gives a list of all timezone
     *
     * @return array
     */
    public function allTimeZones()
    {
        $datetime = new \DateTimeZone("EDT");

        $timezones = $datetime->listIdentifiers();
        $timezone_list = [];
        foreach ($timezones as $timezone) {
            $timezone_list[$timezone] = $timezone;
        }

        return $timezone_list;
    }

    /**
     * Gives a list of all accouting methods
     *
     * @return array
     */
    public function allAccountingMethods()
    {
        return [
            'fifo' => __('business.fifo'),
            'lifo' => __('business.lifo')
        ];
    }

    /**
     * Creates new business with default settings.
     *
     * @return array
     */
    public function createNewBusiness($business_details)
    {
        $business_details['sell_price_tax'] = 'includes';

        $business_details['default_profit_percent'] = 0;

        //Add POS shortcuts
        $business_details['keyboard_shortcuts'] = '{"pos":{"express_checkout":"shift+e","pay_n_ckeckout":"shift+p","draft":"shift+d","cancel":"shift+c","edit_discount":"shift+i","edit_order_tax":"shift+t","add_payment_row":"shift+r","finalize_payment":"shift+f","recent_product_quantity":"f2","add_new_product":"f4"}}';


        //Add prefixes
        $business_details['ref_no_prefixes'] = [
            'purchase' => 'PO',
            'stock_transfer' => 'ST',
            'stock_adjustment' => 'SA',
            'sell_return' => 'CN',
            'expense' => 'EP',
            'contacts' => 'CO',
            'purchase_payment' => 'PP',
            'sell_payment' => 'SP',
            'business_location' => 'BL'
            ];

        //Disable inline tax editing
        $business_details['enable_inline_tax'] = 0;

        $business = Business::create_business($business_details);

        return $business;
    }

    /**
     * Gives details for a business
     *
     * @return object
     */
    public function getDetails($business_id)
    {
        $details = Business::
                        leftjoin('tax_rates AS TR', 'business.default_sales_tax', 'TR.id')
                        ->leftjoin('currencies AS cur', 'business.currency_id', 'cur.id')
                        ->select(
                            'business.*',
                            'cur.code as currency_code',
                            'cur.symbol as currency_symbol',
                            'thousand_separator',
                            'decimal_separator',
                            'TR.amount AS tax_calculation_amount',
                            'business.default_sales_discount'
                        )
                        ->where('business.id', $business_id)
                        ->first();

        return $details;
    }

    /**
     * Gives current financial year
     *
     * @return array
     */
    public function getCurrentFinancialYear($business_id = 0)
    {
        if($business_id == 0)
            $start_month = 1;
        else{
            $business = Business::where('id', $business_id)->first();
            $start_month = $business->fy_start_month;
        }
        $end_month = $start_month -1;
        if ($start_month == 1) {
            $end_month = 12;
        }
        
        $start_year = date('Y');
        //if current month is less than start month change start year to last year
        if (date('n') < $start_month) {
            $start_year = $start_year - 1;
        }

        $end_year = date('Y');
        //if current month is greater than end month change end year to next year
        if (date('n') > $end_month) {
            $end_year = $start_year + 1;
        }
        $start_date = $start_year . '-' . str_pad($start_month, 2, 0, STR_PAD_LEFT) . '-01';
        $end_date = $end_year . '-' . str_pad($end_month, 2, 0, STR_PAD_LEFT) . '-01';
        $end_date = date('Y-m-t', strtotime($end_date));

        $output = [
                'start' => $start_date,
                'end' =>  $end_date
            ];
        return $output;
    }

    /**
     * Adds a new location to a business
     *
     * @param int $business_id
     * @param array $location_details
     * @param int $invoice_layout_id default null
     *
     * @return location object
     */
    public function addLocation($business_id, $location_details, $invoice_scheme_id = null, $invoice_layout_id = null)
    {
        if (empty($invoice_scheme_id)) {
            $layout = InvoiceLayout::where('is_default', 1)
                                    ->where('business_id', $business_id)
                                    ->first();
            $invoice_layout_id = $layout->id;
        }

        if (empty($invoice_scheme_id)) {
            $scheme = InvoiceScheme::where('is_default', 1)
                                    ->where('business_id', $business_id)
                                    ->first();
            $invoice_scheme_id = $scheme->id;
        }

        //Update reference count
        $ref_count = $this->setAndGetReferenceCount('business_location', $business_id);
        $location_id = $this->generateReferenceNumber('business_location', $ref_count, $business_id);

        $location = BusinessLocation::create(['business_id' => $business_id,
                            'name' => $location_details['name'],
                            'landmark' => $location_details['landmark'],
                            'city' => $location_details['city'],
                            'state' => $location_details['state'],
                            'zip_code' => $location_details['zip_code'],
                            'country' => $location_details['country'],
                            'invoice_scheme_id' => $invoice_scheme_id,
                            'invoice_layout_id' => $invoice_layout_id,
                            'mobile' => !empty($location_details['mobile']) ? $location_details['mobile'] : '',
                            'alternate_number' => !empty($location_details['alternate_number']) ? $location_details['alternate_number'] : '',
                            'website' => !empty($location_details['website']) ? $location_details['website'] : '',
                            'email' => '',
                            'location_id' => $location_id
                        ]);
        return $location;
    }

    /**
     * Return the invoice layout details
     *
     * @param int $business_id
     * @param array $location_details
     * @param array $layout_id = null
     *
     * @return location object
     */
    public function invoiceLayout($business_id, $location_id, $layout_id = null)
    {
        $layout = null;
        if (!empty($layout_id)) {
            $layout = InvoiceLayout::find($layout_id);
        }
        
        //If layout is not found (deleted) then get the default layout for the business
        if (empty($layout)) {
            $layout = InvoiceLayout::where('business_id', $business_id)
                        ->where('is_default', 1)
                        ->first();
        }
        //$output = []
        return $layout;
    }

    /**
     * Return the printer configuration
     *
     * @param int $business_id
     * @param int $printer_id
     *
     * @return array
     */
    public function printerConfig($business_id, $printer_id)
    {
        $printer = Printer::where('business_id', $business_id)
                    ->find($printer_id);

        $output = [];

        if (!empty($printer)) {
            $output['connection_type'] = $printer->connection_type;
            $output['capability_profile'] = $printer->capability_profile;
            $output['char_per_line'] = $printer->char_per_line;
            $output['ip_address'] = $printer->ip_address;
            $output['port'] = $printer->port;
            $output['path'] = $printer->path;
            $output['server_url'] = $printer->server_url;
        }

        return $output;
    }

    /**
     * Return the date range for which editing of transaction for a business is allowed.
     *
     * @param int $business_id
     * @param char $edit_transaction_period
     *
     * @return array
     */
    public function editTransactionDateRange($business_id, $edit_transaction_period)
    {
        if (is_numeric($edit_transaction_period)) {
            return ['start' => \Carbon::today()
                                ->subDays($edit_transaction_period),
                    'end' => \Carbon::today()
                ];
        } elseif ($edit_transaction_period == 'fy') {
            //Editing allowed for current financial year
            return $this->getCurrentFinancialYear($business_id);
        }

        return false;
    }

    /**
     * Return the default setting for the pos screen.
     *
     * @return array
     */
    public function defaultPosSettings()
    {
        return ['disable_pay_checkout' => 0, 'disable_draft' => 0, 'disable_express_checkout' => 0, 'hide_product_suggestion' => 0, 'hide_recent_trans' => 0, 'disable_discount' => 0, 'disable_order_tax' => 0, 'is_pos_subtotal_editable' => 0];
    }

    /**
     * Return the default setting for the email.
     *
     * @return array
     */
    public function defaultEmailSettings()
    {
        return ['mail_host' => '', 'mail_port' => '', 'mail_username' => '', 'mail_password' => '', 'mail_encryption' => '', 'mail_from_address' => '', 'mail_from_name' => ''];
    }

    /**
     * Return the default setting for the email.
     *
     * @return array
     */
    public function defaultSmsSettings()
    {
        return ['url' => '', 'send_to_param_name' => 'to', 'msg_param_name' => 'text', 'request_method' => 'post', 'param_1' => '', 'param_val_1' => '', 'param_2' => '', 'param_val_2' => '','param_3' => '', 'param_val_3' => '','param_4' => '', 'param_val_4' => '','param_5' => '', 'param_val_5' => '', ];
    }
}
