<li class="todo_li">
	<input type="checkbox" name="todo_id" class="todo_id" value ="{{ $to_dos->id }}">

	<span class="text task_name"> {{ $to_dos->task}}</span>
	<i class="fa fa-trash text-danger pull-right delete_task cursor-pointer" style="display:none;">
	<span class="hidden">{{ $to_dos->id }}</span>
	</i>
</li>