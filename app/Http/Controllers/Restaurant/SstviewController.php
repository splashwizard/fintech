<?php

namespace App\Http\Controllers\Restaurant;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Datatables;

//use App\Restaurant\ResTable;
use App\Business;
use App\BusinessLocation;

class SstviewController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        // if (!auth()->user()->can('table.view') && !auth()->user()->can('table.create')) {
        //     abort(403, 'Unauthorized action.');
        // }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $tables = Business::where('business.id', $business_id)
                        // ->join('business_locations AS BL', 'res_tables.location_id', '=', 'BL.id')
                        // ->join('business AS bu', 'bu.id', '=', 'res_tables.business_id')     
                        // ->select(['res_tables.name as name', 'BL.name as location',
                        //     'res_tables.description', 'res_tables.id', 'bu.tax_number_1']);
                         ->select(['business.id','business.name as name', 'business.tax_label_1','business.tax_number_1']);
            //$tables->toArray();
           // dd($tables);

            return Datatables::of($tables)
                ->addColumn(
                    'action',
                    '@role("Admin#' . $business_id . '")
                    <button data-href="{{action(\'Restaurant\SstviewController@edit\', [$id])}}" class="btn btn-xs btn-primary edit_table_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                    @endrole'
                    // @role("Admin#' . $business_id . '")
                    //     <button data-href="{{action(\'Restaurant\SstviewController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_table_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    // @endrole'
                )
                ->removeColumn('id')
                ->escapeColumns(['action'])
                ->make(true);
        }

        return view('restaurant.sstview.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('restaurant.sstview.create')
            ->with(compact('business_locations'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        // if (!auth()->user()->can('table.create')) {
        //     abort(403, 'Unauthorized action.');
        // }

        try {
            $input = $request->only(['name', 'tax_label_1', 'tax_number_1']);
            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;
            $input['created_by'] = $request->session()->get('user.id');
            $input['owner_id'] = $request->session()->get('user.id');
            $input['currency_id'] = 75 ;
            $input['default_sales_tax'] = NULL ;
            dd($input);exit;
            $table = Business::create($input);


            $output = ['success' => true,
                            'data' => $table,
                            'msg' => __("lang_v1.added_success")
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
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
        return view('restaurant.sstview.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($id)
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $table = Business::where('id', $business_id)->find($id);

            return view('restaurant.sstview.edit')
                ->with(compact('table'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request, $id)
    {
        // if (!auth()->user()->can('table.update')) {
        //     abort(403, 'Unauthorized action.');
        // }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'tax_label_1','tax_number_1']);
                $business_id = $request->session()->get('user.business_id');

                $table = Business::where('id', $business_id)->findOrFail($id);
                $table->name = $input['name'];
                $table->tax_label_1 = $input['tax_label_1'];
                $table->tax_number_1 = $input['tax_number_1'];
                $table->save();

                $output = ['success' => true,
                            'msg' => __("lang_v1.updated_success")
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
     * @return Response
     */
    public function destroy($id)
    {
        // if (!auth()->user()->can('table.delete')) {
        //     abort(403, 'Unauthorized action.');
        // }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $table = Business::where('business_id', $business_id)->findOrFail($id);
                $table->delete();

                $output = ['success' => true,
                            'msg' => __("lang_v1.deleted_success")
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
