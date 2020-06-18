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

            <div id="bank_account_detail">
            </div>

            <div class="form-group">
                {!! Form::label('withdraw_mode', __( 'account.withdraw_mode' ) .":*") !!}
                <div class="row" style="margin: 0">
                    <div class="col-sm-4" style="padding-left: 5px; padding-right: 5px">
                        <button class="form-control btn-withdraw_mode btn-info" style="padding: 10px" data-mode="b">Withdraw to customer</button>
                    </div>
                    <div class="col-sm-4" style="padding-left: 5px; padding-right: 5px">
                        <button class="form-control btn-withdraw_mode" data-mode="gt">Game Credit Transfer</button>
                    </div>
                    <div class="col-sm-4" style="padding-left: 5px; padding-right: 5px">
                        <button class="form-control btn-withdraw_mode" data-mode="gd">Game Credit Deduction</button>
                    </div>
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('amount', __( 'sale.amount' ) .":*") !!}
                {!! Form::text('amount', 0, ['class' => 'form-control input_number', 'required','placeholder' => __( 'sale.amount' ) ]); !!}
            </div>
            {!! Form::hidden('withdraw_mode', 'b', ['id' => 'withdraw_mode']) !!}
{{--            <div class="form-group">--}}
{{--                {!! Form::label('withdraw_mode', __( 'account.withdraw_mode' ) .":*") !!}--}}
{{--                {!! Form::select('withdraw_mode', $withdraw_mode, null, ['class' => 'form-control', 'required' ]); !!}--}}
{{--            </div>--}}
            <div class="form-group">

            </div>

            <div class="form-group">
                <div class="row" style="margin: 0">
                    <div class="col-sm-6" style="padding-left: 5px; padding-right: 5px">
                        {!! Form::label('withdraw_to', __( 'account.withdraw_to' ) .":*") !!}
                        {!! Form::select('withdraw_to', $to_users, null, ['class' => 'form-control', 'required', 'style' => 'width:100%']); !!}
                    </div>
                    <div class="col-sm-6" style="padding-left: 5px; padding-right: 5px">
                        {!! Form::label('withdraw_to', "Game ID" .":*") !!}
                        <button class="form-control" id="btn-game_id"></button>
                    </div>
                </div>
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

            <div id="receipt_image_div">
                <div class="form-group">
                    {!! Form::label('document', __('purchase.attach_receipt_image') . ':*') !!}
                    <textarea id="pasteArea" placeholder="Paste Image Here"></textarea>
                    {!! Form::file('document', ['id' => 'service_document', 'style' => 'display:none']); !!}
                    <p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])</p>
                </div>
                <img id="pastedImage" style="max-width: 570px"/>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary" id="btn-withdraw_submit">@lang( 'messages.submit' )</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script type="text/javascript">
    const fileInput = document.getElementById("service_document");
    const pasteArea = document.getElementById("pasteArea");
    pasteArea.addEventListener('paste', e => {
        fileInput.files = e.clipboardData.files;
        pasteArea.value = "image.png";

        var items = (e.clipboardData  || e.originalEvent.clipboardData).items;
        console.log(JSON.stringify(items)); // will give you the mime types
        // find pasted image among pasted items
        var blob = null;
        for (var i = 0; i < items.length; i++) {
            if (items[i].type.indexOf("image") === 0) {
                blob = items[i].getAsFile();
            }
        }
        // load image if there is a pasted image
        if (blob !== null) {
            var reader = new FileReader();
            reader.onload = function(event) {
                console.log(event.target.result); // data url!
                document.getElementById("pastedImage").src = event.target.result;
            };
            reader.readAsDataURL(blob);
        }
    });
    $(document).ready(function () {

        function copyTextToClipboard(text) {


            $('.view_modal').find('.modal-body').prepend('<textarea id="copy_clipboard">'+ text+'</textarea>');
            $('#copy_clipboard').focus();
            $('#copy_clipboard').select();

            try {
                var successful = document.execCommand('copy');
                var msg = successful ? 'successful' : 'unsuccessful';
                console.log('Copying text command was ' + msg);
            } catch (err) {
                console.log('Oops, unable to copy');
            }

            $('#copy_clipboard').remove();
        }


        function getBankDetail(){
            $.ajax({
                method: $(this).attr('method'),
                url: '/contacts/bank_detail',
                dataType: 'json',
                data: {user_id: $('#withdraw_to').val()},
                success: function(result) {
                    $('#bank_account_detail').html(result.bank_account_detail);
                    $('.account_detail').click(function (e) {
                        e.preventDefault();
                        copyTextToClipboard($(this).text());
                    });
                }
            });
        }
        function getGameId(){
            $.ajax({
                method: 'post',
                url: '/service/getGameId',
                dataType: 'json',
                data: {
                    customer_id: $('#withdraw_to').val(),
                    service_id: '{{$account->id}}'
                },
                success: function(result) {
                    $('#btn-game_id').html(result.game_id);
                }
            });
        }
        // fileinput_setting = {
        //     showUpload: false,
        //     showPreview: false,
        //     browseLabel: LANG.file_browse_label,
        //     removeLabel: LANG.remove,
        // };
        // $('#service_document').fileinput(fileinput_setting);

        $('#btn-game_id').click(function (e) {
            e.preventDefault();
            copyTextToClipboard($(this).text());
        });
        $('#service_div select').removeAttr('required');
        $('#od_datetimepicker').datetimepicker({
            format: moment_date_format + ' ' + moment_time_format
        });

        $('.btn-withdraw_mode').click(function (e) {
            e.preventDefault();
            const withdraw_mode = $(this).data('mode');
            $('#withdraw_mode').val(withdraw_mode);
            if (withdraw_mode === 'b') {
                $('#bank_div').show();
                $('#service_div').hide();
                $('#service_div select').removeAttr('required');
                $('#bank_account_detail').show();
                // $('#receipt_image_div').show();
            } else if (withdraw_mode === 'gt') {
                $('#bank_div').hide();
                $('#bank_div select').removeAttr('required');
                $('#service_div').show();
                $('#bank_account_detail').hide();
                // $('#receipt_image_div').hide();
            } else {
                $('#bank_div').hide();
                $('#service_div').hide();
                $('#bank_account_detail').hide();
                // $('#receipt_image_div').hide();
            }
            $(this).addClass('btn-info');
            $(this).parent().siblings().find('button').removeClass('btn-info');
        });

        $('#withdraw_mode').change(function () {
            var withdraw_mode = $(this).val();
            if (withdraw_mode === 'b') {
                $('#bank_div').show();
                $('#service_div').hide();
                $('#service_div select').removeAttr('required');
                $('#bank_account_detail').show();
                // $('#receipt_image_div').show();
            } else if (withdraw_mode === 'gt') {
                $('#bank_div').hide();
                $('#bank_div select').removeAttr('required');
                $('#service_div').show();
                $('#bank_account_detail').hide();
                // $('#receipt_image_div').hide();
            } else {
                $('#bank_div').hide();
                $('#service_div').hide();
                $('#bank_account_detail').hide();
                // $('#receipt_image_div').hide();
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

        getBankDetail();
        getGameId();
        $('#withdraw_to').on("change", function(e) {
            getBankDetail();
            getGameId();
        });
        $('#btn-withdraw_submit').click(function (e) {
            e.preventDefault();
            $.ajax({
                method: 'post',
                url: '/service/checkWithdraw',
                dataType: 'json',
                data: {
                    customer_id: $('#withdraw_to').val()
                },
                success: function(result) {
                    if(result.exceeded) {
                        swal({
                            title: LANG.sure,
                            text: LANG.withdraw_fourth_warning.replace("xxxx", $('#withdraw_to').children('option:selected').html()),
                            icon: 'warning',
                            buttons: true,
                            dangerMode: true,
                        }).then(willProceed => {
                            if(willProceed){
                                $('#withdraw_form').submit();
                            }
                        });
                    }
                    else
                        $('#withdraw_form').submit();
                    // $('#btn-game_id').html(result.game_id);
                }
            });
        })
    });
</script>