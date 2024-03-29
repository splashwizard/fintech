<div class="row">
	<div class="col-md-12">
		<h4>@lang('lang_v1.more_info')</h4>
	</div>
	<div class="col-md-4">
		<p><strong>@lang( 'lang_v1.dob' ):</strong> @if(!empty($user->dob)) {{@format_date($user->dob)}} @endif</p>
		<p><strong>@lang( 'lang_v1.marital_status' ):</strong> @if(!empty($user->marital_status)) @lang('lang_v1.' .$user->marital_status) @endif</p>
{{--		<p><strong>@lang( 'lang_v1.blood_group' ):</strong> {{$user->blood_group ?? ''}}</p>--}}
		<p><strong>@lang( 'lang_v1.contact_no' ):</strong> {{$user->contact_number ?? ''}}</p>
		<p><strong>@lang( 'lang_v1.ip_addr_restrict' ):</strong> {{$user->ipaddr_restrict ?? ''}}</p>
	</div>
{{--	<div class="col-md-4">--}}
{{--		<p><strong>@lang( 'lang_v1.fb_link' ):</strong> {{$user->fb_link ?? ''}}</p>--}}
{{--		<p><strong>@lang( 'lang_v1.twitter_link' ):</strong> {{$user->twitter_link ?? ''}}</p>--}}
{{--		<p><strong>@lang( 'lang_v1.social_media', ['number' => 1] ):</strong> {{$user->social_media_1 ?? ''}}</p>--}}
{{--		<p><strong>@lang( 'lang_v1.social_media', ['number' => 2] ):</strong> {{$user->social_media_2 ?? ''}}</p>--}}
{{--	</div>--}}
	<div class="col-md-4">
		<p><strong>@lang( 'lang_v1.custom_field', ['number' => 1] ):</strong> {{$user->custom_field_1 ?? ''}}</p>
		<p><strong>@lang( 'lang_v1.custom_field', ['number' => 2] ):</strong> {{$user->custom_field_2 ?? ''}}</p>
		<p><strong>@lang( 'lang_v1.custom_field', ['number' => 3] ):</strong> {{$user->custom_field_3 ?? ''}}</p>
		<p><strong>@lang( 'lang_v1.custom_field', ['number' => 4] ):</strong> {{$user->custom_field_4 ?? ''}}</p>
	</div>
	<div class="clearfix"></div>
{{--	<div class="col-md-4">--}}
{{--		<p><strong>@lang('lang_v1.id_proof_name'):</strong>--}}
{{--		{{$user->id_proof_name ?? ''}}</p>--}}
{{--	</div>--}}
{{--	<div class="col-md-4">--}}
{{--		<p><strong>@lang('lang_v1.id_proof_number'):</strong>--}}
{{--		{{$user->id_proof_number ?? ''}}</p>--}}
{{--	</div>--}}
{{--	<div class="clearfix"></div>--}}
	<hr>
	<div class="col-md-6">
		<strong>@lang('lang_v1.permanent_address'):</strong><br>
		<p>{{$user->permanent_address ?? ''}}</p>
	</div>
{{--	<div class="col-md-6">--}}
{{--		<strong>@lang('lang_v1.current_address'):</strong><br>--}}
{{--		<p>{{$user->current_address ?? ''}}</p>--}}
{{--	</div>--}}
	<div class="clearfix"></div>
	<hr>
	<div class="col-md-12">
		<h4>@lang('lang_v1.bank_details'):</h4>
	</div>
	@php
		$bank_details = !empty($user->bank_details) ? json_decode($user->bank_details, true) : [];
	@endphp
	<div class="col-md-4">
		<p><strong>@lang('lang_v1.account_holder_name'):</strong> {{$bank_details['account_holder_name'] ?? ''}}</p>
		<p><strong>@lang('lang_v1.account_number'):</strong> {{$bank_details['account_number'] ?? ''}}</p>
	</div>
	<div class="col-md-4">
		<p><strong>@lang('lang_v1.bank_name'):</strong> {{$bank_details['bank_name'] ?? ''}}</p>
{{--		<p><strong>@lang('lang_v1.bank_code'):</strong> {{$bank_details['bank_code'] ?? ''}}</p>--}}
	</div>
	<div class="col-md-4">
{{--		<p><strong>@lang('lang_v1.branch'):</strong> {{$bank_details['branch'] ?? ''}}</p>--}}
		<p><strong>@lang('lang_v1.tax_payer_id'):</strong> {{$bank_details['tax_payer_id'] ?? ''}}</p>
	</div>
	<div class="col-md-12">
		<h4>@lang('lang_v1.emergency_contact'):</h4>
	</div>
	@php
		$emergency_contact = !empty($user->emergency_contact) ? json_decode($user->emergency_contact, true) : [];
	@endphp
	<div class="col-md-4">
		<p><strong>@lang('lang_v1.name'):</strong> {{$emergency_contact['name'] ?? ''}}</p>
	</div>
	<div class="col-md-4">
		<p><strong>@lang('lang_v1.contact_number'):</strong> {{$emergency_contact['contact_number'] ?? ''}}</p>
		{{--		<p><strong>@lang('lang_v1.bank_code'):</strong> {{$bank_details['bank_code'] ?? ''}}</p>--}}
	</div>
	<div class="col-md-4">
		{{--		<p><strong>@lang('lang_v1.branch'):</strong> {{$bank_details['branch'] ?? ''}}</p>--}}
		<p><strong>@lang('lang_v1.relationship'):</strong> {{$emergency_contact['relationship'] ?? ''}}</p>
	</div>
</div>