@inject('request', 'Illuminate\Http\Request')
@if( ($request->segment(1) == 'pos' || $request->segment(1) == 'pos_deposit') && ($request->segment(2) == 'create' || $request->segment(2) == 'create_selected_transaction' || $request->segment(3) == 'edit'))
    @php
            $pos_layout = true;
            if($request->segment(2) == 'create' || $request->segment(2) == 'create_selected_transaction')
                $pos_action = 'create';
            else
                $pos_action = 'edit';
    @endphp
@else
    @php
        $pos_layout = false;
    @endphp
@endif

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ? 'rtl' : 'ltr'}}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title') - {{ Session::get('business.name') }}</title>
        
        @include('layouts.partials.css')

        @yield('css')
        <script src='https://cdn.tiny.cloud/1/2pn06cqm4it0x5a1rnsjhwysgeex4pg0qye8aac992cpgxwh/tinymce/5/tinymce.min.js' referrerpolicy="origin">
        </script>
        <style>
            .wrapper {
                background-color: #ecf0f5!important;
            }
            .content-wrapper {
                margin: 0 40px;
            }
            .main-footer {
                margin-left: 0;
            }
        </style>
    </head>

    <body class="@if($pos_layout) hold-transition lockscreen @else hold-transition skin-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'blue'}}@endif sidebar-mini @endif">
        <div class="wrapper">
            <script type="text/javascript">
                if(localStorage.getItem("upos_sidebar_collapse") == 'true'){
                    var body = document.getElementsByTagName("body")[0];
                    body.className += " sidebar-collapse";
                }
            </script>
            @include('layouts.partials.header-procatcher')

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">

                <!-- Add currency related field-->
                <input type="hidden" id="__code" value="{{session('currency')['code']}}">
                <input type="hidden" id="__symbol" value="{{session('currency')['symbol']}}">
                <input type="hidden" id="__thousand" value="{{session('currency')['thousand_separator']}}">
                <input type="hidden" id="__decimal" value="{{session('currency')['decimal_separator']}}">
                <input type="hidden" id="__symbol_placement" value="{{session('business.currency_symbol_placement')}}">
                <input type="hidden" id="__precision" value="{{config('constants.currency_precision', 2)}}">
                <input type="hidden" id="__quantity_precision" value="{{config('constants.quantity_precision', 2)}}">
                <!-- End of currency related field-->

                @if (session('status'))
                    <input type="hidden" id="status_span" data-status="{{ session('status.success') }}" data-msg="{{ session('status.msg') }}">
                @endif
                @yield('content')
                @if(config('constants.iraqi_selling_price_adjustment'))
                    <input type="hidden" id="iraqi_selling_price_adjustment">
                @endif

                <!-- This will be printed -->
                <section class="invoice print_section" id="receipt_section">
                </section>
                
            </div>
            @include('home.todays_profit_modal')
            <!-- /.content-wrapper -->

            @include('layouts.partials.footer')

            <audio id="success-audio">
              <source src="{{ asset('/audio/success.ogg?v=' . $asset_v) }}" type="audio/ogg">
              <source src="{{ asset('/audio/success.mp3?v=' . $asset_v) }}" type="audio/mpeg">
            </audio>
            <audio id="error-audio">
              <source src="{{ asset('/audio/error.ogg?v=' . $asset_v) }}" type="audio/ogg">
              <source src="{{ asset('/audio/error.mp3?v=' . $asset_v) }}" type="audio/mpeg">
            </audio>
            <audio id="warning-audio">
              <source src="{{ asset('/audio/warning.ogg?v=' . $asset_v) }}" type="audio/ogg">
              <source src="{{ asset('/audio/warning.mp3?v=' . $asset_v) }}" type="audio/mpeg">
            </audio>

        </div>

        @include('layouts.partials.javascripts')
        <div class="modal fade view_modal" tabindex="-1" role="dialog"
        aria-labelledby="gridSystemModalLabel"></div>
    </body>

</html>