<div class="hide" >
    <table id="hidden_allowance_table">@include('essentials::payroll.allowance_and_deduction_row', ['type' => 'allowance'])</table>
</div>
<div class="hide" >
    <table id="hidden_deduction_table">@include('essentials::payroll.allowance_and_deduction_row', ['type' => 'deduction'])</table>
</div>
<script type="text/javascript">
$(document).ready( function () {
    $('#add_allowance').click( function() {
        var html = $('table#hidden_allowance_table tbody').html();
        $('#allowance_table tbody').append(html);
    });

    $('#add_deduction').click( function() {
        var html = $('table#hidden_deduction_table tbody').html();
        $('#deductions_table tbody').append(html);
    });

    $(document).on('click', 'button.remove_tr', function(){
        $(this).closest('tr').remove();
        calculateTotal();
    });
    $(document).on('change', '#essentials_duration, #essentials_amount_per_unit_duration, input.allowance, input.deduction', function() {
        calculateTotal();
    });
    $(document).on('change', '#total', function() {
        var total_duration = __read_number($('#essentials_duration'));

        var total = __read_number($(this));

        var amount_per_unit_duration = total / total_duration;
        __write_number($('#essentials_amount_per_unit_duration'), amount_per_unit_duration, false, 2);
    });
});

function calculateTotal() {
    var total_duration = __read_number($('#essentials_duration'));
    var amount_per_unit_duration = __read_number($('#essentials_amount_per_unit_duration'));
    var total = total_duration * amount_per_unit_duration;
    __write_number($('#total'), total, false, 2);

    var total_allowance = 0;
    $('input.allowance').each( function(){
        total_allowance += __read_number($(this));
    });
    $('#total_allowances').text(__currency_trans_from_en(total_allowance, true));

    var total_deduction = 0;
    $('input.deduction').each( function(){
        total_deduction += __read_number($(this));
    });
    $('#total_deductions').text(__currency_trans_from_en(total_deduction, true));

    var gross_amount = total + total_allowance - total_deduction;
    $('#gross_amount').val(gross_amount);
    $('#gross_amount_text').text(__currency_trans_from_en(gross_amount, true));
}

</script>