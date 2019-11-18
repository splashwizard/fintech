<!-- Post -->
<div class="post" style="margin-left: 15px; margin-right: 15px;">
  	<div class="user-block">
        <span class="username" style="margin-left: 0;">
          <span class="text-primary">{{$message->sender->user_full_name}}</span>
          @if($message->user_id == auth()->user()->id)
          	<a href="{{action('\Modules\Essentials\Http\Controllers\EssentialsMessageController@destroy', [$message->id])}}" class="pull-right btn-box-tool chat-delete" title="@lang('messages.delete')"><i class="fa fa-times text-danger"></i></a>
          @endif
        </span>
    	<span class="description" style="margin-left: 0;"><small><i class="fa fa-clock-o"></i> {{$message->created_at->diffForHumans()}}</small></span>
  	</div>
  	<!-- /.user-block -->
  	<p>
    	{!! strip_tags($message->message, '<br>') !!}
  	</p>
</div>
<!-- /.post -->