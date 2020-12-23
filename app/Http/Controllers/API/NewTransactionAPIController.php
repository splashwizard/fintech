<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\NewTransactions;
use Illuminate\Http\Request;



class NewTransactionAPIController extends Controller
{
    public function store(Request $request) {
        try {
            $input = $request->only(['bank', 'deposit_method', 'amount', 'reference_number']);
            $input['client_id'] = $request->post('user_id');
            if ($request->hasFile('image')){
                $input['receipt_url'] = time().'.'.$request->image->getClientOriginalName();
                $request->image->move(public_path('/uploads/receipt_images'), $input['receipt_url']);
            }
            NewTransactions::create($input);
            $output = ['success' => true, 'msg' => 'Created Successfully'];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false, 'msg' => __("messages.something_fwent_wrong")
            ];
        }
        return $output;
    }
}
