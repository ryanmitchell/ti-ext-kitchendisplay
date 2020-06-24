<?php

namespace Thoughtco\Ordersummary\Controllers;

use AdminMenu;
use Admin\Facades\AdminLocation;
use Admin\Models\Locations_model;
use Admin\Models\Orders_model;
use Admin\Models\Working_hours_model;
use ApplicationException;
use Carbon\Carbon;
use DB;

/**
 * Order Summary
 */
class Summary extends \Admin\Classes\AdminController
{

    protected $requiredPermissions = 'Thoughtco.Ordersummary.*';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('sales', 'summary');
        
    }
    
    public function index()
    {
        $this->addJs('/app/system/assets/ui/js/vendor/moment.min.js', 'moment-js');
        $this->addCss('/app/admin/formwidgets/datepicker/assets/vendor/datepicker/bootstrap-datepicker.min.css', 'bootstrap-datepicker-css');
        $this->addJs('/app/admin/formwidgets/datepicker/assets/vendor/datepicker/bootstrap-datepicker.min.js', 'bootstrap-datepicker-js');
        $this->addCss('/app/admin/formwidgets/datepicker/assets/css/datepicker.css', 'datepicker-css');
        $this->addJs('/app/admin/formwidgets/datepicker/assets/js/datepicker.js', 'datepicker-js');
	}
	
	public function getParams(){
		
	    $locations = $this->getLocations();
	    	   
	    $dateParam = isset($_GET['date']) ? new Carbon($_GET['date']) : Carbon::now();
	    $locationParam = isset($_GET['location']) ? $_GET['location'] : array_keys($locations)[0];
	    $typeParam = isset($_GET['type']) ? $_GET['type'] : 'collection';
	    
	    return [$dateParam, $locationParam, $typeParam];
	    	    
    }

    public function getLocations()
    {
    
    	if ($this->locations) return $this->locations;
    
    	$locations = []; 
    	
    	foreach (Locations_model::get() as $l){
     
			if (AdminLocation::getId() === NULL || AdminLocation::getId() == $l->location_id){
				$locations[$l->location_id] = $l->location_name;
			}
    	
    	}
    	
    	$this->locations = $locations;
    	
    	return $locations;        
        
    }
        
    public function renderResults()
    {
	    	    
	    // to allow us to pass in use()
	    [$dateParam, $locationParam, $typeParam] = $this->getParams();
	    
	    $locations = $this->getLocations();
	    	    
	    // get our location
	    $selectedLocation = false;
	    foreach ($locations as $i=>$l){
		    if ($i == $locationParam){
			    $selectedLocation = Locations_model::where('location_id', $i)->first();
		    }
	    }
	    
	    if ($selectedLocation === false) return '<br /><h2>Location not found</h2>';

	    // get working hours for the day requested
        $openingHours = false;
	    foreach (Working_hours_model::where('location_id', $locationParam)->get() as $row){
		    
		    if ($row['day']->format('N') == $dateParam->format('N')){
		    
	       		$type = !empty($row['type']) ? $row['type'] : 'opening';
	       		
	       		if ($type == $typeParam && $row['status'] == true){
	       		
				    $openingHours = [
			            'day' => $row['day'],
			            'type' => $type,
			            'open' => $row['opening_time'],
			            'close' => $row['closing_time'],
			            'is_24_hours' => $row['open_all_day'],
				    ];
			    
			    }
		    
		    }
		    
	    }
	    
	    if ($openingHours === false) return '<br /><h2>Location is not open on this day</h2>';
	    
	    // get orders for the day requested
	    $getOrders = Orders_model::where(function($query) use ($locationParam, $dateParam){
		    $query
		    	->where('location_id', $locationParam)
		    	->where('order_date', $dateParam->format('Y-m-d'));
		})
		->orderBy('order_time', 'asc')
		->get();
		
        // create query of menu items that count as covers
	    $menuItemsThatAreCovers = DB::table('menu_categories')
		   ->whereIn('category_id', [10, 14, 15, 26, 27, 28])
           //->groupBy('menu_id')
           ->get()
           ->pluck('menu_id')
           ->toArray();
                                         		
	    $outputHours = [];
	    $outputDishes = [];
	    $outputRunning = [];
	    $totals = ['covers' => 0, 'orders' => 0, 'revenue' => 0];
	    $startTime = Carbon::createFromTimeString($openingHours['open']);
	    $endTime = Carbon::createFromTimeString($openingHours['close']);
	    
	    while ($startTime < $endTime){
		    
		    $orders = [];
		    $menuItems = [];
		    $covers = 0;
		    $revenue = 0;
		    
		    foreach ($getOrders as $o){
			    
			    if ($o->order_time == $startTime->format('H:i:s')){
				    
				    $orders[] = $o->id;
				    
				    $runningDishes = [];
				    $runningCovers = 0;
				    
				    $menus = $o->getOrderMenus();
			        foreach ($menus as $menu){
				        $menu->category_priority = 100;
				        $menuModel = \Admin\Models\Menus_model::with('categories')->where('menu_id', $menu->menu_id)->first();
				        if (isset($menuModel->categories) && sizeof($menuModel->categories) > 0){
					        $menu->category_priority = $menuModel->categories[0]->priority;
				        }
			        }
			        $menus = $menus->toArray();
			        uasort($menus, function($a, $b){
				        return $a->category_priority > $b->category_priority ? 1 : -1;
			        }); 
				    
					foreach ($menus as $menuItem){
						
						$menuItems[] = $menuItem->name;
						$menuItems[] = $menuItem->name;
						
						if (!isset($outputDishes[$menuItem->menu_id])) $outputDishes[$menuItem->menu_id] = ['quantity' => 0, 'name' => $menuItem->name];
						$outputDishes[$menuItem->menu_id]['quantity'] += $menuItem->quantity;
						
						if (!isset($runningDishes[$menuItem->menu_id])) $runningDishes[$menuItem->menu_id] = ['quantity' => 0, 'name' => $menuItem->name];
						$runningDishes[$menuItem->menu_id]['quantity'] += $menuItem->quantity;
						
						if (in_array($menuItem->menu_id, $menuItemsThatAreCovers)){
							$covers += $menuItem->quantity;
							$runningCovers += $menuItem->quantity;
						}
						
					}
					
					$runningDishesOutput = [];
					foreach ($runningDishes as $dish){
						$runningDishesOutput[] = $dish['quantity'].'x '.$dish['name'];
					}
					
					foreach ($o->getOrderTotals() as $total){
						if ($total->code == 'total'){
							$revenue += $total->value;
												
							$outputRunning[] = [
								'time' => $startTime->format('H:i'),
								'name' => $o->first_name.' '.$o->last_name,
								'phone' => $o->telephone,
								'comment' => $o->comment,
								'covers' => $runningCovers,
								'dishes' => implode('<br />', $runningDishesOutput),
								'value' => number_format($total->value, 2)
							];							
							
						}
					}
					
					//$menuItemsOptions = $model->getOrderMenuOptions();
				    
				    
			    }
		    }
		    		    
		    $outputHours[] = [
				'time' => $startTime->format('H:i'),
				'orders' => $orders,
				'totals' => [
					'covers' => $covers,
					'count' => sizeof($orders)
				]  
		    ];
		    
		    $totals['covers'] += $covers;
		    $totals['orders'] += sizeof($orders);
		    $totals['revenue'] += $revenue;

		    $startTime->add($selectedLocation->{$typeParam.'_time'}, 'minutes');
		    
	    }
	    
	    uasort($outputDishes, function($a, $b){
		 	return $a['name'] > $b['name'] ? 1 : -1;   
		});
			    
	    $html = '
	    <p><br /> </p>
	    
		<div id="form-outside-tabs" class="">
			<div class="form-fields">
				<div class="form-group partial-field span-left " data-field-name="_info" id="form-field-order-info-group">
					<div class="d-flex">
						<div class="mr-3 flex-fill">
							<label class="control-label">Orders</label>
							<h3>'.$totals['orders'].'</h3>
						</div>
						<div class="mr-3 flex-fill text-center">
							<label class="control-label">Covers</label>
							<h3>'.$totals['covers'].'</h3>
						</div>
						<div class="mr-3 flex-fill text-center">
							<label class="control-label">Revenue</label>
							<h3>&pound;'.number_format($totals['revenue'], 2).'</h3>
						</div>						
					</div>
				</div>
			</div>
		</div>   
		
	    <p><br /> </p>
	    
	    <div id="form-primary-tabs" class="primary-tabs" data-control="form-tabs">
	    
			<div class="tab-heading">
			    <ul class="form-nav nav nav-tabs">
		            <li class="nav-item">
		                <a class="nav-link active" href="#primarytab-1" data-toggle="tab" data-original-title="" title="">Orders by interval</a>
		            </li>
		            <li class="nav-item">
		                <a class="nav-link" href="#primarytab-2" data-toggle="tab" data-original-title="" title="">Service totals</a>
		            </li>
		            <li class="nav-item">
		                <a class="nav-link" href="#primarytab-3" data-toggle="tab" data-original-title="" title="">Running order</a>
		            </li>		            
			    </ul>
			</div>	 
			
			<div class="tab-content">
			
				<div class="tab-pane active" id="primarytab-1">
					<div class="form-fields">
					
						<div class="form-group">
							<div class="table-responsive">
							
							    <table class="table table-striped">
							        <thead>
							        <tr>
							            <th width="50%">Time</th>
							            <th class="text-center">Orders</th>
							            <th class="text-center">Covers</th>
							        </tr>
							        </thead>
							        <tbody>
		';
		
		foreach ($outputHours as $hours){
			
			$html .= '
							            <tr>
							                <td>'.$hours['time'].'</td>
							                <td class="text-center">'.$hours['totals']['count'].'</td>
							                <td class="text-center">'.$hours['totals']['covers'].'</td>
							            </tr>
			';
			
		}
		
		$html .= '
							        </tbody>
							    </table>
							    
							</div>
						</div>
					
					</div>
				</div>

				<div class="tab-pane" id="primarytab-2">
					<div class="form-fields">
					
						<div class="form-group">
							<div class="table-responsive">
							
							    <table class="table table-striped">
							        <thead>
							        <tr>
							            <th width="65%">Menu item</th>
							            <th class="text-center">Quantity</th>
							        </tr>
							        </thead>
							        <tbody>
		';
		
		foreach ($outputDishes as $dish){
			
			$html .= '
							            <tr>
							                <td>'.$dish['name'].'</td>
							                <td class="text-center">'.$dish['quantity'].'</td>
							            </tr>
			';
			
		}
		
		$html .= '
							        </tbody>
							    </table>
							    
							</div>
						</div>
				
					</div>
				</div>
				
				<div class="tab-pane" id="primarytab-3">
					<div class="form-fields">
					
						<div class="form-group" style="width:100%">
							<div class="table-responsive">
							
							    <table class="table table-striped" width="100%">
							        <thead>
							        <tr>
							            <th width="10%">Time</th>
							            <th>Name</th>
							            <th>Phone</th>
							            <th>Covers</th>
							            <th>Order</th>
							            <th>Comments</th>
							            <th>Total</th>
							        </tr>
							        </thead>
							        <tbody>
		';
		
		foreach ($outputRunning as $running){
			
			$html .= '
							            <tr>
							                <td>'.$running['time'].'</td>
							                <td>'.$running['name'].'</td>
							                <td>'.$running['phone'].'</td>
							                <td>'.$running['covers'].'</td>
							                <td>'.$running['dishes'].'</td>
							                <td>'.$running['comment'].'</td>
							                <td>&pound;'.$running['value'].'</td>
							            </tr>
			';
			
		}
		
		$html .= '
							        </tbody>
							    </table>
							    
							</div>
						</div>
					
					</div>
				</div>
							
			</div>   	
	    
	    </div>
	    ';
	    
	    return $html;
	    
    }
    
}
