<?php

namespace Modules\Essentials\Http\Controllers;

use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\View;
use Modules\Essentials\Entities\ToDo;

class ToDoController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $commonUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param CommonUtil
     * @return void
     */
    public function __construct(Util $commonUtil, ModuleUtil $moduleUtil)
    {
        $this->commonUtil = $commonUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }
   
        if (request()->ajax()) {
            $date = $request->get('date');
            $current_date = $this->commonUtil->uf_date($date);
            
            $user_id = request()->session()->get('user.id');

            $todo = ToDo::where('business_id', $business_id)
                      ->where('user_id', $user_id)
                      ->where('date', $current_date)
                      ->get();
            
            $view = View::make('essentials::todo.show')->with(compact('todo'))->render();
            $output = [
                        'success' => true,
                        'html' => $view,
                    ];

            return $output;
        }
        
        return view('essentials::todo.index');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $user_id = $request->session()->get('user.id');

                $input = $request->only('task', 'date');
                
                $todo['date'] = $this->commonUtil->uf_date($input['date']);
                $todo['task'] = $input['task'];
                $todo['business_id'] = $business_id;
                $todo['user_id'] = $user_id;
                $todo['is_completed'] = 0;

                $to_dos = ToDo::create($todo);

                $view = View::make('essentials::todo.todo_append')->with(compact('to_dos'))->render();
                
                $output = [
                          'success' => true,
                          'html' => $view,
                          'date' => $input['date'],
                          'msg' => __('lang_v1.success')
                        ];

                return $output;
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = [
                            'success' => false,
                            'msg' => __('messages.something_went_wrong')
                            ];

                return back()->with('status', $output);
            }
        }
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $business_id = $request->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $todo['is_completed'] = $request->get('is_completed');

                ToDo::where('id', $id)
                      ->update($todo);
                
                $output = [
                          'success' => true,
                          'is_completed' => $todo['is_completed'],
                          'msg' => __('lang_v1.success')
                        ];

                return $output;
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = [
                            'success' => false,
                            'msg' => __('messages.something_went_wrong')
                            ];

                return back()->with('status', $output);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $todo = ToDo::destroy($id);

                $output = [
                          'success' => true,
                          'is_deleted' => $todo,
                          'msg' => __('lang_v1.success')
                        ];

                return $output;
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = [
                            'success' => false,
                            'msg' => __('messages.something_went_wrong')
                            ];

                return back()->with('status', $output);
            }
        }
    }
}
