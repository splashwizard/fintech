@foreach($transaction_data as $row)
    <tr>
        <td>{{@format_datetime($row->created_at)}}</td>
        <td>{{$row->invoice_no}}</td>
        <td>{{$row->contact_id}}</td>
        <td>{{$row->from_name}}</td>
        <td>{{$row->to_name}}</td>
        <td>{{$row->amount}}</td>
    </tr>
@endforeach