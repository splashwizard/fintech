<div class="modal-dialog" role="document">
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title">Please insert a second client for this item:</h4>
		</div>
		<div class="modal-body">
			<div class="row">
				<div class="form-group col-xs-12">
					{!! Form::select("products[{$row_count}][payment_for1]", [], null, ['class' => 'form-control mousetrap second_contact_select', 'placeholder' => 'Enter Customer name / phone', 'required', 'style' => 'width: 100%;']); !!}
					{!! Form::hidden("products[{$row_count}][payment_for]", 0, ['class' => 'payment_for']); !!}
					{!! Form::hidden("products[{$row_count}][game_id]", 'UNDEFINED', ['class' => 'second_game_id']); !!}
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" id="confirm_second_client_btn">@lang('messages.confirm')</button>
		</div>
	</div>
</div>