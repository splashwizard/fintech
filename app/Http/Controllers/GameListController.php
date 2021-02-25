<?php

namespace App\Http\Controllers;

use App\AccountTransaction;

use App\BusinessLocation;
use App\ExpenseCategory;
use App\Promotion;
use App\PromotionCollection;
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

class GameListController extends Controller
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

            $expenses = Promotion::join('promotion_collections', 'promotion_collections.id', 'promotions.collection_id')
            ->select(
                'promotions.*',
                'promotions.promotion_id as no',
                'promotions.updated_at as last_modified_on',
                'promotion_collections.name as collection'
            )->where('promotions.type', 'jewellery')
                ->groupBy('promotions.promotion_id')
            ->orderBy('promotions.promotion_id', 'ASC');

            
            return Datatables::of($expenses)
                ->addColumn(
                    'action',
                    '<a href="{{action(\'GameListController@edit\', [$promotion_id])}}" type="button" class="btn btn-info dropdown-toggle btn-xs">
                        Edit
                    </a>'
                )
                ->editColumn('show',
                    '
                    @if($show == "active")
                        <span class="badge btn-success"> Active </span>
                    @else
                        <span class="badge btn-danger"> Inactive </span>
                    @endif
                    '
                    )
                ->editColumn('sale', function ($row) {
                    return  '<input type="checkbox" class="checkbox_game_sale" data-id="'.$row->id.'"'. ($row->sale ? 'checked' : null) .'>' ;
                })
                ->editColumn('new', function ($row) {
                    return  '<input type="checkbox" class="checkbox_game_new" data-id="'.$row->id.'"'. ($row->new ? 'checked' : null) .'>' ;
                })
                ->rawColumns(['action', 'show', 'sale', 'new'])
                ->make(true);
        }

        $business_id = request()->session()->get('user.business_id');

        $categories = ExpenseCategory::where('business_id', $business_id)
                            ->pluck('name', 'id');

        $users = User::forDropdown($business_id, false, true, true);

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('game_list.index')
            ->with(compact('categories', 'business_locations', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $promotion_langs = PromotionLang::forDropdown([], false);
        $promotion_collections = PromotionCollection::forDropdown( false);
        
        return view('game_list.create')
            ->with(compact('promotion_langs', 'promotion_collections'));
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

            $input = $request->only(['lang_id', 'title', 'sub_title', 'content', 'start_time', 'collection_id', 'end_time', 'sequence']);
            if(!empty($request->get('show')))
                $input['show'] = 'active';
            else
                $input['show'] = 'inactive';
            $input['type'] = 'jewellery';
            $input['promotion_id'] = $this->promotionUtil->generatePromotionID();
            if ($request->hasFile('desktop_imageUpload')){
                $uploaded_file_name = $this->promotionUtil->uploadFile($request, 'desktop_imageUpload', 'promotion_images');
                $input['desktop_image'] = '/uploads/promotion_images/'.$uploaded_file_name;
            }
            if ($request->hasFile('mobile_imageUpload')){
                $uploaded_file_name = $this->promotionUtil->uploadFile($request, 'mobile_imageUpload', 'promotion_images');
                $input['mobile_image'] = '/uploads/promotion_images/'.$uploaded_file_name;
            }

            Promotion::create($input);
            $output = ['success' => 1,
                            'msg' => __('promotion.promotion_add_success')
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return redirect('game_list')->with('status', $output);
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
        $promotions = Promotion::join('promotion_langs', 'promotion_langs.id', 'lang_id')
            ->where('promotions.promotion_id', $id)
            ->orderBy('lang_id', 'ASC')
            ->select('promotions.*', 'promotion_langs.lang as lang')->get();
        $promotion_langs = PromotionLang::forDropdown([], false);
        $promotion_collections = PromotionCollection::forDropdown(false);
        return view('game_list.edit')
            ->with(compact('promotions', 'id', 'promotion_langs', 'promotion_collections'));
    }

    public function getTab($promotion_id, $form_index)
    {
        $selected_langs = [];
        $data = Promotion::where('promotion_id', $promotion_id)->get();
        foreach ($data as $row){
            $selected_langs[] = $row->lang_id;
        }
        $promotion_langs = PromotionLang::forDropdown($selected_langs, false);
        $promotion_collections = PromotionCollection::forDropdown(false);
        return ['html' => view('game_list.form')->with(['form_index' => $form_index, 'promotion' => null, 'promotion_langs' => $promotion_langs, 'promotion_collections' => $promotion_collections])->render() ];
    }

<<<<<<< HEAD
=======
    public function updateGameSale(Request $request, $id){
        $is_sale = $request->get('is_sale');
        Promotion::find($id)->update(['sale' => $is_sale]);
        return ['success' => true];
    }

    public function updateGameNew(Request $request, $id){
        $is_new = $request->get('is_new');
        Promotion::find($id)->update(['new' => $is_new]);
        return ['success' => true];
    }

>>>>>>> 2102422 (Game 50a)
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
                $promotion_cnt = Promotion::where('promotion_id', $id)->where('lang_id', $form['lang_id'])->count();
                if($promotion_cnt == 0){
//                    $input = $form->only(['lang_id', 'title', 'sub_title', 'content', 'start_time', 'end_time', 'sequence']);
                    $input = $form;
                    if(!empty($form['show']))
                        $input['show'] = 'active';
                    else
                        $input['show'] = 'inactive';
                    $input['promotion_id'] = $id;
                    $desktop_file_key = "form_".$key."_desktop_imageUpload";
                    if ($request->hasFile($desktop_file_key)){
                        $uploaded_file_name = $this->promotionUtil->uploadFile($request, $desktop_file_key, 'promotion_images');
                        $input['desktop_image'] = '/uploads/promotion_images/'.$uploaded_file_name;
                    }
                    $mobile_file_key = "form_".$key."_mobile_imageUpload";
                    if ($request->hasFile($mobile_file_key)){
                        $uploaded_file_name = $this->promotionUtil->uploadFile($request, $mobile_file_key, 'promotion_images');
                        $input['mobile_image'] = '/uploads/promotion_images/'.$uploaded_file_name;
                    }

                    Promotion::create($input);
                } else {
                    $input = $form;
                    if(!empty($form['show']))
                        $input['show'] = 'active';
                    else
                        $input['show'] = 'inactive';
                    $desktop_file_key = "form_".$key."_desktop_imageUpload";
                    if ($request->hasFile($desktop_file_key)){
                        $uploaded_file_name = $this->promotionUtil->uploadFile($request, $desktop_file_key, 'promotion_images');
                        $input['desktop_image'] = '/uploads/promotion_images/'.$uploaded_file_name;
                    }
                    $mobile_file_key = "form_".$key."_mobile_imageUpload";
                    if ($request->hasFile($mobile_file_key)){
                        $uploaded_file_name = $this->promotionUtil->uploadFile($request, $mobile_file_key, 'promotion_images');
                        $input['mobile_image'] = '/uploads/promotion_images/'.$uploaded_file_name;
                    }

                    Promotion::where('promotion_id', $id)->where('lang_id', $form['lang_id'])->update($input);
                }
            }

            $output = ['success' => 1,
                            'msg' => __('promotion.promotion_update_success')
                        ];
//        } catch (\Exception $e) {
//            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
//
//            $output = ['success' => 0,
//                            'msg' => __('messages.something_went_wrong')
//                        ];
//        }
//
        return redirect('game_list')->with('status', $output);
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
