<?php

namespace Thoughtco\KitchenDisplay\Controllers;

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
use Thoughtco\KitchenDisplay\Models\Settings as KitchenSettings;

/**
 * Order Summary
 */
class Summary extends \Admin\Classes\AdminController
{

    protected $requiredPermissions = 'Thoughtco.KitchenDisplay.*';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('sales', 'summary');
        Template::setTitle(lang('lang:thoughtco.kitchendisplay::default.text_title'));
        
    }
    
    public function index()
    {
	    // add CSS and JS
	    $this->addCSS('extensions/thoughtco/kitchendisplay/assets/css/kds.css', 'thoughtco-kds');
	    $this->addJS('extensions/thoughtco/kitchendisplay/assets/js/kds.js', 'thoughtco-kds');

	    // url params and defaults
	    $action = Request::get('action', '');
	    $orderId = Request::get('order', -1);
	    	    
	    // if we have an action and an order id
	    if ($action != '' and $orderId > -1){
		    
		    $sale = Orders_model::where('order_id', $orderId)->first();
			
			// user configs
			$prepStatus = KitchenSettings::get('prep_status');
			$readyStatus = KitchenSettings::get('ready_status');
			$completeStatus = KitchenSettings::get('completed_status');
			
			// not user config
			if (!$prepStatus){
		    	$processingStatuses = setting('processing_order_status');
		    	$completedStatuses = setting('completed_order_status');
				$completeStatus = array_shift($completedStatuses);
				$prepStatus = array_shift($processingStatuses);
				$readyStatus = array_pop($processingStatuses);
			}
		    	    
		    // valid sale and complete
		    if ($action == 'complete'){
		    	if ($sale !== NULL){
			    	$status = Statuses_model::where(['status_id' => $completeStatus])->first();
			    	if ($status){
					    $sale->updateOrderStatus($status->status_id, ['notify' => FALSE]);
				    	if ($status->notify_customer)
				    		$sale->mailSend('admin::_mail.order_update', 'customer');
				    }
			    }	
			    return $this->redirect('thoughtco/kitchendisplay/summary');
		    }
		    
	    	// valid sale and preparation
		    if ($action == 'prep'){
		    	if ($sale !== NULL){
			    	$status = Statuses_model::where(['status_id' => $prepStatus])->first();
			    	if ($status){
					    $sale->updateOrderStatus($status->status_id, ['notify' => FALSE]);
				    	if ($status->notify_customer)
				    		$sale->mailSend('admin::_mail.order_update', 'customer');
				    }
			    }	
			    return $this->redirect('thoughtco/kitchendisplay/summary');
		    }
		    
	    	// valid sale and ready
		    if ($action == 'ready'){
		    	if ($sale !== NULL){
			    	$status = Statuses_model::where(['status_id' => $readyStatus])->first();
			    	if ($status){
					    $sale->updateOrderStatus($status->status_id, ['notify' => FALSE]);
				    	if ($status->notify_customer)
				    		$sale->mailSend('admin::_mail.order_update', 'customer');
				    }
			    }	
			    return $this->redirect('thoughtco/kitchendisplay/summary');
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
		
		// user configs
		$prepStatus = KitchenSettings::get('prep_status');
		$readyStatus = KitchenSettings::get('ready_status');
		$completedStatus = KitchenSettings::get('completed_status');
		$prepColor = KitchenSettings::get('prep_color');
		$readyColor = KitchenSettings::get('ready_color');
		$completedColor = KitchenSettings::get('completed_color');
		$showaddress = KitchenSettings::get('show_address');
		
		// not user config
		if (!$prepStatus){
	    	$processingStatuses = setting('processing_order_status');
	    	$completedStatuses = setting('completed_order_status');
			$completedStatus = array_shift($completedStatuses);
			$prepStatus = array_shift($processingStatuses);
			$readyStatus = array_pop($processingStatuses);
			
			// build colours
			$statusColors = Statuses_model::all()->pluck('status_color', 'status_id');
			
		    $prepColor = $statusColors[$prepStatus];
		    $readyColor = $statusColors[$readyStatus];
		    $completedColor = $statusColors[$completedStatus];
			
		}
	    
	    // what statuses do we ignore?
	    $ignoreStatuses = [$completedStatus];
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
				
		$outputRunning = [];
		
	    foreach ($getOrders as $o){
		    
		    $runningDishes = [];
			
			$o->order_time = substr($o->order_time, 0, -3);
		    
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
					
					$runningDishes[] = '<ul class="list-unstyled mb-0 pl-3">';
					foreach ($menuItemOptions as $menuItemOption) { 
						$runningDishes[] = '<li>'.$menuItemOption->quantity.'x '.$menuItemOption->order_option_name;
					}
					$runningDishes[] = '</li>';
					$runningDishes[] = '</ul>';
				} 
				else {
			            $runningDishes[] = '<br/>';
				}

				   
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
						'address' => ($showaddress == 1 ? ((isset($o->address->address_1) ? ucwords($o->address->address_1).', ' : '').(isset($o->address->postcode) ? strtoupper($o->address->postcode) : '')) : ''),
						'comment' => $o->comment,
						'dishes' => $runningDishes,
						'value' => currency_format($total->value),
						'status_name'=>$o->status_name,
						'status_color'=>$o->status_color,
						'buttons' => '
		                	<a class="btn label-default'.($o->status_id != $prepStatus ? '" href="'.admin_url('thoughtco/kitchendisplay/summary?action=prep&order='.$o->order_id).'" style="background-color:'.$prepColor.'";' : ' btn-light"').'>'.lang('lang:thoughtco.kitchendisplay::default.btn_prep').'</a>
							<a class="btn label-default'.($o->status_id != $readyStatus ? '" href="'.admin_url('thoughtco/kitchendisplay/summary?action=ready&order='.$o->order_id).'" style="background-color:'.$readyColor.'";' : ' btn-light"').'>'.lang('lang:thoughtco.kitchendisplay::default.btn_ready').'</a>
							<a class="btn label-default'.($o->status_id != $completedStatus ? '" href="'.admin_url('thoughtco/kitchendisplay/summary?action=complete&order='.$o->order_id).'" style="background-color:'.$completedColor.'";' : ' btn-light"').'>'.lang('lang:thoughtco.kitchendisplay::default.btn_complete').'</a>
						',
					];							
				}
			}
		    
		}
		
		return $outputRunning;
			    
    }
    
}
