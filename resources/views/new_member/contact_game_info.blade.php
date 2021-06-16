{{--@foreach($game_data as $game)--}}
{{--<strong>{{$game->name}}</strong>--}}
{{--<p class="text-muted">--}}
{{--{{$game->cur_game_id}}--}}
{{--</p>--}}
{{--@endforeach--}}
@foreach($game_data as $game_name => $balance)
    <strong>{{$game_name}}</strong>
    <p class="text-muted">
        {{$balance}}
    </p>
@endforeach