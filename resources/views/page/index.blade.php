@extends('layouts.app')
@section('title', 'Page Content')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Page Content</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('expense.all_expenses')])
                @can('expenses')
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center" id="page_table">
                            <thead>
                                <tr>
                                    <th>@lang('promotion.no')</th>
                                    <th>@lang('promotion.title')</th>
                                    <th>@lang('promotion.last_modified_on')</th>
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
 <script src="{{ asset('js/page.js?v=' . $asset_v) }}"></script>
@endsection