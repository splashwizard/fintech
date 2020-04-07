@extends('layouts.app')
@section('title', __('lang_v1.'.$type.'s'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1> @lang('lang_v1.'.$type.'s')
        <small>@lang( 'contact.manage_your_contact', ['contacts' =>  __('lang_v1.'.$type.'s') ])</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    <input type="hidden" value="{{$type}}" id="contact_type">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'contact.all_your_contact', ['contacts' => __('lang_v1.'.$type.'s') ])])
        @if( (auth()->user()->can('supplier.create') || auth()->user()->can('customer.create')) && $type != 'blacklisted_customer' )
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal" 
                    data-href="{{action('ContactController@create', ['type' => $type])}}" 
                    data-container=".contact_modal">
                    <i class="fa fa-plus"></i> @lang('messages.add')</button>
                </div>
            @endslot
        @endif
        @if(auth()->user()->can('supplier.view') || auth()->user()->can('customer.view'))
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="contact_table">
                    <thead>
                        <tr>
                            <th>@lang('lang_v1.contact_id')</th>
                            @if($type == 'supplier')
                                    <th>@lang('business.business_name')</th>
                                <th>@lang('contact.name')</th>
                                <th>@lang('lang_v1.added_on')</th>
                                <th>@lang('contact.contact')</th>
                                <th>@lang('contact.total_purchase_due')</th>
                                <th>@lang('lang_v1.total_purchase_return_due')</th>
                                <th>@lang('messages.action')</th>
                            @elseif( $type == 'customer')
                                <th>@lang('user.name')</th>
                                <th>@lang('contact.contact')</th>
                                <th>@lang('user.email')</th>
                                <th>@lang('lang_v1.membership')</th>
                                <th>@lang('lang_v1.customer_group')</th>
                                <th>@lang('contact.total_sale_due')</th>
                                <th>@lang('lang_v1.total_sell_return_due')</th>
                                <th>@lang('contact.birthday')</th>
                                @if($reward_enabled)
                                    {{--                                    <th>{{session('business.rp_name')}}</th>--}}
                                    <th>@lang('user.rp_name')</th>
                                @endif
                                <th>@lang('business.address')</th>
                                <th>@lang('lang_v1.added_on')</th>
                                <th>@lang('messages.action')</th>
                            @elseif( $type == 'blacklisted_customer')
                                <th>@lang('user.name')</th>
                                <th>@lang('contact.contact')</th>
                                <th>@lang('user.email')</th>
                                <th>@lang('lang_v1.customer_group')</th>
                                <th>@lang('contact.total_sale_due')</th>
                                <th>@lang('lang_v1.total_sell_return_due')</th>
                                @if($reward_enabled)
                                    {{--                                    <th>{{session('business.rp_name')}}</th>--}}
                                    <th>@lang('user.rp_name')</th>
                                @endif
                                <th>@lang('business.address')</th>
                                <th>@lang('lang_v1.added_on')</th>
                                <th>@lang('contact.blacklist_by')</th>
                                <th>@lang('contact.remarks')</th>
                                <th>@lang('contact.banned_by')</th>
                                <th>@lang('messages.action')</th>
                            @endif
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-17 text-center footer-total">
                            <td @if($type == 'supplier') colspan="2"
                                @elseif( $type == 'customer') colspan="6"
                                @elseif( $type == 'blacklisted_customer') colspan="5"
                                @endif>
                                <strong>@lang('sale.total'):</strong>
                            </td>
                            <td><span class="display_currency" id="footer_contact_due"></span></td>
                            <td><span class="display_currency" id="footer_contact_return_due"> </span></td>
                            @if( $type == 'blacklisted_customer')
                                <td @if($reward_enabled) colspan="6" @else colspan="5" @endif></td>
                            @else
                                <td @if($reward_enabled) colspan="5" @else colspan="4" @endif></td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    @endcomponent

    <div class="modal fade contact_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade blacklist_modal" tabindex="-1" role="dialog"
         aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade pay_contact_due_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
    <script>
        //Start: CRUD for Contacts
        //contacts table
        var contact_table_type = $('#contact_type').val();
        var reward_enabled = '{{$reward_enabled}}';
        var columns;
        console.log(reward_enabled);
        // var targets = 8;
        // if (contact_table_type == 'supplier') {
        //     targets = [8,9,10];
        // }
        if (contact_table_type === 'blacklisted_customer'){
            columns = [{data: 'contact_id', width: "10%"},
                {data: 'name', width: "10%"},
                {data: 'mobile', width: "10%"},
                {data: 'email', width: "10%"},
                {data: 'customer_group', width: "10%"},
                {data: 'due', width: "10%"},
                {data: 'return_due', width: "10%"}];
        } else {
            columns = [{data: 'contact_id', width: "10%"},
                {data: 'name', name: 'contacts.name', width: "10%"},
                {data: 'mobile', width: "10%"},
                {data: 'email', width: "10%"},
                {data: 'membership', name: 'm.name', width: "10%"},
                {data: 'customer_group', name: 'cg.name', width: "10%"},
                {data: 'due', width: "10%"},
                {data: 'return_due', width: "10%"},
                {data: 'birthday', width: "10%"}];
        }
        if(reward_enabled)
            columns.push({data: 'total_rp', width: "10%"});
        if (contact_table_type === 'blacklisted_customer'){
            columns.push.apply(columns, [
                {data: 'landmark', width: "10%"},
                {data: 'created_at', width: "10%"},
                {data: 'blacked_by_user', width: "10%"},
                {data: 'remark', width: "10%"},
                {data: 'banned_by_user', visible: false, width: "0%"},
                {data: 'action', width: "10%"}
            ]);
        } else {
            columns.push.apply(columns,[
                {data: 'landmark', width: "10%"},
                {data: 'created_at', width: "10%"},
                {data: 'action', width: "10%"}
            ]);
        }
        var contact_table = $('#contact_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/contacts?type=' + $('#contact_type').val(),
            // columnDefs: [
            //     {
            //         targets: targets,
            //         orderable: false,
            //         searchable: false,
            //     },
            // ],
            columns: columns,
            fnDrawCallback: function(oSettings) {
                var total_due = sum_table_col($('#contact_table'), 'contact_due');
                $('#footer_contact_due').text(total_due);

                var total_return_due = sum_table_col($('#contact_table'), 'return_due');
                $('#footer_contact_return_due').text(total_return_due);
                __currency_convert_recursively($('#contact_table'));
            },
            "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                console.log(aData);
                if ( aData.banned_by_user && aData.banned_by_user == 'king king' )
                {
                    $('td', nRow).css('color', 'Red');
                }
            }
        });


        //On display of add contact modal
        $('.contact_modal').on('shown.bs.modal', function(e) {
            if ($('select#contact_type').val() == 'customer') {
                $('div.supplier_fields').hide();
                $('div.customer_fields').show();
            } else if ($('select#contact_type').val() == 'supplier') {
                $('div.supplier_fields').show();
                $('div.customer_fields').hide();
            }

            $('select#contact_type').change(function() {
                var t = $(this).val();

                if (t == 'supplier') {
                    $('div.supplier_fields').fadeIn();
                    $('div.customer_fields').fadeOut();
                } else if (t == 'both') {
                    $('div.supplier_fields').fadeIn();
                    $('div.customer_fields').fadeIn();
                } else if (t == 'customer') {
                    $('div.customer_fields').fadeIn();
                    $('div.supplier_fields').fadeOut();
                }
            });

            $('form#contact_add_form, form#contact_edit_form')
                .submit(function(e) {
                    console.log('editing form');
                    e.preventDefault();
                })
                .validate({
                    rules: {
                        contact_id: {
                            remote: {
                                url: '/contacts/check-contact-id',
                                type: 'post',
                                data: {
                                    contact_id: function() {
                                        return $('#contact_id').val();
                                    },
                                    hidden_id: function() {
                                        if ($('#hidden_id').length) {
                                            return $('#hidden_id').val();
                                        } else {
                                            return '';
                                        }
                                    },
                                },
                            },
                        },
                    },
                    messages: {
                        contact_id: {
                            remote: LANG.contact_id_already_exists,
                        },
                    },
                    submitHandler: function(form) {
                        e.preventDefault();
                        var data = $(form).serialize();
                        // $(form)
                        //     .find('button[type="submit"]')
                        //     .attr('disabled', true);
                        $.ajax({
                            method: 'POST',
                            url: $(form).attr('action'),
                            dataType: 'json',
                            data: data,
                            success: function(result) {
                                if (result.success == true) {
                                    $('div.contact_modal').modal('hide');
                                    toastr.success(result.msg);
                                    contact_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    },
                });
        });

        //On display of add contact modal
        $('.blacklist_modal').on('shown.bs.modal', function(e) {
            $('form#contact_edit_blacklist_form')
                .submit(function(e) {
                    console.log('editing form');
                    e.preventDefault();
                })
                .validate({
                    submitHandler: function(form) {
                        e.preventDefault();
                        var data = $(form).serialize();
                        $(form)
                            .find('button[type="submit"]')
                            .attr('disabled', true);
                        $.ajax({
                            method: 'POST',
                            url: $(form).attr('action'),
                            dataType: 'json',
                            data: data,
                            success: function(result) {
                                if (result.success == true) {
                                    $('div.blacklist_modal').modal('hide');
                                    toastr.success(result.msg);
                                    contact_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    },
                });
        });

        $(document).on('click', '.btn-plus', function(e) {
            e.preventDefault();
            var account_index = parseInt($('#account_index').val());
            if(account_index === 3){
                toastr.error(LANG.contact_account_maximum_error);
                return;
            }
            $.ajax({
                method: 'GET',
                url: '/contacts/bank_detail_html',
                async: false,
                data: {
                    account_index: account_index
                },
                dataType: 'json',
                success: function(result) {
                    if (result.success) {
                        $('#bank_details_part').append(result.html);
                        $('#account_index').val(account_index + 1);
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });

        $(document).on('click', '.edit_contact_button', function(e) {
            e.preventDefault();
            $('div.contact_modal').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });

        $(document).on('click', '.edit_blacklist_button', function(e) {
            e.preventDefault();
            $('div.blacklist_modal').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });

        $(document).on('click', '.delete_contact_button', function(e) {
            e.preventDefault();
            swal({
                title: LANG.sure,
                text: LANG.confirm_delete_contact,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var href = $(this).attr('href');
                    var data = $(this).serialize();

                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                contact_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });

        $(document).on('click', '.ban_user_button', function(e) {
            e.preventDefault();
            swal({
                title: LANG.sure,
                text: LANG.confirm_ban_contact,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var href = $(this).attr('href');
                    var data = $(this).serialize();

                    $.ajax({
                        method: 'POST',
                        url: href,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                contact_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });
    </script>
@endsection