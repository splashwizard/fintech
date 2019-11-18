@extends('layouts.auth')
@section('title', __('lang_v1.login'))

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="login-box" style="margin: 2% auto;">
                
                <div class="login-box-body">

                <div class="login-logo">
                    @if(file_exists(public_path('uploads/logo.png')))
                        <img src="/uploads/logo.png" class="img-rounded" alt="Logo" width="150">
                    @else
                       {{ config('app.name', 'ultimatePOS') }}
                    @endif
                </div>
                <!-- /.login-logo -->

                <form method="POST" action="{{ route('login') }}">
                    {{ csrf_field() }}
                    <div class="form-group has-feedback {{ $errors->has('username') ? ' has-error' : '' }}">
                        @php
                            $username = old('username');
                            $password = null;
                            if(config('app.env') == 'demo'){
                                $username = 'admin';
                                $password = '123456';

                                $demo_types = array(
                                    'all_in_one' => 'admin',
                                    'super_market' => 'admin',
                                    'pharmacy' => 'admin-pharmacy',
                                    'electronics' => 'admin-electronics',
                                    'services' => 'admin-services',
                                    'restaurant' => 'admin-restaurant',
                                    'superadmin' => 'superadmin',
                                    'woocommerce' => 'woocommerce_user',
                                    'essentials' => 'admin-essentials',
                                    'manufacturing' => 'manufacturer-demo',
                                );

                                if( !empty($_GET['demo_type']) && array_key_exists($_GET['demo_type'], $demo_types) ){
                                    $username = $demo_types[$_GET['demo_type']];
                                }
                            }
                        @endphp
                        <input id="username" type="text" class="form-control" name="username" value="{{ $username }}" required autofocus placeholder="@lang('lang_v1.username')">
                        <span class="fa fa-user form-control-feedback"></span>
                        @if ($errors->has('username'))
                            <span class="help-block">
                                <strong>{{ $errors->first('username') }}</strong>
                            </span>
                        @endif
                    </div>
                    <div class="form-group has-feedback {{ $errors->has('password') ? ' has-error' : '' }}">
                        <input id="password" type="password" class="form-control" name="password"
                        value="{{ $password }}" required placeholder="@lang('lang_v1.password')">
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                        @if ($errors->has('password'))
                            <span class="help-block">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif
                    </div>
                    <div class="form-group">
                        <div class="checkbox icheck">
                            <label>
                                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> @lang('lang_v1.remember_me')
                            </label>
                        </div>
                    </div>
                    <br>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block btn-flat">@lang('lang_v1.login')</button>
                    </div>
                </form>
                @if(config('app.env') != 'demo')
                    <a href="{{ route('password.request') }}">
                        @lang('lang_v1.forgot_your_password')
                    </a>
                @endif
                </div>
                <!-- /.login-box-body -->
            </div>
            <!-- /.login-box -->
        </div>
    </div>
    @if(config('app.env') == 'demo')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading"><h4>Demo Shops <small><i> Demos are for example purpose only, this application <u>can be used in many other similar businesses.</u></i></small></h4></div>
                <div class="panel-body">
                    <div class="col-md-12 text-center">
                                <a href="?demo_type=all_in_one" class="btn btn-app bg-olive" data-toggle="tooltip" title="Showcases all feature available in the application." >
                                    <i class="fa fa-star"></i>
                                All In One</a>
                                <a href="?demo_type=pharmacy" class="btn bg-maroon btn-app" data-toggle="tooltip" title="Shops with products having expiry dates." >
                                <i class="fa fa-medkit"></i>
                                Pharmacy</a>
                                <a href="?demo_type=services" class="btn bg-orange btn-app" data-toggle="tooltip" title="For all service providers like Web Development, Restaurants, Repairing, Plumber, Salons, Beauty Parlors etc.">
                                <i class="fa fa-wrench"></i>
                                Multi-Service Center</a>
                                <a href="?demo_type=electronics" class="btn bg-purple btn-app" data-toggle="tooltip" title="Products having IMEI or Serial number code." >
                                <i class="fa fa-laptop"></i>
                                Electronics & Mobile Shop</a>
                                <a href="?demo_type=super_market" class="btn bg-navy btn-app" data-toggle="tooltip" title="Super market & Similar kind of shops." >
                                <i class="fa fa-shopping-cart"></i>
                                Super Market</a>
                                <a href="?demo_type=restaurant" class="btn bg-red btn-app" data-toggle="tooltip" title="Restaurants, Salons and other similar kind of shops." >
                                <i class="fa fa-cutlery"></i>
                                Restaurant</a>
                    </div>

                    <div class="col-md-12">
                        <hr>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-success alert-dismissible">
                            <i class="icon fa fa-plug"></i> Premium optional modules:
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-12">
                        <a href="?demo_type=superadmin" class="btn bg-red-active btn-app" data-toggle="tooltip" title="SaaS & Superadmin extension Demo">
                            <i class="fa fa-university"></i>
                            SaaS / Superadmin</a>

                        <a href="?demo_type=woocommerce" class="btn bg-woocommerce btn-app" data-toggle="tooltip" title="WooCommerce demo user - Open web shop in minutes!!" style="color:white !important">
                            <i class="fa fa-wordpress"></i>
                            WooCommerce</a>

                        <a href="?demo_type=essentials" class="btn bg-navy btn-app" data-toggle="tooltip" title="Essentials & HRM (human resource management) Module Demo" style="color:white !important">
                            <i class="fa fa-check-circle-o"></i>
                            Essentials & HRM</a>

                        <a href="?demo_type=manufacturing" class="btn bg-orange btn-app" data-toggle="tooltip" title="Manufacturing module demo" style="color:white !important">
                            <i class="fa fa-industry"></i>
                            Manufacturing Module</a>

                    </div>
                </div>
            </div>
         </div>
    </div>           
    @endif
</div>
@stop
@section('javascript')
<script type="text/javascript">
    $(document).ready(function(){
        $('#change_lang').change( function(){
            window.location = "{{ route('login') }}?lang=" + $(this).val();
        });
    })
</script>
@endsection
