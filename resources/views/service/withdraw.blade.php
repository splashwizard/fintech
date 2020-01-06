<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open(['url' => action('ServiceController@postWithdraw'), 'method' => 'post', 'id' => 'withdraw_form' ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang( 'account.withdraw' )</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                <strong>@lang('account.selected_account')</strong>:
                {{$account->name}}
                {!! Form::hidden('account_id', $account->id) !!}
            </div>

            <div class="form-group">
                {!! Form::label('amount', __( 'sale.amount' ) .":*") !!}
                {!! Form::text('amount', 0, ['class' => 'form-control input_number', 'required','placeholder' => __( 'sale.amount' ) ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('withdraw_to', __( 'account.withdraw_to' ) .":*") !!}
                {!! Form::select('withdraw_to', $to_users, null, ['class' => 'form-control', 'required', 'style' => 'width:100%' ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('withdraw_mode', __( 'account.withdraw_mode' ) .":*") !!}
                {!! Form::select('withdraw_mode', $withdraw_mode, null, ['class' => 'form-control', 'required' ]); !!}
            </div>

            <div class="form-group" id="bank_div">
                {!! Form::label('bank_account_id', __( 'account.via_account' ) .":*") !!}
                {!! Form::select('bank_account_id', $bank_accounts, null, ['class' => 'form-control', 'required' ]); !!}
            </div>
            <div class="form-group" id="service_div" style="display: none">
                {!! Form::label('service_id', __( 'account.via_account' ) .":*") !!}
                {!! Form::select('service_id', $service_accounts, null, ['class' => 'form-control', 'required' ]); !!}
            </div>

            {{--            <div class="form-group">--}}
            {{--                {!! Form::label('operation_date', __( 'messages.date' ) .":*") !!}--}}
            {{--                <div class="input-group date" id='od_datetimepicker'>--}}
            {{--                  {!! Form::text('operation_date', 0, ['class' => 'form-control', 'required','placeholder' => __( 'messages.date' ) ]); !!}--}}
            {{--                  <span class="input-group-addon">--}}
            {{--                    <span class="glyphicon glyphicon-calendar"></span>--}}
            {{--                  </span>--}}
            {{--                </div>--}}
            {{--            </div>--}}

            <div class="form-group">
                {!! Form::label('note', __( 'brand.note' )) !!}
                {!! Form::textarea('note', null, ['class' => 'form-control', 'placeholder' => __( 'brand.note' ), 'rows' => 4]); !!}
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang( 'messages.submit' )</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script type="text/javascript">
    $(document).ready(function () {
        $('#service_div select').removeAttr('required');
        $('#od_datetimepicker').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format
        });
        $('#withdraw_mode').change(function () {
            var withdraw_mode = $(this).val();
            if (withdraw_mode === 'b') {
                $('#bank_div').show();
                $('#service_div').hide();
                $('#service_div select').removeAttr('required');
            } else {
                $('#bank_div').hide();
                $('#bank_div select').removeAttr('required');
                $('#service_div').show();
            }
        });
        $('#withdraw_to').select2({
            dropdownParent: $(".view_modal"),
            ajax: {
                url: '/contacts/customers',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page,
                    };
                },
                processResults: function (data) {
                    return {
                        results: data,
                    };
                },
            },
            templateResult: function (data) {
                var template = data.text + "<br>" + LANG.mobile + ": " + data.mobile;
                if (typeof (data.total_rp) != "undefined") {
                    var rp = data.total_rp ? data.total_rp : 0;
                    template += "<br><i class='fa fa-gift text-success'></i> " + rp;
                }

                return template;
            },
            minimumInputLength: 1,
            language: {
                noResults: function () {
                    var name = $('#withdraw_to')
                        .data('select2')
                        .dropdown.$search.val();
                    return (
                        '<button type="button" data-name="' +
                        name +
                        '" class="btn btn-link add_new_customer"><i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i>&nbsp; ' +
                        __translate('add_name_as_new_customer', {name: name}) +
                        '</button>'
                    );
                },
            },
            escapeMarkup: function (markup) {
                return markup;
            },
        });
    });
</script>