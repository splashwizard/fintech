@extends('layouts.auth')

@section('title', __('lang_v1.reset_password'))

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
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                    <form  method="POST" action="{{ route('password.email') }}">
                        {{ csrf_field() }}
                         <div class="form-group has-feedback {{ $errors->has('email') ? ' has-error' : '' }}">
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus placeholder="@lang('lang_v1.email_address')">
                            <span class="fa fa-envelope form-control-feedback"></span>
                            @if ($errors->has('email'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('email') }}</strong>
                                </span>
                            @endif
                        </div>
                        <br>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block btn-flat">
                                @lang('lang_v1.send_password_reset_link')
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
