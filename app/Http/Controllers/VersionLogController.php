<?php

namespace App\Http\Controllers;

use App\Account;
use App\Business;
use App\BusinessLocation;

use App\Contact;
use App\Currency;
use App\Project;
use App\Transaction;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;

use App\Utils\TransactionUtil;
use App\VariationLocationDetails;

use Charts;

use Datatables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\TransactionPayment;

class VersionLogController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
    ) {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $version_log = Project::find(1)->version_log;
        return view('version_log.index', compact('version_log'));
    }
    public function update(Request $request)
    {
        if($request->ajax()){
            try{
                $version_log = $request->get('version_log');
                $project = Project::find(1);
                $project->version_log = $version_log;
                $project->save();
                $output = ['success' => 1,
                    'msg' => __("messages.version_log_updated_successfully")
                ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = ['success' => 0,
                    'msg' => __("messages.something_went_wrong")
                ];
            }
            return $output;
        }
    }
}
