<script src="{{ asset('plugins/mousetrap/mousetrap.min.js?v=' . $asset_v) }}"></script>
<script type="text/javascript">
	$(document).ready( function() {
		//shortcut for express checkout
		@if(!empty($shortcuts["pos"]["express_checkout"]) && ($pos_settings['disable_express_checkout'] == 0))
			Mousetrap.bind('{{$shortcuts["pos"]["express_checkout"]}}', function(e) {
				e.preventDefault();
				$('button.pos-express-finalize[data-pay_method="cash"]').trigger('click');
			});
		@endif

		//shortcut for cancel checkout
		@if(!empty($shortcuts["pos"]["cancel"]))
			Mousetrap.bind('{{$shortcuts["pos"]["cancel"]}}', function(e) {
				e.preventDefault();
				$('#pos-cancel').trigger('click');
			});
		@endif

        //shortcut for bank
        var bank_shortcuts = [1,2,3,4,'q','w','e','r','a','s','d','f'];
        var bank_keycodes = ['Digit1', 'Digit2', 'Digit3', 'Digit4', 'KeyQ', 'KeyW', 'KeyE', 'KeyR', 'KeyA', 'KeyS', 'KeyD', 'KeyF'];
        for(var i = 0; i < bank_shortcuts.length; i ++){
            Mousetrap.bind('shift+' + bank_shortcuts[i], function(e) {
                e.preventDefault();
                var index = bank_keycodes.indexOf(e.code);
                $('#bank_products_list .product_list:eq(' + index +') .bank_product_box').trigger('click');
            });
        }

        //insert bank item
        var insert_bank_shortcuts = ['1','2','3','4','5','6','7','8'];
        var insert_bank_keycodes = ['Digit1', 'Digit2', 'Digit3', 'Digit4', 'Digit5', 'Digit6', 'Digit7', 'Digit8'];
        for(i = 0; i < insert_bank_shortcuts.length; i ++){
            var input = $('input[name=email]');
            Mousetrap.bind(insert_bank_shortcuts[i], function(e) {
                if (!$(e.target).hasClass('pos_unit_price')) {
                    e.preventDefault();
                    var index = insert_bank_keycodes.indexOf(e.code);
                    $('#product_list_body .product_list:eq(' + index +') .product_box').trigger('click');
                }
            });
        }

        //insert game item
        var insert_game_shortcuts = ['u','i','o','p','j','k','l',';'];
        var insert_game_keycodes = ['KeyU', 'KeyI', 'KeyO', 'KeyP', 'KeyJ', 'KeyK', 'KeyL', 'Semicolon'];
        for(i = 0; i < insert_game_shortcuts.length; i ++){
            Mousetrap.bind('shift+' + insert_game_shortcuts[i], function(e) {
                console.log(e.code);
                e.preventDefault();
                var index = insert_game_keycodes.indexOf(e.code);
                $('#product_list_body2 .product_list:eq(' + index +') .product_box').trigger('click');
            });
        }

        //insert bank_in time
        Mousetrap.bind('f2', function(e) {
            e.preventDefault();
            $('#bank_in_time').trigger('click');
        });

        //insert customer
        Mousetrap.bind('f4', function(e) {
            e.preventDefault();
            $('#customer_id').select2("open");
        });

        //insert bonus
        Mousetrap.bind('f8', function(e) {
            e.preventDefault();
            $('#bonus').show().focus().click();
        });

        //refresh
        Mousetrap.bind('f9', function(e) {
            e.preventDefault();
            $('#refresh').trigger('click');
        });
		//shortcut for draft checkout
		@if(!empty($shortcuts["pos"]["draft"]) && ($pos_settings['disable_draft'] == 0))
			Mousetrap.bind('{{$shortcuts["pos"]["draft"]}}', function(e) {
				e.preventDefault();
				$('#pos-draft').trigger('click');
			});
		@endif

		//shortcut for edit tax
		@if(!empty($shortcuts["pos"]["edit_order_tax"]) && ($pos_settings['disable_order_tax'] == 0))
			Mousetrap.bind('{{$shortcuts["pos"]["edit_order_tax"]}}', function(e) {
				e.preventDefault();
				$('#pos-edit-tax').trigger('click');
			});
		@endif

		//shortcut for add finalize payment
		@if(!empty($shortcuts["pos"]["finalize_payment"]) && ($pos_settings['disable_pay_checkout'] == 0))
			var payment_modal = document.querySelector('#modal_payment');
			Mousetrap(payment_modal).bind('{{$shortcuts["pos"]["finalize_payment"]}}', function(e, combo) {
				if($('#modal_payment').is(':visible')){
					e.preventDefault();
					$('#pos-save').trigger('click');
				}
			});
		@endif

	});
</script>