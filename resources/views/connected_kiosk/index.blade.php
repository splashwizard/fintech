@extends('layouts.app')
@section('title', __('lang_v1.payment_accounts'))

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('lang_v1.connected_kiosk')
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @if(!empty($not_linked_payments))
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-danger">
                    <ul>
                        @if(!empty($not_linked_payments))
                            <li>{!! __('account.payments_not_linked_with_account', ['payments' => $not_linked_payments]) !!} <a href="{{action('AccountReportsController@paymentAccountReport')}}">@lang('account.view_details')</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    @endif
    @can('account.access')
    @if(auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Admin'))
    <div class="row">
        <div class="col-sm-12">
            <button type="button" class="btn btn-primary btn-modal pull-right" 
                data-container=".account_model"
                data-href="{{action('ServiceController@create')}}">
                <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
        </div>
    </div>
    <br>
    @endif
    <div class="row">
        <div class="col-sm-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#other_accounts" data-toggle="tab">
                            <i class="fa fa-book"></i> <strong>@lang('account.service_list')</strong>
                        </a>
                    </li>
                    {{--
                    <li>
                        <a href="#capital_accounts" data-toggle="tab">
                            <i class="fa fa-book"></i> <strong>
                            @lang('account.capital_accounts') </strong>
                        </a>
                    </li>
                    --}}
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="other_accounts">
                        <table class="table table-bordered table-striped" id="other_account_table">
                            <thead>
                                <tr>
                                    <th>@lang( 'lang_v1.name' )</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan
    
    <div class="modal fade account_model" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
<script src="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js?v=' . $asset_v) }}"></script>
<script>
    $(document).ready(function(){
        other_account_table = $('#other_account_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/account/connectedlist',
            columns: [
                {data: 'name', name: 'name'}
            ],
        });

    });

</script>
@endsection