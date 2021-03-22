<?php

namespace App\Http\Controllers;

use App\BankBrand;
use App\BusinessLocation;
use App\Contact;
use App\Currency;
use App\CustomerGroup;
use App\DisplayGroup;
use App\Product;
use App\Unit;
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

class DashboardTransferController extends Controller
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
                ->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier')
                ->select(
                    'products.id',
                    'products.name as product',
                    'products.priority as priority',
                    'products.sku',
                    'products.image',
                    'products.is_inactive',
                    'products.not_for_selling',
                    'products.is_display_front'
                )->groupBy('products.id');
            $gtrans_unit_id = Unit::where('business_id', $business_id)->where('short_name', 'GTrans')->first()->id;

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
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can("product.view")) {
                            return  action('ProductController@view', [$row->id]) ;
                        } else {
                            return '';
                        }
                }])
                ->rawColumns(['product', 'is_display_front'])
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
        return view('dashboard_transfer.index')
            ->with(compact('not_linked_payments'));
    }

    public function updateDisplayFront(Request $request, $id){
        $is_display_front = $request->get('is_display_front');
        Product::find($id)->update(['is_display_front' => $is_display_front]);
        return ['success' => true];
    }
}
