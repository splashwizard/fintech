@foreach($game_data as $game)
<strong>{{$game->name}}</strong>
<p class="text-muted">
{{$game->game_id}}
</p>
@endforeach