<?php

namespace Modules\Essentials\Http\Controllers;

use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Http\Request;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\Reminder;

class ReminderController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $commonUtil;
    protected $moduleUtil;

    public function __construct(Util $commonUtil, ModuleUtil $moduleUtil)
    {
        $this->commonUtil = $commonUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');

        if (request()->ajax()) {
            $start_date = request()->start;
            $end_date = request()->end;
         
            //reminders
            $reminders = Reminder::where('business_id', $business_id)
                               ->where('user_id', $user_id)
                               ->get();

            $events = [];
            foreach ($reminders as $reminder) {
                $reminder_date = $reminder->date;
                // $time = $this->commonUtil->format_time($reminder->time);

                if ($reminder->repeat == "every_day" || $reminder->repeat == "every_month" || $reminder->repeat == "every_week") {
                    while ($reminder_date <= $end_date) {
                        $date = $reminder_date.' '.$reminder->time;
                        $events[] = [
                              'title' => $reminder->name,
                              'start' => $date,
                              'end' => $date,
                              'name' => $reminder->name,
                              // 'time' => $time,
                              'repeat' => $reminder->repeat,
                              'url' => action('\Modules\Essentials\Http\Controllers\ReminderController@show', [$reminder->id]),
                              'allDay' => false,
                              ];

                        $dt = strtotime($reminder_date);

                        if ($reminder->repeat == "every_day") {
                            $reminder_date= date("Y-m-d", strtotime("+1 day", $dt));
                        } elseif ($reminder->repeat == "every_month") {
                            $reminder_date= date("Y-m-d", strtotime("+1 month", $dt));
                        } elseif ($reminder->repeat == "every_week") {
                            $reminder_date= date("Y-m-d", strtotime("+1 weeks", $dt));
                        }
                    }
                } elseif ($reminder->repeat == "one_time") {
                    $date = $reminder_date.' '.$reminder->time;
                    $events[] = [
                            'title' => $reminder->name,
                            'start' => $date,
                            'end' => $date,
                            'name' => $reminder->name,
                            // 'time' => $time,
                            'repeat' => $reminder->repeat,
                            'url' => action('\Modules\Essentials\Http\Controllers\ReminderController@show', [$reminder->id]),
                            'allDay' => false,
                            ];
                }
            }
          
            return $events;
        }

        return view('essentials::reminder.index');
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

                $input = $request->only(['name', 'date', 'repeat', 'time']);
            
                $reminder['date'] = $this->commonUtil->uf_date($input['date']);
                $reminder['name'] = $input['name'];
                $reminder['repeat'] = $input['repeat'];
                $reminder['time'] = $input['time'];
                $reminder['user_id'] = $user_id;
                $reminder['business_id'] = $business_id;

                Reminder::create($reminder);

                $output = [
                        'success' => true,
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
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $user_id = request()->session()->get('user.id');

            $reminder = Reminder::where('business_id', $business_id)
                              ->where('user_id', $user_id)
                              ->find($id);

            $time = $this->commonUtil->format_time($reminder->time);
        
            $repeat = [
                'one_time' => __('essentials::lang.one_time'),
                'every_day' => __('essentials::lang.every_day'),
                'every_week' => __('essentials::lang.every_week'),
                'every_month' => __('essentials::lang.every_month'),
                  ];

            return view('essentials::reminder.show')
                ->with(compact('reminder', 'time', 'repeat'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $user_id = request()->session()->get('user.id');

                $repeat = $request->only('repeat');

                Reminder::where('business_id', $business_id)
                    ->where('user_id', $user_id)
                    ->where('id', $id)
                    ->update($repeat);

                $output = ['success' => true,
                      'msg' => trans("lang_v1.updated_success")
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
                $business_id = request()->session()->get('user.business_id');
                $user_id = request()->session()->get('user.id');

                Reminder::where('business_id', $business_id)
                  ->where('user_id', $user_id)
                  ->where('id', $id)
                  ->delete();

                $output = ['success' => true,
                          'msg' => trans("lang_v1.deleted_success")
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
