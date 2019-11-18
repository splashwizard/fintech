<?php

Route::group(['middleware' => ['web','IsInstalled', 'auth', 'SetSessionData', 'language', 'timezone'], 'namespace' => 'Modules\Essentials\Http\Controllers'], function () {
    Route::group(['prefix' => 'essentials'], function () {
        Route::get('/install', 'InstallController@index');
        Route::get('/install/update', 'InstallController@update');
        
        Route::get('/', 'EssentialsController@index');

        //document controller
        Route::resource('document', 'DocumentController')->only(['index', 'store', 'destroy', 'show']);
        Route::get('document/download/{id}', 'DocumentController@download');

        //document share controller
        Route::resource('document-share', 'DocumentShareController')->only(['edit', 'update']);

        //todo controller
        Route::resource('todo', 'ToDoController')->only(['index', 'store', 'update', 'destroy']);

        //reminder controller
        Route::resource('reminder', 'ReminderController')->only(['index', 'store', 'edit', 'update', 'destroy', 'show']);

        //message controller
        Route::resource('messages', 'EssentialsMessageController')->only(['index', 'store','destroy']);
    });

    Route::group(['prefix' => 'hrm'], function () {
        Route::resource('/leave-type', 'EssentialsLeaveTypeController');
        Route::resource('/leave', 'EssentialsLeaveController');
        Route::post('/change-status', 'EssentialsLeaveController@changeStatus');
        Route::get('/leave/activity/{id}', 'EssentialsLeaveController@activity');
        Route::get('/user-leave-summary', 'EssentialsLeaveController@getUserLeaveSummary');

        Route::get('/settings', 'EssentialsSettingsController@edit');
        Route::post('/settings', 'EssentialsSettingsController@update');

        Route::resource('/attendance', 'AttendanceController');
        Route::post('/clock-in-clock-out', 'AttendanceController@clockInClockOut');
        Route::get(
            '/user-attendance-summary',
            'AttendanceController@getUserAttendanceSummary'
        );

        Route::resource('/payroll', 'PayrollController');
        Route::resource('/holiday', 'EssentialsHolidayController');
    });
});
