@extends('layouts.app')
@section('title', __('expense.expenses'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('game_list.game_list')</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('game_list.all_games')])
                @can('expenses')
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-block btn-primary" href="{{action('GameListController@create')}}">
                            <i class="fa fa-plus"></i> @lang('messages.add') Game</a>
                        </div>
                    @endslot
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center" id="promotion_table">
                            <thead>
                                <tr>
                                    <th>@lang('promotion.collection')</th>
                                    <th>@lang('promotion.no')</th>
                                    <th>@lang('promotion.title')</th>
                                    <th>@lang('promotion.sequence')</th>
                                    <th>@lang('promotion.show')</th>
                                    <th>@lang('promotion.sale')</th>
                                    <th>@lang('promotion.new')</th>
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
 <script src="{{ asset('js/game_list.js?v=' . $asset_v) }}"></script>
@endsection