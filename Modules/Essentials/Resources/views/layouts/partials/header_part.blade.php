@if($__is_essentials_enabled && $is_employee_allowed)
	<button 
		type="button" 
		class="btn bg-blue btn-flat 
		pull-left m-8 btn-sm mt-10 
		clock_in_btn
		@if(!empty($clock_in))
	    	hide
	    @endif
		"
	    data-type="clock_in"
	    data-toggle="tooltip"
	    data-placement="bottom"
	    title="@lang('essentials::lang.clock_in')" 
	    >
	    <i class="fa fa-arrow-circle-down"></i>
	</button>

	<button 
		type="button" 
		class="btn bg-yellow btn-flat pull-left m-8 
		 btn-sm mt-10 clock_out_btn
		@if(empty($clock_in))
	    	hide
	    @endif
		" 	
	    data-type="clock_out"
	    data-toggle="tooltip"
	    data-placement="bottom"
	    data-html="true"
	    title="@lang('essentials::lang.clock_out') @if(!empty($clock_in))
                    <br><small>(@lang('essentials::lang.clocked_in_at'): {{@format_datetime($clock_in->clock_in_time)}})</small>
                @endif" 
	    >
	    <i class="fa fa-hourglass-2 fa-spin"></i>
	</button>
@endif