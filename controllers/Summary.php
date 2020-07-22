<?php

namespace Thoughtco\Runningorder\Controllers;

use AdminMenu;
use Admin\Facades\AdminLocation;
use Admin\Models\Locations_model;
use Admin\Models\Orders_model;
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
	    if (isset($_GET['complete'])){
		    
	    	$sale = Orders_model::where('order_id', $_GET['complete'])->first();

	    	// valid sale
	    	if ($sale !== NULL){
			    $status = $sale->updateOrderStatus(5);
		    }	
		    
		    return $this->redirect('thoughtco/runningorder/summary');
		    
	    }
	    
	}
	
	public function getParams(){
		
	    $locations = $this->getLocations();
	    	   
	    $locationParam = isset($_GET['location']) ? $_GET['location'] : array_keys($locations)[0];
	    
	    return [$locationParam];
	    	    
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
	    [$locationParam] = $this->getParams();
	    
	    $locations = $this->getLocations();
	    	    
	    // get our location
	    $selectedLocation = false;
	    foreach ($locations as $i=>$l){
		    if ($i == $locationParam){
			    $selectedLocation = Locations_model::where('location_id', $i)->first();
		    }
	    }
	    
	    if ($selectedLocation === false) return '<br /><h2>Location not found</h2>';
	    
	    // get orders for the day requested
	    $getOrders = Orders_model::where(function($query) use ($selectedLocation){
		    $query
				->where('status_id', '<=', 1)
		    	->where('order_date', Carbon::now()->format('Y-m-d'));
		    	
		    if (AdminLocation::getId() !== NULL){
		    	$query->where('location_id', $selectedLocation);
		    }
		})
		->orderBy('order_time', 'asc')
		->limit(30)
		->get();
		
		$outputRunning = [];
		
	    foreach ($getOrders as $o){
		    
		    $runningDishes = [];
		    
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
				if (!isset($runningDishes[$menuItem->menu_id])) $runningDishes[$menuItem->menu_id] = ['quantity' => 0, 'name' => $menuItem->name];
				$runningDishes[$menuItem->menu_id]['quantity'] += $menuItem->quantity;
			}
			
			$runningDishesOutput = [];
			foreach ($runningDishes as $dish){
				$runningDishesOutput[] = $dish['quantity'].'x '.$dish['name'];
			}
			
			foreach ($o->getOrderTotals() as $total){
				if ($total->code == 'total'){
										
					$outputRunning[] = [
						'id' => $o->order_id,
						'time' => $o->order_time,
						'name' => $o->first_name.' '.$o->last_name,
						'phone' => $o->telephone,
						'comment' => $o->comment,
						'dishes' => implode('<br />', $runningDishesOutput),
						'value' => number_format($total->value, 2)
					];							
					
				}
			}
		    
		}
	    
	    $html = '
	    <p><br /> </p>
	    
		<div class="form-fields">
		
			<div class="form-group" style="width:100%">
				<div class="table-responsive">
				
				    <table class="table table-striped" width="100%">
				        <thead>
				        <tr>
				            <th width="10%">Time</th>
				            <th>Name</th>
				            <th>Phone</th>
				            <th>Order</th>
				            <th>Comments</th>
				            <th>Total</th>
				            <th></th>
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
				                <td>'.$running['dishes'].'</td>
				                <td>'.$running['comment'].'</td>
				                <td>&pound;'.$running['value'].'</td>
				                <td><a class="btn btn-primary" href="'.admin_url('thoughtco/runningorder/summary?complete='.$running['id']).'">Complete</a></td>
				            </tr>
			';
			
		}
		
		$html .= '
				        </tbody>
				    </table>
				    
				</div>
			</div>

	    </div>
	    ';
	    
	    return $html;
	    
    }
    
}
