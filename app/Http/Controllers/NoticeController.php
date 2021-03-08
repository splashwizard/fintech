<?php

namespace App\Http\Controllers;

use App\AccountTransaction;

use App\BusinessLocation;
use App\ExpenseCategory;
use App\Notice;
use App\Promotion;
use App\PromotionLang;
use App\Transaction;
use App\User;
use App\Utils\ModuleUtil;


use App\Utils\NoticeUtil;
use App\Utils\TransactionUtil;

use DB;
use Illuminate\Http\Request;

use Yajra\DataTables\Facades\DataTables;
use \jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;

class NoticeController extends Controller
{
    protected $moduleUtil;
    protected $transactionUtil;
    protected $noticeUtil;
    /**
    * Constructor
    *
    * @param TransactionUtil $transactionUtil
    * @return void
    */
    public function __construct(TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, NoticeUtil $noticeUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->noticeUtil = $noticeUtil;
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

            $expenses = Notice::where('business_id', $business_id)
            ->select(
                'notices.*',
                'notice_id as no',
                'updated_at as last_modified_on'
            )->groupBy('notice_id')
            ->orderBy('notice_id', 'ASC');

            
            return Datatables::of($expenses)
                ->addColumn(
                    'action',
                    '<a href="{{action(\'NoticeController@edit\', [$notice_id])}}" type="button" class="btn btn-info dropdown-toggle btn-xs">
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
                ->editColumn('start_time',
                    '@if($start_time == "0000-00-00 00:00:00")
                        -
                    @else
                        {{$start_time}}
                    @endif')
                ->editColumn('end_time',
                    '@if($end_time == "0000-00-00 00:00:00")
                        -
                    @else
                        {{$end_time}}
                    @endif')
                ->rawColumns(['action', 'show'])
                ->make(true);
        }

        $business_id = request()->session()->get('user.business_id');

        $categories = ExpenseCategory::where('business_id', $business_id)
                            ->pluck('name', 'id');

        $users = User::forDropdown($business_id, false, true, true);

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        return view('notice.index')
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

        return view('notice.create')
            ->with(compact('promotion_langs'));
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

            $input = $request->only(['lang_id', 'title', 'sub_title', 'content', 'start_time', 'end_time', 'sequence']);
            $input['business_id'] = request()->session()->get('user.business_id');
            if(!empty($request->get('show')))
                $input['show'] = 'active';
            else
                $input['show'] = 'inactive';
            $input['notice_id'] = $this->noticeUtil->generateNoticeID();
            if ($request->hasFile('desktop_imageUpload')){
                $uploaded_file_name = $this->noticeUtil->uploadFile($request, 'desktop_imageUpload', 'notice_images');
                $input['desktop_image'] = '/uploads/notice_images/'.$uploaded_file_name;
            }
            if ($request->hasFile('mobile_imageUpload')){
                $uploaded_file_name = $this->noticeUtil->uploadFile($request, 'mobile_imageUpload', 'notice_images');
                $input['mobile_image'] = '/uploads/notice_images/'.$uploaded_file_name;
            }

            Notice::create($input);
            $output = ['success' => 1,
                            'msg' => __('notice.notice_add_success')
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        return redirect('notices')->with('status', $output);
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
        $notices = Notice::join('promotion_langs', 'promotion_langs.id', 'lang_id')
            ->where('notices.notice_id', $id)
            ->orderBy('lang_id', 'ASC')
            ->select('notices.*', 'promotion_langs.lang as lang')->get();
        $promotion_langs = PromotionLang::forDropdown([], false);
        return view('notice.edit')
            ->with(compact('notices', 'id', 'promotion_langs'));
    }

    public function getTab($notice_id, $form_index)
    {
        $selected_langs = [];
        $data = Notice::where('notice_id', $notice_id)->get();
        foreach ($data as $row){
            $selected_langs[] = $row->lang_id;
        }
        $promotion_langs = PromotionLang::forDropdown($selected_langs, false);
        return ['html' => view('notice.form')->with(['form_index' => $form_index, 'notice' => null, 'promotion_langs' => $promotion_langs])->render() ];
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
                $notice_cnt = Notice::where('notice_id', $id)->where('lang_id', $form['lang_id'])->count();
                if($notice_cnt == 0){
//                    $input = $form->only(['lang_id', 'title', 'sub_title', 'content', 'start_time', 'end_time', 'sequence']);
                    $input = $form;
                    if(!empty($form['show']))
                        $input['show'] = 'active';
                    else
                        $input['show'] = 'inactive';
                    $input['notice_id'] = $id;
                    $desktop_file_key = "form_".$key."_desktop_imageUpload";
                    if ($request->hasFile($desktop_file_key)){
                        $uploaded_file_name = $this->noticeUtil->uploadFile($request, $desktop_file_key, 'notice_images');
                        $input['desktop_image'] = '/uploads/notice_images/'.$uploaded_file_name;
                    }
                    $mobile_file_key = "form_".$key."_mobile_imageUpload";
                    if ($request->hasFile($mobile_file_key)){
                        $uploaded_file_name = $this->noticeUtil->uploadFile($request, $mobile_file_key, 'notice_images');
                        $input['mobile_image'] = '/uploads/notice_images/'.$uploaded_file_name;
                    }

                    Notice::create($input);
                } else {
                    $input = $form;
                    if(!empty($form['show']))
                        $input['show'] = 'active';
                    else
                        $input['show'] = 'inactive';
                    $desktop_file_key = "form_".$key."_desktop_imageUpload";
                    if ($request->hasFile($desktop_file_key)){
                        $uploaded_file_name = $this->noticeUtil->uploadFile($request, $desktop_file_key, 'notice_images');
                        $input['desktop_image'] = '/uploads/notice_images/'.$uploaded_file_name;
                    }
                    $mobile_file_key = "form_".$key."_mobile_imageUpload";
                    if ($request->hasFile($mobile_file_key)){
                        $uploaded_file_name = $this->noticeUtil->uploadFile($request, $mobile_file_key, 'notice_images');
                        $input['mobile_image'] = '/uploads/notice_images/'.$uploaded_file_name;
                    }

                    Notice::where('notice_id', $id)->where('lang_id', $form['lang_id'])->update($input);
                }
            }

            $output = ['success' => 1,
                            'msg' => __('notice.notice_update_success')
                        ];
//        } catch (\Exception $e) {
//            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
//
//            $output = ['success' => 0,
//                            'msg' => __('messages.something_went_wrong')
//                        ];
//        }
//
        return redirect('notices')->with('status', $output);
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
