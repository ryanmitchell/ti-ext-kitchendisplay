<?php 
	
	[$locationParam] = $this->getParams();
	
?>
	<div class="row-fluid">
		
    <?php
        if (sizeof($this->getLocations()) > 1){
	?>
					
	<div class="list-filter" id="filter-list-filter">
		
	    <form id="filter-form" class="form-inline" accept-charset="utf-8" method="GET" action="<?= admin_url('thoughtco/runningorder/summary'); ?>" role="form">
		    	
	        <div class="d-sm-flex flex-sm-wrap w-100 no-gutters">
		        
				<div class="col col-11">
					
					<div class="filter-scope date form-group">
						
						<select name="location" class="form-control select2-hidden-accessible">
							<?php 
								foreach ($this->getLocations() as $key=>$location) echo '<option value="'.$key.'"'.($key == $locationParam ? ' selected' : '').'>'.$location.'</option>'; 
							?>
            			</select>
            			
            		</div>
            		
		        </div>	       
		        		        
				<div class="col col-1">
					
					<button type="submit" class="btn btn-primary float-right"><?= lang('lang:thoughtco.runningorder::default.btn_view') ?></button>
					
				</div>
        
	    	</div>
	    	
		</form>
		
	</div>
	
    <?php 
        }
    ?>	 
        
	<div class="form-fields">
		<div class="row">
	<?php foreach ($this->renderResults() as $order){ ?>
			 <div class="col-4">
				<div class="card" style="border-right: 5px solid <?= $order->status_color; ?>">
					<div class="card-body" style="background:#fff;">
						
						<h4 class="card-title"><?= $order->name ?> <span class="text-muted">(#<?= $order->id; ?>)</span></h5>
						<h6 class="card-subtitle text-muted"><?= $order->phone ?> / <?= $order->time ?> / <?= $order->value; ?></h6>
						<h6 class="label label-default mt-2" style="background-color:<?= $order->status_color; ?>"><?= $order->status_name; ?></h6>
						
						<p>
							<?php 
								foreach ($order->dishes as $dish){ 
									if ($dish != ''){
							?>
							<br /><?= $dish; ?>
							<?php 
									}
								} 
							?>
							<?php if ($order->comment != ''){ ?>
							<br /><br />
							<em><?= $order->comment; ?></em>
							<?php } ?>
						</p>
						
						<div class="btn-group mt-5">
							<?= $order->buttons ?>
						</div>
										
					</div>
				</div>	
			 </div>
	<?php } ?>
		</div>
	</div>
		    	    
</div>