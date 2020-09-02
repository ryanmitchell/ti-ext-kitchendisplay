<?php

namespace Thoughtco\Runningorder\Controllers;

use AdminMenu;
use Admin\Facades\AdminLocation;
use Admin\Models\Locations_model;
use Admin\Models\Menus_model;
use Admin\Models\Orders_model;
use Admin\Models\Statuses_model;
use ApplicationException;
use Carbon\Carbon;
use DB;
use Igniter\Flame\Currency;
use Request;
use Template;

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
        Template::setTitle(lang('lang:thoughtco.runningorder::default.text_title'));
        
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
				    $status = $sale->updateOrderStatus(array_shift($completedStatuses), ['notify_customer' => true]);
			    }	
			    return $this->redirect('thoughtco/runningorder/summary');
		    }
		    
	    	// valid sale and preparation
		    if ($action == 'prep'){
		    	if ($sale !== NULL){
				    $status = $sale->updateOrderStatus(array_shift($processingStatuses), ['notify_customer' => true]);
			    }	
			    return $this->redirect('thoughtco/runningorder/summary');
		    }
		    
	    	// valid sale and ready
		    if ($action == 'ready'){
		    	if ($sale !== NULL){
				    $status = $sale->updateOrderStatus(array_pop($processingStatuses), ['notify_customer' => true]);
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
		->orderBy('order_date', 'asc')
		->orderBy('order_time', 'asc')
		->limit(30)
		->get();
		
		// build colours
		$statusColors = Statuses_model::all()->pluck('status_color', 'status_id');
		
		$processingStatuses = setting('processing_order_status');
		$completedStatuses = setting('completed_order_status');
		
		$prepStatus = array_shift($processingStatuses);
		$readyStatus = array_pop($processingStatuses);
		$completedStatus = array_shift($completedStatuses);
		
	    $prepColor = $statusColors[$prepStatus];
	    $readyColor = $statusColors[$readyStatus];
	    $completedColor = $statusColors[$completedStatus];
		
		$outputRunning = [];
		
	    foreach ($getOrders as $o){
		    
		    $runningDishes = [];
		    
		    $menuItems = $o->getOrderMenus();
   			$menuItemsOptions = $o->getOrderMenuOptions();

	        foreach ($menuItems as $menu){
		        $menu->category_priority = 100;
		        $menuModel = Menus_model::with('categories')->where('menu_id', $menu->menu_id)->first();
		        if (isset($menuModel->categories) && sizeof($menuModel->categories) > 0){
			        $menu->category_priority = $menuModel->categories[0]->priority;
		        }
	        }
	        
	        $menuItems = $menuItems->toArray();
	        uasort($menuItems, function($a, $b){
		        return $a->category_priority > $b->category_priority ? 1 : -1;
	        }); 
		    
			foreach ($menuItems as $menuItem){
				
				$runningDishes[] = '<strong>'.$menuItem->quantity.'x '.$menuItem->name.'</strong>';

				if ($menuItemOptions = $menuItemsOptions->get($menuItem->order_menu_id)) { 
				$runningDishes[] = '<ul class="list-unstyled" style="padding-left:1rem;margin-bottom:0">';
					foreach ($menuItemOptions as $menuItemOption) { 
						$runningDishes[] = '<li>'.$menuItemOption->quantity.'x '.$menuItemOption->order_option_name;
                    }
            $runningDishes[] = '</li>';}
            $runningDishes[] = '</ul>';    
                if ($menuItem->comment != ''){
	            	$runningDishes[] = '<em>'.$menuItem->comment.'</em><br/>';   
                }
                
                $runningDishes[] = '';
                
      		}
			
			foreach ($o->getOrderTotals() as $total){
		        if ($total->code == 'total' || $total->code == 'order_total'){
					$outputRunning[] = (object)[
						'id' => $o->order_id,
						'time' => $o->order_time,
						'name' => $o->first_name.' '.$o->last_name,
						'phone' => $o->telephone,
						'comment' => $o->comment,
						'dishes' => $runningDishes,
						'value' => currency_format($total->value),
						'status_name'=>$o->status_name,
						'status_color'=>$o->status_color,
						'buttons' => '
		                	<a class="btn '.($o->status_id != $prepStatus ? '" href="'.admin_url('thoughtco/runningorder/summary?action=prep&order='.$o->order_id).'" style="background-color:'.$prepColor.'";' : 'btn-light"').'>'.lang('lang:thoughtco.runningorder::default.btn_prep').'</a>
							<a class="btn '.($o->status_id != $readyStatus ? '" href="'.admin_url('thoughtco/runningorder/summary?action=ready&order='.$o->order_id).'" style="background-color:'.$readyColor.'";' : 'btn-light"').'>'.lang('lang:thoughtco.runningorder::default.btn_ready').'</a>
							<a class="btn '.($o->status_id != $completedStatus ? '" href="'.admin_url('thoughtco/runningorder/summary?action=complete&order='.$o->order_id).'" style="background-color:'.$completedColor.'";' : 'btn-light"').'>'.lang('lang:thoughtco.runningorder::default.btn_complete').'</a>
						',
					];							
				}
			}
		    
		}
		
		return $outputRunning;
			    
    }
    
}
