@extends('layouts.app')
@section('title', __('promotion.edit_promotion'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Edit @lang('lang_v1.floating_message')</h1>
</section>

<!-- Main content -->
<section class="content">
  {!! Form::open(['url' => action('FloatingMessageController@update'), 'method' => 'POST', 'id' => 'update_floating_message_form', 'files' => true ]) !!}
  <div class="box box-solid">
    <div class="box-body">
      <button class="btn btn-success" id="new_tab">New Tab</button>
      <input id="form_cnt" value="{{count($floating_messages)}}" hidden>
      <div id="exTab2">
        <ul class="nav nav-tabs">
          @foreach($floating_messages as $key => $floating_message)
            @if($key == 0)
              <li class="active">
                <a href="#tab0" data-toggle="tab">(Default){{$floating_message['lang']}}</a>
              </li>
            @else
              <li>
                <a href="#tab{{$key}}" data-toggle="tab">{{$floating_message['lang']}}</a>
              </li>
            @endif
          @endforeach
        </ul>

        <div class="tab-content">
          @foreach($floating_messages as $key => $floating_message)
            @if($key == 0)
              <div class="tab-pane active" id="tab0">
                @include('floating_message.form', ['form_index' => 0, 'floating_message' => $floating_message, 'promotion_langs' => $promotion_langs])
              </div>
            @else
              <div class="tab-pane" id="tab{{$key}}">
                @include('floating_message.form', ['form_index' => $key, 'floating_message' => $floating_message, 'promotion_langs' => $promotion_langs])
              </div>
            @endif
          @endforeach
        </div>
        <button class="btn" style="background-color: #000000;color: white"> Update Floating Message</button>
      </div>

    </div>
  </div> <!--box end-->

  {!! Form::close() !!}
</section>
@endsection
@section('javascript')
  <script>
    $('#update_floating_message_form').validate({
      ignore: []
    });

    $('#new_tab').click(function (e) {
      e.preventDefault();
      var form_cnt = $('#form_cnt').val();

      $.ajax({
        url: "/floating_message/getTab/" + form_cnt,
        dataType: 'json',
        success: function(result) {
          $('.nav-tabs').append('<li>\n' +
                  '                <a href="#tab' + form_cnt +'" data-toggle="tab">New Tab</a>\n' +
                  '              </li>');
          $('.tab-content').append('<div class="tab-pane" id="tab' + form_cnt + '">' +
                  result.html +
                  '</div>');
            $('#form_cnt').val( parseInt(form_cnt) + 1);
        }
      });
    });

  </script>
@endsection