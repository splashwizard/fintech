@if(count($todo) > 0)
	@foreach($todo as $do)
	<li class="todo_li">
		@if($do->is_completed == 1)
		<input type="checkbox" name="todo_id" class="todo_id" value ="{{ $do->id }}" checked>

		<span class="text task_name" style="text-decoration:line-through;">
			{{ $do->task }}
		</span>
		<i class="fa fa-trash text-danger pull-right delete_task cursor-pointer" style="display:none;">
			<span class="hidden">{{ $do->id }}</span>
		</i>
		@else
		<input type="checkbox" name="todo_id" class="todo_id" value ="{{ $do->id }}">

		<span class="text task_name"> {{ $do->task }}</span>
		<i class="fa fa-trash text-danger pull-right delete_task cursor-pointer" style="display:none;">
			<span class="hidden">{{ $do->id }}</span>
		</i>
		@endif
	</li>
	@endforeach
@else
	<h2 class="text-center text-info">
		{{ __('essentials::lang.no_task') }}
	</h2>
@endif