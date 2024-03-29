<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Notice;
use Illuminate\Http\Request;

class NoticeAPIController extends Controller
{
    public function notices(Request $request) {
        try {
            $business_id = $request->get('business_id');
            $expenses = Notice::select(
                'notice_id as no',
                'lang_id',
                'title',
                'content',
                'desktop_image',
                'mobile_image',
                'sequence',
                'show',
                'start_time',
                'end_time',
                'updated_at as last_modified_on'
            )->where('business_id', $business_id)
            ->groupBy('notice_id')
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
