<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\BankBrand;
use App\DashboardBonus;
use App\Http\Controllers\Controller;
use App\NewTransactions;
use App\Notice;
use App\Product;
use App\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class NoticeAPIController extends Controller
{
    public function notices(Request $request) {
        try {
            $business_id = $request->get('business_id');
            $expenses = Notice::select(
                'notice_id as no',
                'title',
                'sequence',
                'show',
                'start_time',
                'end_time',
                'updated_at as last_modified_on'
            )->groupBy('notice_id')
            ->orderBy('notice_id', 'ASC')
            ->get();
            $output = ['success' => true, 'list' => $expenses];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false, 'msg' => __("messages.something_went_wrong")
            ];
        }
        return $output;
    }
}
