@php
    if($type == 'allowance') {
        $name_col = 'allowance_names';
        $val_col = 'allowance_amounts';
        $val_class = 'allowance';
    } elseif($type == 'deduction') {
        $name_col = 'deduction_names';
        $val_col = 'deduction_amounts';
        $val_class = 'deduction';
    }
@endphp
<tr>
    <td>
        {!! Form::text($name_col . '[]', !empty($name) ? $name : null, ['class' => 'form-control input-sm' ]); !!}
    </td>
    <td>
        {!! Form::text($val_col . '[]', !empty($value) ? @num_format((float) $value) : 0, ['class' => 'form-control input-sm input_number ' . $val_class ]); !!}
    </td>
    <td>
        @if(!empty($add_button))
            <button type="button" class="btn btn-primary btn-xs" @if($type == 'allowance') id="add_allowance" @elseif($type == 'deduction') id="add_deduction" @endif>
            <i class="fa fa-plus"></i>
        @else
            <button type="button" class="btn btn-danger btn-xs remove_tr"><i class="fa fa-minus"></i></button>
        @endif
    </button></td>
</tr>