<?php

namespace App\Http\Controllers;

use App\AccountTransaction;

use App\BusinessLocation;
use App\ExpenseCategory;
use App\Page;
use App\Promotion;
use App\PromotionLang;
use App\Transaction;
use App\User;
use App\Utils\ModuleUtil;


use App\Utils\PromotionUtil;
use App\Utils\TransactionUtil;

use DB;
use Illuminate\Http\Request;

use Yajra\DataTables\Facades\DataTables;
use \jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;

class PageController extends Controller
{
    protected $moduleUtil;
    protected $transactionUtil;
    protected $promotionUtil;
    /**
    * Constructor
    *
    * @param TransactionUtil $transactionUtil
    * @return void
    */
    public function __construct(TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, PromotionUtil $promotionUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->promotionUtil = $promotionUtil;
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

            $expenses = Page::select(
                'pages.*',
                'page_id as no',
                'updated_at as last_modified_on'
            )->groupBy('page_id')
            ->orderBy('page_id', 'ASC');

            
            return Datatables::of($expenses)
                ->addColumn(
                    'action',
                    '<a href="{{action(\'PageController@edit\', [$page_id])}}" type="button" class="btn btn-info dropdown-toggle btn-xs">
                        Edit
                    </a>'
                )
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('page.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
        $pages = Page::join('promotion_langs', 'promotion_langs.id', 'lang_id')
            ->where('pages.page_id', $id)
            ->orderBy('lang_id', 'ASC')
            ->select('pages.*', 'promotion_langs.lang as lang')->get();
        $promotion_langs = PromotionLang::forDropdown([], false);
        return view('page.edit')
            ->with(compact('pages', 'id', 'promotion_langs'));
    }

    public function getTab($page_id, $form_index)
    {
        $selected_langs = [];
        $data = Page::where('page_id', $page_id)->get();
        foreach ($data as $row){
            $selected_langs[] = $row->lang_id;
        }
        $promotion_langs = PromotionLang::forDropdown($selected_langs, false);
        return ['html' => view('page.form')->with(['form_index' => $form_index, 'page' => null, 'promotion_langs' => $promotion_langs])->render() ];
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
//        try {
            $forms = $request->get('form');
            foreach ($forms as $key => $form){
                $promotion_cnt = Page::where('page_id', $id)->where('lang_id', $form['lang_id'])->count();
                if($promotion_cnt == 0){
                    $input = $form;
                    $input['page_id'] = $id;

                    Page::create($input);
                } else {
                    $input = $form;

                    Page::where('page_id', $id)->where('lang_id', $form['lang_id'])->update($input);
                }
            }

            $output = ['success' => 1,
                            'msg' => 'Page updated successfully'
                        ];
//        } catch (\Exception $e) {
//            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
//
//            $output = ['success' => 0,
//                            'msg' => __('messages.something_went_wrong')
//                        ];
//        }
//
        return redirect('pages')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('expense.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $expense = Transaction::where('business_id', $business_id)
                                        ->where('type', 'expense')
                                        ->where('id', $id)
                                        ->first();
                $expense->delete();

                //Delete account transactions
                AccountTransaction::where('transaction_id', $expense->id)->delete();

                $output = ['success' => true,
                            'msg' => __("expense.expense_delete_success")
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
