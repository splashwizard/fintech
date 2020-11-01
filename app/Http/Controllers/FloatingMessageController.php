<?php

namespace App\Http\Controllers;

use App\AccountTransaction;

use App\BusinessLocation;
use App\ExpenseCategory;
use App\FloatingMessage;
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

class FloatingMessageController extends Controller
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
        $floating_messages = FloatingMessage::join('promotion_langs', 'promotion_langs.id', 'lang_id')
            ->orderBy('lang_id', 'ASC')
            ->select('floating_messages.*', 'promotion_langs.lang as lang')->get();
        $promotion_langs = PromotionLang::forDropdown([], false);
        return view('floating_message.edit')
            ->with(compact('floating_messages', 'promotion_langs'));
    }

    public function getTab($id)
    {
        $selected_langs = [];
        $data = FloatingMessage::get();
        foreach ($data as $row){
            $selected_langs[] = $row->lang_id;
        }
        $promotion_langs = PromotionLang::forDropdown($selected_langs, false);
        return ['html' => view('floating_message.form')->with(['form_index' => $id, 'floating_message' => null, 'promotion_langs' => $promotion_langs])->render() ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
//        try {
            $forms = $request->get('form');
            foreach ($forms as $key => $form){
                $promotion_cnt = FloatingMessage::where('lang_id', $form['lang_id'])->count();
                if($promotion_cnt == 0){
                    $input = $form;

                    FloatingMessage::create($input);
                } else {
                    $input = $form;
                    FloatingMessage::where('lang_id', $form['lang_id'])->update($input);
                }
            }

            $output = ['success' => 1,
                            'msg' => "Floating message updated successfully"
                        ];
//        } catch (\Exception $e) {
//            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
//
//            $output = ['success' => 0,
//                            'msg' => __('messages.something_went_wrong')
//                        ];
//        }
//
        return redirect('floating_message')->with('status', $output);
    }

}
