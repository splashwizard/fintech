<?php

namespace App\Http\Controllers;

use App\BankBrand;
use App\BusinessLocation;
use App\Contact;
use App\Currency;
use App\CustomerGroup;
use App\DashboardBonus;
use App\DisplayGroup;
use App\Product;
use App\Unit;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\Util;
use App\Utils\TransactionUtil;
use App\Utils\ContactUtil;
use App\Variation;
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
            $products = Product::leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->join('units', 'products.unit_id', '=', 'units.id')
                ->join('variations as v', 'v.product_id', '=', 'products.id')
                ->leftJoin('variation_location_details as vld', 'vld.variation_id', '=', 'v.id')
                ->leftJoin('accounts', 'accounts.id', '=', 'products.account_id')
                ->leftjoin('bank_brands', 'bank_brands.id', 'accounts.bank_brand_id')
                ->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier')
                ->select(
                    'products.id',
                    'products.name',
                    'products.priority as priority',
                    'products.sku',
                    'products.image',
                    'products.is_inactive',
                    'products.not_for_selling',
                    'accounts.account_number',
                    'bank_brands.name as bank_brand'
                )->groupBy('products.id');
            $gtrans_unit_id = Unit::where('business_id', $business_id)->where('short_name', 'BTrans')->first()->id;

            if (!empty($gtrans_unit_id)) {
                $products->where('products.unit_id', $gtrans_unit_id);
            }

            return Datatables::of($products)
                ->editColumn('product', function ($row) {
                    $product = $row->is_inactive == 1 ? $row->product . ' <span class="label bg-gray">Inactive
                        </span>' : $row->product;

                    $product = $row->not_for_selling == 1 ? $product . ' <span class="label bg-gray">' . __("lang_v1.not_for_selling") .
                        '</span>' : $product;

                    return $product;
                })
                ->addColumn('is_display_front', function ($row) {
                    return  '<input type="checkbox" class="account_display_front" data-id="'.$row->id.'"'. ($row->is_display_front ? 'checked' : null) .'>' ;
                })
                ->rawColumns(['product', 'is_display_front'])
                ->make(true);
        }
        $bonuses = $this->getBonuses($business_id);

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
            ->with(compact('not_linked_payments', 'bonuses'));
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

        $bonuses = [ -1 => ['name' => 'Basic Bonus'], 0 => ['name' => 'No Bonus']];
        $bonuses_data = $bonuses_query->select(
            DB::raw("CONCAT(p.name, ' - ', variations.name) AS name"),
            'variations.id as variation_id'
        )
            ->orderBy('p.name', 'asc')
            ->get();
        foreach ($bonuses_data as $item) {
            $bonuses[$item->variation_id] = ['name' => $item->name];
        }
        $data = DashboardBonus::where('business_id' ,$business_id)->whereIn('variation_id', array_keys($bonuses))->select('variation_id', 'description', 'is_display_front')->get();
        foreach ($data as $item) {
            $bonuses[$item->variation_id]['description'] = $item->description;
            $bonuses[$item->variation_id]['is_display_front'] = $item->is_display_front;
        }

        return $bonuses;
    }

    public function updateDisplayFront(Request $request, $id){
        $is_display_front = $request->get('is_display_front');
        Product::find($id)->update(['is_display_front' => $is_display_front]);
        return ['success' => true];
    }

    public function updateBonusDisplayFront(Request $request, $id){
        $business_id = $request->session()->get('user.business_id');
        $is_display_front = $request->get('is_display_front');
        if(DashboardBonus::where('business_id', $business_id)->where('variation_id', $id)->count() == 0){
            DashboardBonus::create([
                'business_id' => $business_id,
                'variation_id' => $id,
                'is_display_front' => $is_display_front
            ]);
        } else {
            DashboardBonus::where('business_id', $business_id)->where('variation_id', $id)->update(['is_display_front' => $is_display_front]);
        }
        return ['success' => true];
    }

    public function updateBonusDescription(Request $request, $id){
        $business_id = $request->session()->get('user.business_id');
        $description = $request->get('description');
        if(DashboardBonus::where('business_id', $business_id)->where('variation_id', $id)->count() == 0){
            DashboardBonus::create([
                'business_id' => $business_id,
                'variation_id' => $id,
                'description' => $description
            ]);
        } else {
            DashboardBonus::where('business_id', $business_id)->where('variation_id', $id)->update(['description' => $description]);
        }
        return ['success' => true];
    }
}
