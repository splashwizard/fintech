<?php

namespace App\Http\Controllers\API;

use App\FloatingMessage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class FloatingMessageAPIController extends Controller
{
    public function index(Request $request) {
        try {
            $business_id = $request->get('business_id');
            $expenses = FloatingMessage::select(
                'lang_id',
                'title'
            )->where('business_id', $business_id)
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
