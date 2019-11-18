@if(Module::has('Essentials'))
  @include('essentials::attendance.clock_in_clock_out_modal')
@endif
<script type="text/javascript">
	$(document).ready( function(){
        $('#essentials_dob').datepicker();
		$('.clock_in_btn, .clock_out_btn').click( function() {
            var type = $(this).data('type');
            if (type == 'clock_in') {
                $('#clock_in_clock_out_modal').find('#clock_in_text').removeClass('hide');
                $('#clock_in_clock_out_modal').find('#clock_out_text').addClass('hide');
            } else if (type == 'clock_out') {
                $('#clock_in_clock_out_modal').find('#clock_in_text').addClass('hide');
                $('#clock_in_clock_out_modal').find('#clock_out_text').removeClass('hide');
            }
            $('#clock_in_clock_out_modal').find('input#type').val(type);

            $('#clock_in_clock_out_modal').modal('show');
        });
	});

	$(document).on('submit', 'form#clock_in_clock_out_form', function(e) {
        e.preventDefault();
        $(this).find('button[type="submit"]').attr('disabled', true);
        var data = $(this).serialize();

        $.ajax({
            method: $(this).attr('method'),
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            success: function(result) {
                if (result.success == true) {
                    $('div#clock_in_clock_out_modal').modal('hide');
                    toastr.success(result.msg);
                    if (typeof attendance_table !== 'undefined') {
                        attendance_table.ajax.reload();
                    }
                    if (result.type == 'clock_in') {
                        $('.clock_in_btn').addClass('hide');
                        $('.clock_out_btn').removeClass('hide');
                    } else if(result.type == 'clock_out') {
                        $('.clock_out_btn').addClass('hide');
                        $('.clock_in_btn').removeClass('hide');
                    }
                    $('#clock_in_clock_out_form')[0].reset();
                     $('#clock_in_clock_out_form').find('button[type="submit"]').removeAttr('disabled');
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });
</script>