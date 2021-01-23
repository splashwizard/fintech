@extends('layouts.app')
@section('title', __('expense.expenses'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('notice.notices')</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('notice.all_notices')])
                @can('expenses')
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-block btn-primary" href="{{action('NoticeController@create')}}">
                            <i class="fa fa-plus"></i> @lang('messages.add') notice</a>
                        </div>
                    @endslot
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center" id="notice_table">
                            <thead>
                                <tr>
                                    <th>@lang('notice.no')</th>
                                    <th>@lang('notice.title')</th>
                                    <th>@lang('notice.sequence')</th>
                                    <th>@lang('notice.show')</th>
                                    <th>@lang('notice.start_time')</th>
                                    <th>@lang('notice.end_time')</th>
                                    <th>@lang('notice.last_modified_on')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcan
            @endcomponent
        </div>
    </div>

</section>
<!-- /.content -->
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
@stop
@section('javascript')
 <script src="{{ asset('js/notice.js?v=' . $asset_v) }}"></script>
@endsection