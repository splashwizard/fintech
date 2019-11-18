<?php

namespace App\Http\Controllers\Restaurant;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Datatables;

//use App\Restaurant\ResTable;
use App\Business;
use App\BusinessLocation;
use App\SstReport;
use DB;

class SstreportController extends Controller
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
           //$categories = Category::forDropdown($business_id);


            $tables = SstReport::where('sst_report.business_id', $business_id)
                         ->join('business AS BL', 'sst_report.business_id', '=', 'BL.id')
                        // ->join('business AS bu', 'bu.id', '=', 'res_tables.business_id')     
                        // ->select(['res_tables.name as name', 'BL.name as location',
                        //     'res_tables.description', 'res_tables.id', 'bu.tax_number_1']);
                         ->select(['BL.tax_number_1','sst_report.customs_code','sst_report.id','sst_report.contact_person', 'sst_report.designation' , 'sst_report.total_sales_manual','sst_report.total_tax_manual','sst_report.total_tax_actual','total_sales_actual','sst_report.contact_ic','sst_report.tax_type','sst_report.description1','sst_report.start_date', 'sst_report.end_date', 'sst_report.date_return_due','sst_report.customs_code','imported_salestax','jadual_c']);

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $tables->whereDate('sst_report.start_date', '>=', $start)
                            ->whereDate('sst_report.start_date', '<=', $end);
            }

            if (!empty(request()->input('contact_ic'))) {
                $tables->where('sst_report.contact_ic', request()->input('contact_ic'));
            }


            //$tables->toArray();
           // dd($tables);

            return Datatables::of($tables)
                ->addColumn(
                    'action',
                    '@role("Admin#' . $business_id . '")
                    <button data-href="{{action(\'Restaurant\SstreportController@edit\', [$id])}}" class="btn btn-xs btn-primary edit_table_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                    @endrole
                    @role("Admin#' . $business_id . '")
                         <button data-href="{{action(\'Restaurant\SstreportController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_table_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>  
                    @endrole
                    @role("Admin#' . $business_id . '")
                         <button data-href="{{action(\'ReportController@test\', [$id])}}" class="btn btn-xs btn-primary edit_table_button"><i class="glyphicon glyphicon-edit"></i> Update</button>  
                    @endrole'
                )
                ->addColumn('print', function ($row) {
                    if ($row->id) {
                   // return '<a href="/fpdi/print/pdf/sstp2n.php?id="'.$row->id.'>Visit our HTML tutorial</a>' ;
                    //return '<a href="/fpdi/print/pdf/sstp2n.php?id='. $row->id .' asdsadsds</a>';
                     return ' 
                     <div class="input-group-btn">
                  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"  aria-label="Print" > <i class="fa fa-print"></i> Print
                    <span class="fa fa-caret-down"></span></button>
                  <ul class="dropdown-menu">   
                     <div class="col-sm-12">
                            <a class="btn btn-primary pull-right"   aria-label="Print" 
                            href=/fpdi/print/pdf/sstp1n.php?id=' . $row->id . '  '. 'target="_blank">
                            <i class="fa fa-print"></i> Print Page 1</a>
                     </div>
                        <div class="col-sm-12">
                               <a class="btn btn-primary pull-right"   aria-label="Print" 
                            href=/fpdi/print/pdf/sstp2n.php?id=' . $row->id . '  '. 'target="_blank">
                            <i class="fa fa-print"></i> Print Page 2</a>
                        </div>
                        <div class="col-sm-12">
                               <a class="btn btn-primary pull-right"   aria-label="Print" 
                            href=/fpdi/print/pdf/sstp3n.php?id=' . $row->id . '  '. 'target="_blank">
                            <i class="fa fa-print"></i> Print Page 3</a>
                        </div>
                        <div class="col-sm-12">
                               <a class="btn btn-primary pull-right"   aria-label="Print" 
                            href=/fpdi/print/pdf/sstp4n.php?id=' . $row->id . '  '. 'target="_blank">
                            <i class="fa fa-print"></i> Print Page 4</a>
                        </div>
                        <div class="col-sm-12">
                               <a class="btn btn-primary pull-right"   aria-label="Print" 
                            href=/fpdi/print/pdf/sstp5n.php?id=' . $row->id . '  '. 'target="_blank">
                            <i class="fa fa-print"></i> Print Page 5</a>

                        </div> 
                        <li class="divider"></li>
                            <li>asasas</li>
         
      
            
        </div>';
                    } 
                })
                ->editColumn('tax_type', function ($row) {
                    if ($row->tax_type == '1') {
                       // return 'Sales Tax '. $row->id .' dsadsad';
                        return 'Sales Tax';
                    } elseif($row->tax_type == '2') {
                        return 'Service Tax';
                    }
                })
                //  ->editColumn('customs_code', function ($row) {
                //     if ($row->customs_code == NULL) {
                //         return '9401.20.1000';
                //     }
                // })
             
                ->editColumn('start_date', '{{@format_date($start_date)}}')
                ->editColumn('end_date', '{{@format_date($end_date)}}')
                ->editColumn('date_return_due', '{{@format_date($date_return_due)}}')
                ->removeColumn('id')
                ->escapeColumns(['action'])

                ->make(true);
        }

        return view('restaurant.sstreport.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $first_name = request()->session()->get('user.first_name');
        $last_name = request()->session()->get('user.last_name');
        $business_locations = BusinessLocation::forDropdown($business_id);
        $tax_type = array('1'=>"Sales Tax", '2'=>"Service Tax");

        return view('restaurant.sstreport.create')
            ->with(compact('business_locations','tax_type','first_name','last_name'));
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
            $input = $request->only(['contact_no','designation','customs_code','date_return_due','start_date','contact_person', 'total_sales_manual', 'contact_ic','tax_type','description1','end_date','imported_salestax','jadual_c']);
            $business_id = $request->session()->get('user.business_id');
            
            //Calculate actual tax_amount
            // $request = DB::table('transactions')
            //         ->select('business_id', DB::raw('SUM(tax_amount) as tax_amount'))
            //           ->where('business_id', $business_id)
            //         //->whereBetween('transaction_date','2019-01-01' , '2019-12-01')
            //         ->where('transaction_date', '>=', $input['start_date'])
            //         ->where('transaction_date   ', '<=', $input['end_date'])
            //         ->groupBy('business_id')
            //         ->havingRaw('SUM(tax_amount) > ?', [0])
            //         ->get();

            $input['business_id'] = $business_id;
            if($input['tax_type'] == 1){
                $input['total_tax_manual'] = $input['total_sales_manual'] * 0.1 ;
            } elseif($input['tax_type'] == 2){
                $input['total_tax_manual'] = $input['total_sales_manual'] * 0.06 ;
            }
            //$input['created_by'] = $request->session()->get('user.id');
            $date = date('Y-m-d H:i:s');
            // $input['start_date'] = $date;
            // $input['end_date'] = $date;
            //$input['date_return_due'] = $date;
            // $input['created_by'] = $request->session()->get('user.id');
            // $input['owner_id'] = $request->session()->get('user.id');
            // $input['currency_id'] = 75 ;
            // $input['default_sales_tax'] = NULL ;
            //dd($input);exit;

         
           
            //dd($transactions);exit; 
            // $id = SstReport::create($input)->id;       
            $table = SstReport::create($input);

            // foreach ($transactions as $transaction) {
            //              DB::table('sst_report')
            //              ->where('id', $table)
            //              ->update(['total_tax_actual' => $transaction->tax_amount]); 
            // } 
           


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
        return view('restaurant.sstreport.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($id)
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $table = SstReport::where('id', $business_id)->find($id);

            return view('restaurant.sstreport.edit')
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
                $input = $request->only(['contact_person', 'total_tax_manual','contact_ic']);
                $business_id = $request->session()->get('user.business_id');

                $table = SstReport::where('id', $business_id)->findOrFail($id);
                $table->contact_person = $input['contact_person'];
                $table->total_tax_manual = $input['total_tax_manual'];
                $table->contact_ic = $input['contact_ic'];
                //$table->total_sales_manual = 90.00 ;
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

                $table = SstReport::where('business_id', $business_id)->findOrFail($id);
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
    
    public function print($id)
    {
        echo $id;exit;
        //  if (request()->ajax()) {
        //     try {
        //         $business_id = request()->user()->business_id;

        //         $table = SstReport::where('business_id', $business_id)->findOrFail($id);
        //         //dd($table);exit;
        //         //$table->delete();

        //         $output = ['success' => true,
        //                     'msg' => __("lang_v1.deleted_success")
        //                     ];
        //     } catch (\Exception $e) {
        //         \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
        //         $output = ['success' => false,
        //                     'msg' => __("messages.something_went_wrong")
        //                 ];
        //     }

        //     return $output;
        // }
        // $input_tax_details = $this->transactionUtil->getInputTax($business_id, $input['start_date'], $input['end_date']);
        // dd($input_tax_details);


    }
}
