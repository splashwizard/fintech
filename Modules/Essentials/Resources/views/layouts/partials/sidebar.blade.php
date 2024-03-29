@if($__is_essentials_enabled)
<li class="bg-navy treeview {{ in_array($request->segment(1), ['essentials']) ? 'active active-sub' : '' }}">
    <a href="#">
        <i class="fa fa-check-circle-o"></i>
        <span class="title"> 12. @lang('essentials::lang.essentials')</span>
        <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
        </span>
    </a>

    <ul class="treeview-menu">
        <li class="{{ $request->segment(2) == 'todo' ? 'active active-sub' : '' }}">
            <a href="{{action('\Modules\Essentials\Http\Controllers\ToDoController@index')}}">
                <i class="fa fa-list-ul"></i>
                <span class="title">12.1 @lang('essentials::lang.todo')</span>
            </a>
        </li>
		<li class="{{ ($request->segment(2) == 'document' && $request->get('type') != 'memos') ? 'active active-sub' : '' }}">
				<a href="{{action('\Modules\Essentials\Http\Controllers\DocumentController@index')}}">
				<i class="fa fa-file"></i>
				<span class="title"> 12.2 @lang('essentials::lang.document') </span>
			</a>
		</li>
        <li class="{{ ($request->segment(2) == 'document' && $request->get('type') == 'memos') ? 'active active-sub' : '' }}">
            <a href="{{action('\Modules\Essentials\Http\Controllers\DocumentController@index') .'?type=memos'}}">
                <i class="fa fa-envelope-open"></i>
                <span class="title">
                    12.3 @lang('essentials::lang.memos')
                </span>
            </a>
        </li>
        <li class="{{ $request->segment(2) == 'reminder' ? 'active active-sub' : ''}}">
            <a href="{{action('\Modules\Essentials\Http\Controllers\ReminderController@index')}}">
                <i class="fa fa-bell"></i>
                <span class="title">
                    12.4 @lang('essentials::lang.reminders')
                </span>
            </a>
        </li>
        @if(auth()->user()->can('essentials.view_message') || auth()->user()->can('essentials.create_message'))
        <li class="{{ $request->segment(2) == 'messages' ? 'active active-sub' : ''}}">
            <a href="{{'/messages'}}">
                <i class="fa fa-comments-o"></i>
                <span class="title">
                    12.5 @lang('essentials::lang.messages')
                </span>
            </a>
        </li>
        @endif
    </ul>
</li>
@endif