<div class="row-fluid">	 
        
	<div class="form-fields pr-0">
		<div class="row w-100">
			
			@foreach ($this->renderResults() as $order)
			 <div class="col col-sm-12 col-md-6 col-lg-4 col-xl-3 mb-3 kds-card">
				<div class="card" style="border-right: 5px solid {!! $order->status_color !!}">
					<div class="card-body" style="background:#fff;">
						
						<div class="btn-group mb-3 w-100">
							{!! $order->buttons !!}
						</div>
												
						<h4 class="card-title">{{ $order->name }} <span class="text-muted">(#{{ $order->id }})</span></h5>
						<h6 class="card-subtitle text-muted">{{ $order->phone }} / {{ $order->time }} / {{ $order->value }}</h6>
						<h6 class="label label-default mt-2" style="background-color:{!! $order->status_color !!}">{{ $order->status_name }}</h6>
						
						<div>
							@foreach ($order->dishes as $dish)
								@if ($dish != '')
								{!! $dish !!}
								@endif
							@endforeach
							@if ($order->comment != '')
							<p class="w-100 text-wrap"><em>{!! $order->comment !}}</em></p>
							@endif
						</div>
										
					</div>
				</div>	
			 </div>
			 @endforeach
			 
		</div>
	</div>
		    	    
</div>
