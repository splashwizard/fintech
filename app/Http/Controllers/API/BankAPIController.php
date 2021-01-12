<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\BankBrand;
use App\Http\Controllers\Controller;
use App\NewTransactions;
use App\Product;
use App\Unit;
use Illuminate\Http\Request;



class BankAPIController extends Controller
{
    public function bankList(Request $request) {
        try {
            $data = Account::leftjoin('bank_brands', 'bank_brands.id', 'accounts.bank_brand_id')
                ->where('is_display_front', true)
                ->select('accounts.id AS bank_id', 'accounts.name', 'accounts.account_number', 'bank_brands.name as bank_brand')->get();
            $output = ['success' => true, 'list' => $data];
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
