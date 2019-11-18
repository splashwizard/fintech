@extends('layouts.app')
@section('title', __('business.business_settings'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('business.business_settings')</h1>
    <br>
    <div class="row">
        <div class="col-md-8 col-xs-12 col-md-offset-2">
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-search"></i></span>
                <select id="search_settings" class="form-control" style="width: 100%;">
                </select>
            </div>
            
        </div>
    </div>
</section>

<!-- Main content -->
<section class="content">
{!! Form::open(['url' => action('BusinessController@postBusinessSettings'), 'method' => 'post', 'id' => 'bussiness_edit_form',
           'files' => true ]) !!}
    <div class="row">
        <div class="col-xs-12">
       <!--  <pos-tab-container> -->
        <div class="col-xs-12 pos-tab-container">
            <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 pos-tab-menu">
                <div class="list-group">
                    <a href="#" class="list-group-item text-center active">@lang('business.business')</a>
                    <a href="#" class="list-group-item text-center">@lang('business.tax') @show_tooltip(__('tooltip.business_tax'))</a>
                    <a href="#" class="list-group-item text-center">@lang('business.product')</a>
                    <a href="#" class="list-group-item text-center">@lang('business.sale')</a>
                    <a href="#" class="list-group-item text-center">@lang('sale.pos_sale')</a>
                    <a href="#" class="list-group-item text-center">@lang('purchase.purchases')</a>
                    @if(!config('constants.disable_expiry', true))
                    <a href="#" class="list-group-item text-center">@lang('business.dashboard')</a>
                    @endif
                    <a href="#" class="list-group-item text-center">@lang('business.system')</a>
                    <a href="#" class="list-group-item text-center">@lang('lang_v1.prefixes')</a>
                    <a href="#" class="list-group-item text-center">@lang('lang_v1.email_settings')</a>
                    <a href="#" class="list-group-item text-center">@lang('lang_v1.sms_settings')</a>
                    <a href="#" class="list-group-item text-center">@lang('lang_v1.reward_point_settings')</a>
                    <a href="#" class="list-group-item text-center">@lang('lang_v1.modules')</a>
                    <a href="#" class="list-group-item text-center">@lang('lang_v1.custom_labels')</a>
                </div>
            </div>
            <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                <!-- tab 1 start -->
                @include('business.partials.settings_business')
                <!-- tab 1 end -->
                <!-- tab 2 start -->
                @include('business.partials.settings_tax')
                <!-- tab 2 end -->
                <!-- tab 3 start -->
                @include('business.partials.settings_product')
                <!-- tab 3 end -->
                <!-- tab 4 start -->
                @include('business.partials.settings_sales')
                @include('business.partials.settings_pos')
                <!-- tab 4 end -->
                <!-- tab 5 start -->
                @include('business.partials.settings_purchase')
                <!-- tab 5 end -->
                <!-- tab 6 start -->
                @if(!config('constants.disable_expiry', true))
                    @include('business.partials.settings_dashboard')
                @endif
                <!-- tab 6 end -->
                <!-- tab 7 start -->
                @include('business.partials.settings_system')
                <!-- tab 7 end -->
                <!-- tab 8 start -->
                @include('business.partials.settings_prefixes')
                <!-- tab 8 end -->
                <!-- tab 9 start -->
                @include('business.partials.settings_email')
                <!-- tab 9 end -->
                <!-- tab 10 start -->
                @include('business.partials.settings_sms')
                <!-- tab 10 end -->
                <!-- tab 11 start -->
                @include('business.partials.settings_reward_point')
                <!-- tab 11 end -->
                <!-- tab 12 start -->
                @include('business.partials.settings_modules')
                <!-- tab 12 end -->
                @include('business.partials.settings_custom_labels')
            </div>
        </div>
        <!--  </pos-tab-container> -->
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <button class="btn btn-danger pull-right" type="submit">@lang('business.update_settings')</button>
        </div>
    </div>
{!! Form::close() !!}
</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    $(document).ready( function(){
        //Search Settings
        //Set all labels as select2 options
        label_objects = [];
        select2_data = [{
            id: '',
            text: ''
        }];
        var i = 0;
        $('.pos-tab-container label').each( function(){
            label_objects.push($(this));
            var label_text = $(this).text().trim().replace(":", "").replace("*", "");
            select2_data.push(
                {
                    id: i,
                    text: label_text
                }
            );
            i++;
        });
        $('#search_settings').select2({
            data: select2_data,
            placeholder: '@lang("lang_v1.search_settings")',
        });
        $('#search_settings').change( function(){
            //Get label position and add active class to the tab
            var label_index = $(this).val();
            var label = label_objects[label_index];
            $('.pos-tab-content.active').removeClass('active');
            var tab_content = label.closest('.pos-tab-content');
            tab_content.addClass('active');
            tab_index = $('.pos-tab-content').index(tab_content);
            $('.list-group-item.active').removeClass('active');
            $('.list-group-item').eq(tab_index).addClass('active');

            //Highlight the label for three seconds
            label.css('background-color', 'yellow');
            setTimeout(function(){ 
                label.css('background-color', ''); 
            }, 3000);
        });
    });

    $(document).on('ifToggled', '#use_superadmin_settings', function() {
        if ($('#use_superadmin_settings').is(':checked')) {
            $('#toggle_visibility').addClass('hide');
        } else {
            $('#toggle_visibility').removeClass('hide');
        }
    });
</script>
@endsection