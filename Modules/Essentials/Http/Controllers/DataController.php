<?php

namespace Modules\Essentials\Http\Controllers;

use App\User;
use Illuminate\Routing\Controller;

class DataController extends Controller
{
    /**
     * Parses notification message from database.
     * @return array
     */
    public function parse_notification($notification)
    {
        $notification_data = [];
        if ($notification->type ==
            'Modules\Essentials\Notifications\DocumentShareNotification') {
            $data = $notification->data;
            $msg = __('essentials::lang.document_share_notification', ['document_name' => $data['document_name'], 'shared_by' => $data['shared_by_name']]);

            $link = $data['document_type'] != 'memos' ? action('\Modules\Essentials\Http\Controllers\DocumentController@index') :
            action('\Modules\Essentials\Http\Controllers\DocumentController@index') .'?type=memos';

            $icon = $data['document_type'] != 'memos' ? "fa fa-file text-success"
                    : "fa fa-envelope-open text-success";
            
            $notification_data = [
                'msg' => $msg,
                'icon_class' => $icon,
                'link' => $link,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans()
            ];
        } elseif ($notification->type ==
            'Modules\Essentials\Notifications\NewMessageNotification') {
            $data = $notification->data;
            $msg = __('essentials::lang.new_message_notification', ['sender' => $data['from']]);

            $notification_data = [
                'msg' => $msg,
                'icon_class' => 'fa fa-envelope text-success',
                'link' => action('\Modules\Essentials\Http\Controllers\EssentialsMessageController@index'),
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans()
            ];
        } elseif ($notification->type ==
            'Modules\Essentials\Notifications\NewLeaveNotification') {
            $data = $notification->data;

            $employee = User::find($data['applied_by']);

            $msg = __('essentials::lang.new_leave_notification', ['employee' => $employee->user_full_name, 'ref_no' => $data['ref_no']]);

            $notification_data = [
                'msg' => $msg,
                'icon_class' => 'fa fa-user-times text-success',
                'link' => action('\Modules\Essentials\Http\Controllers\EssentialsLeaveController@index'),
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans()
            ];
        } elseif ($notification->type ==
            'Modules\Essentials\Notifications\LeaveStatusNotification') {
            $data = $notification->data;

            $admin = User::find($data['changed_by']);

            $msg = __('essentials::lang.status_change_notification', ['status' => $data['status'], 'ref_no' => $data['ref_no'], 'admin' => $admin->user_full_name]);

            $notification_data = [
                'msg' => $msg,
                'icon_class' => 'fa fa-user-times text-success',
                'link' => action('\Modules\Essentials\Http\Controllers\EssentialsLeaveController@index'),
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans()
            ];
        } elseif ($notification->type ==
            'Modules\Essentials\Notifications\PayrollNotification') {
            $data = $notification->data;

            $month = \Carbon::createFromFormat('m', $data['month'])->format('F');

            $msg = '';

            $created_by = User::find($data['created_by']);
            if ($data['action'] == 'created') {
                $msg = __('essentials::lang.payroll_added_notification', ['month_year' => $month . '/' . $data['year'], 'ref_no' => $data['ref_no'] , 'created_by' => $created_by->user_full_name]);
            } elseif ($data['action'] == 'updated') {
                $msg = __('essentials::lang.payroll_updated_notification', ['month_year' => $month . '/' . $data['year'], 'ref_no' => $data['ref_no'], 'created_by' => $created_by->user_full_name]);
            }
            

            $notification_data = [
                'msg' => $msg,
                'icon_class' => 'fa fa-money text-success',
                'link' => action('\Modules\Essentials\Http\Controllers\PayrollController@index'),
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans()
            ];
        }

        return $notification_data;
    }

    /**
     * Defines user permissions for the module.
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'essentials.create_message',
                'label' => __('essentials::lang.create_message'),
                'default' => false
            ],
            [
                'value' => 'essentials.view_message',
                'label' => __('essentials::lang.view_message'),
                'default' => false
            ]
        ];
    }

    /**
     * Superadmin package permissions
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'essentials_module',
                'label' => __('essentials::lang.essentials_module'),
                'default' => false
            ]
        ];
    }
}
