<?php

namespace App\Http\Controllers;

use App\Bankbrand;
use App\Utils\Util;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BankbrandController extends Controller
{
    /**
       * Constructor
       *
       * @param Util $commonUtil
       * @return void
       */
    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
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

            $bank_brand = Bankbrand::where('business_id', $business_id)
                                ->select(['name', 'id']);

            return Datatables::of($bank_brand)
                    ->addColumn(
                        'action',
                        '@can("customer.update")
                            <button data-href="{{action(\'BankbrandController@edit\', [$id])}}" class="btn btn-xs btn-primary edit_bank_brand_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                        @endcan

                        @can("customer.delete")
                            <button data-href="{{action(\'BankbrandController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_bank_brand_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                        @endcan'
                    )
                    ->removeColumn('id')
                    ->rawColumns([1])
                    ->make(false);
        }

        return view('bank_brand.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('customer.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('bank_brand.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $input = $request->only(['name']);
            $input['business_id'] = $request->session()->get('user.business_id');
            $input['created_by'] = $request->session()->get('user.id');

            $bank_brand = Bankbrand::create($input);
            $output = ['success' => true,
                            'data' => $bank_brand,
                            'msg' => __("lang_v1.success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return $output;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CustomerGroup  $customerGroup
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $bank_brand = Bankbrand::where('business_id', $business_id)->find($id);

            return view('bank_brand.edit')
                ->with(compact('bank_brand'));
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
        if (!auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name']);
                $business_id = $request->session()->get('user.business_id');

                $bank_brand = Bankbrand::where('business_id', $business_id)->findOrFail($id);
                $bank_brand->name = $input['name'];
                $bank_brand->save();

                $output = ['success' => true,
                            'msg' => __("lang_v1.success")
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
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('customer.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $ms = Bankbrand::where('business_id', $business_id)->findOrFail($id);
                $ms->delete();

                $output = ['success' => true,
                            'msg' => __("lang_v1.success")
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
}
