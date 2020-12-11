<div class="row-fluid" data-refreshinterval="{{ $viewSettings->display['refresh_interval'] }}">	 
        
	<div class="form-fields pr-0">
		<div class="row w-100">
			
			@foreach ($results as $order)
			 <div class="col col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3 kds-card">
				<div class="card" style="border-right: 5px solid {!! $order->status_color !!}">
					<div class="card-body" style="background:#fff;">
						
						@if($order->buttons != '')
						<div class="btn-group mb-3 w-100">
							{!! $order->buttons !!}
						</div>
						@endif 
						
						@if($order->assign != '')
						<form class="mb-3" method="get" action="{{ $order->assign_url }}">
							<input type="hidden" name="orderId" value="{{ $order->id }}" />
							<input type="hidden" name="action" value="assign" />
							<select name="actionId" class="form-control" onchange="this.parentNode.submit();">
								<option value="">--</option>
								@foreach($order->assign as $index=>$staff)
								<option value="{{ $index }}"@if($index == $order->assigned_to) selected @endif>{{ $staff }}</option>
								@endforeach
							</select>
						</form>
						@endif 
						
						@switch($viewSettings->display['card_line_1'])
							@case(1)<h4 class="card-title">{{ $order->name }} <span class="text-muted">(#{{ $order->id }})</span></h4>@break
							@case(2)<h4 class="card-title">#{{ $order->id }} <span class="text-muted">({{ $order->name }})</span></h4>@break
						@endswitch
						
						@switch($viewSettings->display['card_line_2'])
							@case(1)<h6 class="card-subtitle text-muted">{{ $order->phone }} / {{ $order->time }} / {{ $order->value }} / {{ $order->payment_code }}</h6>@break
							@case(2)<h6 class="card-subtitle text-muted">{{ $order->time }} / {{ $order->phone }} / {{ $order->value }} / {{ $order->payment_code }}</h6>@break
						@endswitch
						
						@switch($viewSettings->display['card_line_3'])
							@case(1)<h6 class="card-subtitle text-muted mt-1">{{ ($order->address) }}@if($order->type == 'delivery')<a class="p-2 fab fa-google" target="_blank" title="Google Maps" href="{{ 'https://www.google.com/maps/dir/?api=1&destination='.urlencode($order->address) }}"></a><a class="p-2 fab fa-bing" target="_blank" title="Bing Maps" href="{{ 'https://maps.bing.com/?api=1&destination='.urlencode($order->address) }}"><img src="https://www.bing.com/sa/simg/favicon-2x.ico" width="15" height="15" ></a>@endif</h6>@break
						@endswitch		
										
						@if($viewSettings->display['card_status'])
							<h6 class="label label-default mt-2" style="background-color:{!! $order->status_color !!}">{{ $order->status_name }}</h6>
						@endif
						
						@if($viewSettings->display['card_items'])
						<div>
							@foreach ($order->dishes as $dish)
								@if ($dish != '')
								{!! $dish !!}
								@endif
							@endforeach
							@if ($order->comment != '')
							<p class="w-100 text-wrap"><em>{!! $order->comment !!}</em></p>
							@endif
						</div>
						@endif 
										
					</div>
				</div>	
			 </div>
			 @endforeach
			 
		</div>
	</div>
		    	    
</div>
