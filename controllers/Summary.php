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
use Thoughtco\KitchenDisplay\Models\Views as KitchenViews;

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
    
    public function view($route, $viewId)
    {
		if ($viewSettings = KitchenViews::where([
			['id', $viewId],
			['is_enabled', 1],
		])->first())
		{
			
		    // add CSS and JS
		    $this->addCSS('extensions/thoughtco/kitchendisplay/assets/css/kds.css', 'thoughtco-kds');
		    $this->addJS('extensions/thoughtco/kitchendisplay/assets/js/kds.js', 'thoughtco-kds');
	
		    // url params and defaults
		    $action = Request::get('action', '');
		    $actionId = Request::get('actionId', 1);
		    $orderId = Request::get('orderId', -1);
		    	    
		    // if we have an action and an order id
		    if ($action != '' and $orderId > -1)
			{
			    
			    $sale = Orders_model::where('order_id', $orderId)->first();
			    	    
			    // update status
			    if ($action == 'status')
				{
					
			    	if ($sale !== NULL)
					{
				    	$status = Statuses_model::where(['status_id' => $actionId])->first();
				    	if ($status)
						{
						    $sale->updateOrderStatus($status->status_id, ['notify' => FALSE]);
					    	if ($status->notify_customer)
					    		$sale->mailSend('admin::_mail.order_update', 'customer');
					    }
				    }	
					
				    return $this->redirect('thoughtco/kitchendisplay/summary/view/'.$viewId);
			    }
		    
		    }
	
			$this->vars['viewSettings'] = $viewSettings;
			$this->vars['results'] = [];
						
			// what statuses do we query
			$statuses = [$viewSettings->order_status];
			if ($viewSettings->display['button1_enable'])
				$statuses[] = $viewSettings->display['button1_status'];
			if ($viewSettings->display['button2_enable'])
				$statuses[] = $viewSettings->display['button2_status'];
		    
		    // get orders for the day requested
		    $getOrders = Orders_model::where(function($query) use ($viewSettings, $statuses){
			    $query->whereIn('status_id', $statuses);
					
				if ($viewSettings->locations != '')
					$query->whereIn('location_id', $viewSettings->locations);
				
				if ($viewSettings->order_types != 0)
					$query->where('order_type', $viewSettings->order_types == 1 ? 'delivery' : 'collection');
			})
			->orderBy('order_date', 'asc')
			->orderBy('order_time', 'asc')
			->limit($viewSettings->display['order_count'])
			->get();
							
		    foreach ($getOrders as $orderIdx => $order)
			{
			    
			    $runningDishes = [];
				
				$buttonUrl = admin_url('thoughtco/kitchendisplay/summary/view/'.$viewId.'?orderId='.$order->order_id.'&action=status&actionId=');
				
				$order->order_time = substr($order->order_time, 0, -3);
			    
			    $menuItems = $order->getOrderMenus();
	   			$menuItemsOptions = $order->getOrderMenuOptions();
	
		        $menuItems = $menuItems->toArray();
		        foreach ($menuItems as $menuIdx => $menu)
				{
			        $menu->category_priority = 100;
			        $menuModel = Menus_model::with('categories')->where('menu_id', $menu->menu_id)->first();
			        if (isset($menuModel->categories) && count($menuModel->categories) > 0)
					{
				        $menu->category_priority = $menuModel->categories[0]->priority;
						
						// if we have no overlapping categories then remove
						if (!count(array_intersect($menuModel->categories->pluck('category_id')->toArray(), $viewSettings->categories)))
							unset($menuItems[$menuIdx]);
			        } 
					else if (count($viewSettings->categories))
					{
						unset($menuItems[$menuIdx]);
					}
		        }
				
				// if we have no menu items
				if (!count($menuItems))
					continue;
		        
				// order by category priority
		        uasort($menuItems, function($a, $b){
			        return $a->category_priority > $b->category_priority ? 1 : -1;
		        }); 
			    
				foreach ($menuItems as $menuItem)
				{
					
					$runningDishes[] = '<strong>'.$menuItem->quantity.'x '.$menuItem->name.'</strong>';
	
					if ($menuItemOptions = $menuItemsOptions->get($menuItem->order_menu_id))
					{ 
						
						$runningDishes[] = '<ul class="list-unstyled mb-0 pl-3">';
						foreach ($menuItemOptions as $menuItemOption)
						{ 
							$runningDishes[] = '<li>'.$menuItemOption->quantity.'x '.$menuItemOption->order_option_name;
						}
						$runningDishes[] = '</li>';
						$runningDishes[] = '</ul>';
					} 
					else 
					{
				        $runningDishes[] = '<br/>';
					}
					   
	                if ($menuItem->comment != '')
					{
		            	$runningDishes[] = '<em>'.$menuItem->comment.'</em><br/>';   
	                }
	                
	                $runningDishes[] = '';
	                
	      		}
				
				foreach ($order->getOrderTotals() as $total)
				{
			        if ($total->code == 'total' || $total->code == 'order_total')
					{
						
        				$outputAddress = lang('admin::lang.orders.text_collection_order_type');
						if ($order->address)
						{
	        				$address = $order->address->toArray();
	        				$address['format'] = '{address_1}, {address_2}, {city}, {postcode}';
							$outputAddress = str_replace(', , ', ', ', format_address($address, TRUE));
						}
						
						$this->vars['results'][] = (object)[
							'id' => $order->order_id,
							'time' => $order->order_time,
							'name' => $order->first_name.' '.$order->last_name,
							'address' => $outputAddress,
							'phone' => $order->telephone,
							'comment' => $order->comment,
							'dishes' => $runningDishes,
							'value' => currency_format($total->value),
							'status_name' => $order->status_name,
							'status_color' => $order->status_color,
							'buttons' => '
			                	'.($viewSettings->display['button1_enable'] ? '<a class="btn label-default'.($order->status_id != $viewSettings->display['button1_status'] ? '" href="'.$buttonUrl.$viewSettings->display['button1_status'].'" style="background-color:'.$viewSettings->display['button1_color'].'";' : ' btn-light"').'>'.$viewSettings->display['button1_text'].'</a>' : '').'
								'.($viewSettings->display['button2_enable'] ? '<a class="btn label-default'.($order->status_id != $viewSettings->display['button2_status'] ? '" href="'.$buttonUrl.$viewSettings->display['button2_status'].'" style="background-color:'.$viewSettings->display['button2_color'].'";' : ' btn-light"').'>'.$viewSettings->display['button2_text'].'</a>' : '').'
								'.($viewSettings->display['button3_enable'] ? '<a class="btn label-default'.($order->status_id != $viewSettings->display['button3_status'] ? '" href="'.$buttonUrl.$viewSettings->display['button3_status'].'" style="background-color:'.$viewSettings->display['button3_color'].'";' : ' btn-light"').'>'.$viewSettings->display['button3_text'].'</a>' : '').'
							',
						];							
					}
				}
			
			}
		
		}
			    
    }
    
}
