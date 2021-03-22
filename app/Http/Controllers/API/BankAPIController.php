<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\BankBrand;
use App\DashboardBonus;
use App\Http\Controllers\Controller;
use App\NewTransactions;
use App\Product;
use App\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class BankAPIController extends Controller
{
    public function bankList(Request $request) {
//        try {
            $business_id = $request->get('business_id');
//            $data = Account::leftjoin('bank_brands', 'bank_brands.id', 'accounts.bank_brand_id')
//                ->where('accounts.business_id', $business_id)
//                ->where('is_display_front', true)
//                ->select('accounts.id AS bank_id', 'accounts.name', 'accounts.account_number', 'bank_brands.name as bank_brand')->get();
            $products = Product::leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->join('units', 'products.unit_id', '=', 'units.id')
                ->join('variations as v', 'v.product_id', '=', 'products.id')
                ->leftJoin('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')
                ->leftJoin('accounts', 'accounts.id', '=', 'products.account_id')
                ->leftjoin('bank_brands', 'bank_brands.id', 'accounts.bank_brand_id')
                ->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier')
                ->where('products.is_display_front', true)
                ->select(
                    'products.id',
                    'products.name',
                    'products.priority as priority',
                    'products.sku',
                    'products.image',
                    'products.is_inactive',
                    'products.not_for_selling',
                    'accounts.account_number',
                    'accounts.id as bank_id',
                    'bank_brands.name as bank_brand'
                )->groupBy('products.id');
            $gtrans_unit_id = Unit::where('business_id', $business_id)->where('short_name', 'BTrans')->first()->id;

            if (!empty($gtrans_unit_id)) {
                $products->where('products.unit_id', $gtrans_unit_id);
            }

            $data = $products->get();
            $output = ['success' => true, 'list' => $data];
//        } catch (\Exception $e) {
//            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
//
//            $output = ['success' => false, 'msg' => __("messages.something_went_wrong")
//            ];
//        }
        return $output;
    }

    public function bonusList(Request $request) {
        try {
            $business_id = $request->get('business_id');
            $data = DashboardBonus
                ::leftjoin('variations as v', 'v.id', 'dashboard_bonuses.variation_id')
                ->join('products as p', 'v.product_id', 'p.id')
                ->where('dashboard_bonuses.variation_id', '!=', '-1')
                ->where('dashboard_bonuses.business_id', $business_id)
                ->where('dashboard_bonuses.is_display_front', true)
                ->select('dashboard_bonuses.variation_id', DB::raw("CONCAT(p.name, ' - ', v.name) AS name"))
                ->get();
            $new_data = [];
            foreach ($data as $row)
                $new_data[] = $row;
            if(DashboardBonus::where('business_id', $business_id)->where('variation_id', '-1')->where('is_display_front', true)->count() > 0)
                $new_data[] =  ['variation_id' => -1, 'name' => 'Basic Bonus'];
            $output = ['success' => true, 'list' => $new_data];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false, 'msg' => __("messages.something_went_wrong")
            ];
        }
        return $output;
    }

    public function bankBrandList(Request $request) {
        try {
            $business_id = $request->get('business_id');
            $data = BankBrand::forDropdown($business_id);
            $output = ['success' => true, 'list' => $data];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false, 'msg' => __("messages.something_fwent_wrong")
            ];
        }
        return $output;
    }

    public function kioskList(Request $request) {
        try {
            $business_id = $request->get('business_id');
//            $data = BankBrand::forDropdown($business_id);
            $query = Account::where('business_id', $business_id)
                ->where('is_display_front', 1)
                ->NotCapital();

            $dropdown = $query->pluck('name', 'id');
            //Prepend all
            $output = ['success' => true, 'list' => $dropdown];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false, 'msg' => __("messages.something_fwent_wrong")
            ];
        }
        return $output;
    }

    public function productList(Request $request) {
        try {
            $business_id = $request->get('business_id');
            $gtrans_unit_id = Unit::where('business_id', $business_id)->where('short_name', 'GTrans')->first()->id;
            $query = Product::where('business_id', $business_id)
                ->where('unit_id', $gtrans_unit_id)
                ->where('is_display_front', 1);

            $dropdown = $query->pluck('name', 'id');
            //Prepend all
            $output = ['success' => true, 'list' => $dropdown];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false, 'msg' => __("messages.something_fwent_wrong")
            ];
        }
        return $output;
    }
}
