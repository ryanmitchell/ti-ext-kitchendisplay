<?php

namespace Thoughtco\Runningorder\Controllers;

use AdminMenu;
use Admin\Facades\AdminLocation;
use Admin\Models\Locations_model;
use Admin\Models\Orders_model;
use ApplicationException;
use Carbon\Carbon;
use DB;
use Igniter\Flame\Currency;
use Request;

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
	    
	    // url params and defaults
	    $action = Request::get('action', '');
	    $orderId = Request::get('order', -1);
	    	    
	    // if we have an action and an order id
	    if ($action != '' and $orderId > -1){
		    
		    $sale = Orders_model::where('order_id', $orderId)->first();
		    
		    $processingStatuses = setting('processing_order_status');
		    $completedStatuses = setting('completed_order_status');
		    	    
		    // valid sale and complete
		    if ($action == 'complete'){
		    	if ($sale !== NULL){
				    $status = $sale->updateOrderStatus(array_shift($completedStatuses));
			    }	
			    return $this->redirect('thoughtco/runningorder/summary');
		    }
		    
	    	// valid sale and preparation
		    if ($action == 'prep'){
		    	if ($sale !== NULL){
				    $status = $sale->updateOrderStatus(array_shift($processingStatuses));
			    }	
			    return $this->redirect('thoughtco/runningorder/summary');
		    }
		    
	    	// valid sale and ready
		    if ($action == 'ready'){
		    	if ($sale !== NULL){
				    $status = $sale->updateOrderStatus(array_pop($processingStatuses));
			    }	
			    return $this->redirect('thoughtco/runningorder/summary');
		    }
	    
	    }
	    
	}
	
	public function getParams(){
		
	    $locations = $this->getLocations();
	    	   
	    $locationParam = Request::get('location', array_keys($locations)[0]);
	    
	    return [$locationParam];
	    	    
    }

public function getLocations()
    {
    
    	if ($this->locations) return $this->locations;
    
    	$locations = []; 
    	
    	foreach (Locations_model::get() as $l){
     
			if (AdminLocation::getId() === NULL || AdminLocation::getId() == $l->location_id){
				
				if ($l->location_status){
				
					$locations[$l->location_id] = $l->location_name;
				
				}
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
	    
	    // what statuses do we ignore?
	    $ignoreStatuses = setting('completed_order_status');
	    $ignoreStatuses[] = setting('canceled_order_status');
	    
	    // get orders for the day requested
	    $getOrders = Orders_model::where(function($query) use ($selectedLocation, $ignoreStatuses){
		    $query
				->whereNotIn('status_id', $ignoreStatuses);
		    	
		    if (AdminLocation::getId() !== NULL){
		    	$query->where('location_id', $selectedLocation->location_id);
		    }
		})
		->orderBy('order_time', 'asc')
		->limit(30)
		->get();
		
		$outputRunning = [];
		
	    foreach ($getOrders as $o){
		    
		    $runningDishes = [];
		    $runningDishOptions = [];
		    
		    $menuItems = $o->getOrderMenus();
   			$menuItemsOptions = $o->getOrderMenuOptions();

	        foreach ($menuItems as $menu){
		        $menu->category_priority = 100;
		        $menuModel = \Admin\Models\Menus_model::with('categories')->where('menu_id', $menu->menu_id)->first();
		        if (isset($menuModel->categories) && sizeof($menuModel->categories) > 0){
			        $menu->category_priority = $menuModel->categories[0]->priority;
		        }
	        }
	        
	        $menuItems = $menuItems->toArray();
	        uasort($menuItems, function($a, $b){
		        return $a->category_priority > $b->category_priority ? 1 : -1;
	        }); 
		    
			foreach ($menuItems as $menuItem){
				if (!isset($runningDishes[$menuItem->menu_id])) $runningDishes[$menuItem->menu_id] = ['menu_id'=>$menuItem->menu_id,'quantity' => 0, 'name' => $menuItem->name];
				$runningDishes[$menuItem->menu_id]['quantity'] += $menuItem->quantity;
				if ($menuItemOptions = $menuItemsOptions->get($menuItem->order_menu_id)) { 
					foreach ($menuItemOptions as $menuItemOption) { 
						$runningDishOptions[$menuItem->menu_id] = ['optionmenu_id'=>$menuItemOption->menu_id,'quantity'=>$menuItemOption->quantity,'optionname'=>$menuItemOption->order_option_name];
                    }
                }
      		}
			
			$runningDishesOutput = [];
			foreach ($runningDishes as $dish){
				$runningDishesOutput[] = '<b>'.$dish['quantity'].'x '.$dish['name'].'</b>';
				foreach ($runningDishOptions as $dishOption) { 
					if ($dishOption['optionmenu_id'] == $dish['menu_id']) $runningDishesOutput[] = $dishOption['quantity'].'x '.$dishOption['optionname'];
        		}  
			}
			
			foreach ($o->getOrderTotals() as $total){
		        if ($total->code == 'total' || $total->code == 'order_total'){
					$outputRunning[] = [
						'id' => $o->order_id,
						'time' => $o->order_time,
						'name' => $o->first_name.' '.$o->last_name,
						'phone' => $o->telephone,
						'comment' => $o->comment,
						'dishes' => implode('<br />', $runningDishesOutput),
						'value' => number_format($total->value, 2),
						'status_name'=>$o->status_name,
						'status_color'=>$o->status_color
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
					            <th>ID</th>
					            <th>Time</th>
					            <th>Name</th>
					            <th>Phone</th>
					            <th width="15%">Order</th>
					            <th>Comments</th>
					            <th>Total</th>
					            <th>Status</th>
					            <th></th>
					        </tr>
				        </thead>
				        <tbody>
		';
		
		foreach ($outputRunning as $running){

			$html .= '
				            <tr>
				                <td>'.$running['id'].'</td>
				                <td>'.$running['time'].'</td>
				                <td>'.$running['name'].'</td>
				                <td>'.$running['phone'].'</td>
				                <td>'.$running['dishes'].'</td>
				                <td>'.$running['comment'].'</td>
				                <td>'.currency_format($running['value']).'</td>
				                <td><span class="label label-default" style="background-color:'.$running['status_color'].'";>'.$running['status_name'].'</span></td>
				                <td>
				                	<a class="btn btn-primary" href="'.admin_url('thoughtco/runningorder/summary?action=prep&order='.$running['id']).'">Prep</a>
									<a class="btn btn-secondary ml-3" href="'.admin_url('thoughtco/runningorder/summary?action=ready&order='.$running['id']).'">Ready</a>
									<a class="btn btn-success ml-3" href="'.admin_url('thoughtco/runningorder/summary?action=complete&order='.$running['id']).'">Complete</a>
				                </td>
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
