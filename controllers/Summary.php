<?php

namespace Thoughtco\KitchenDisplay\Controllers;

use Admin\Facades\AdminAuth;
use Admin\Facades\AdminLocation;
use Admin\Facades\AdminMenu;
use Admin\Facades\Template;
use Admin\Models\Menus_model;
use Admin\Models\Orders_model;
use Admin\Models\Staffs_model;
use Admin\Models\Statuses_model;
use Carbon\Carbon;
use Igniter\Flame\Exception\ApplicationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
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
        $viewSettings = KitchenViews::where([
			['id', $viewId],
			['is_enabled', 1],
		])->first();

        if (!AdminAuth::isSuperUser()) {
            if (AdminLocation::getId() === NULL || in_array(AdminLocation::getId(), $viewSettings->locations)) {

                $limitedUsers = array_get($viewSettings->display, 'users_limit', []);

				if (count($limitedUsers) AND !in_array(AdminAuth::getId(), $limitedUsers))
                    throw new ApplicationException('Permission denied');

            }
        }

		if ($viewSettings)
		{

        	Template::setTitle($viewSettings->name);
        	Template::setHeading($viewSettings->name);

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

			    if ($sale !== NULL)
				{

				    // update status
				    if ($action == 'status')
					{
				    	$status = Statuses_model::where(['status_id' => $actionId])->first();
				    	if ($status)
						    $sale->updateOrderStatus($status->status_id);
				    }

				    // update assignment
				    if ($action == 'assign')
					{
						$sale->updateAssignTo(null, Staffs_model::find($actionId));
					}

				}

				return $this->redirect('thoughtco/kitchendisplay/summary/view/'.$viewId);

		    }

			$this->vars['viewSettings'] = $viewSettings;
			$this->vars['results'] = [];

            // what statuses do we query
             if (isset($viewSettings->display['orders_forXhours'])){
                 $statuses = $viewSettings->order_status;
             } else {
                 $statuses = [];

                 if (!count($statuses))
                 {
                     $statuses[] = $viewSettings->order_status;
                     if ($viewSettings->display['button1_enable'])
                         $statuses[] = $viewSettings->display['button1_status'];
                     if ($viewSettings->display['button2_enable'])
                         $statuses[] = $viewSettings->display['button2_status'];
                 }
            }

		    // get orders for the day requested
		    $getOrders = Orders_model::where(function($query) use ($viewSettings, $statuses){

			    if (isset($statuses))
					$query->whereIn('status_id', $statuses);

                if (isset($viewSettings->display['orders_forXhours']) AND $viewSettings->display['orders_forXhours'] > 0)
                    $query->where(DB::raw("CONCAT(order_date, ' ', order_time)"), '<=', Carbon::now()->addHours($viewSettings->display['orders_forXhours'])->format('Y-m-d H:i'));

			    if ($viewSettings->order_assigned != '')
					$query->where('assignee_id', $viewSettings->order_assigned);

				if ($viewSettings->locations != '')
					$query->whereIn('location_id', $viewSettings->locations);

                // backwards compat
                if (is_numeric($viewSettings->order_types))
					$query->where('order_type', $viewSettings->order_types == 1 ? 'delivery' : 'collection');
                else if ($viewSettings->order_types != '')
					$query->whereIn('order_type', $viewSettings->order_types);
			})
			->orderBy('order_date', 'asc')
			->orderBy('order_time', 'asc')
			->limit($viewSettings->display['order_count'])
            ->get();

		    foreach ($getOrders as $orderIdx => $order)
			{

			    $runningDishes = [];

				$assignUrl = admin_url('thoughtco/kitchendisplay/summary/view/'.$viewId.'?orderId='.$order->order_id);
				$buttonUrl = $assignUrl.'&action=status&actionId=';

                $menuItems = $order->getOrderMenusWithOptions();

                foreach ($menuItems as $key => $menu) {

                    $forget = false;
                    $hasMenuOption = false;

                    $menuModel = Menus_model::with('categories')->where('menu_id', $menu->menu_id)->first();

                    if ($menuModel) {

    					// if we have no overlapping categories then remove
    					if (isset($viewSettings->categories) && count($viewSettings->categories) > 0)
    					{
                            $menu_cats = $menuModel->categories->pluck('category_id')->toArray();
    						if (count($menu_cats) AND count(array_intersect($menu_cats, $viewSettings->categories)) < 1)
    							$forget = true;
    					}
                        else if (isset($viewSettings->categories) && count($viewSettings->categories) > 0)
    					{
    						$forget = true;
                        }

                        if ($forget)
                        {
                            $menuItems->forget($key);
                            continue;
                        }

                        $optionData = [];

    			        $menu->category_priority = 100;
                        if ($cat = $menuModel->categories->sortBy('priority')->first())
                            $menu->category_priority = $cat->priority;

       					$runningDishes[] = '<strong>'.$menu->quantity.'x '.$menu->name.'</strong>';

                        foreach ($menu->menu_options->groupBy('order_option_group') as $menuItemOptionGroupName => $menuItemOptions) {

                            if (!$hasMenuOption)
        						$runningDishes[] = '<ul class="list-unstyled mb-0 pl-3">';

                            $hasMenuOption = true;

                            $runningDishes[] = '<li><strong>'.$menuItemOptionGroupName.'</strong></li>';

                            foreach ($menuItemOptions as $menuItemOption) {
        						$runningDishes[] = '<li>'.$menuItemOption->quantity.'x '.$menuItemOption->order_option_name;
                            }

                        }

                    }

					if (!$hasMenuOption)
				        $runningDishes[] = '<br/>';
                    else
    					$runningDishes[] = '</ul>';

	                if ($menu->comment != '')
					{
		            	$runningDishes[] = '<em>'.$menu->comment.'</em><br/>';
	                }

	                $runningDishes[] = '';

                }

				// if we have no menu items
				if (!count($menuItems) > 0)
					continue;

                $menuItems = $menuItems->sortBy('category_priority');

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

						$payment_code = lang('admin::lang.orders.text_no_payment');
						if ($order->payment_method)
						{
							$payment_code = strtoupper($order->payment_method->code);
						}

						$time = lang('igniter.local::default.text_asap');
						if (!$order->order_time_is_asap)
						    $time = Carbon::createFromTimeString($order->order_time)->format(lang('system::lang.php.time_format'));

						$this->vars['results'][] = (object)[
							'id' => $order->order_id,
							'type' => $order->order_type,
							'type_name' => $order->order_type_name,
							'time' => $time,
							'date' => $order->order_date->format(lang('system::lang.php.date_format')),
							'name' => $order->first_name.' '.$order->last_name,
							'address' => $outputAddress,
							'payment_code' => $payment_code,
							'phone' => $order->telephone,
							'comment' => $order->comment,
							'dishes' => $runningDishes,
							'value' => currency_format($total->value),
							'status_name' => $order->status_name,
							'status_color' => $order->status_color,
							'buttons' => '
			                	'.($viewSettings->display['button1_enable'] ? '<a class="btn label-default'.($order->status_id != $viewSettings->display['button1_status'] ? '" href="'.$buttonUrl.$viewSettings->display['button1_status'].'" style="background-color:'.$viewSettings->display['button1_color'].'!important";' : ' btn-light"').'>'.$viewSettings->display['button1_text'].'</a>' : '').'
								'.($viewSettings->display['button2_enable'] ? '<a class="btn label-default'.($order->status_id != $viewSettings->display['button2_status'] ? '" href="'.$buttonUrl.$viewSettings->display['button2_status'].'" style="background-color:'.$viewSettings->display['button2_color'].'!important";' : ' btn-light"').'>'.$viewSettings->display['button2_text'].'</a>' : '').'
								'.($viewSettings->display['button3_enable'] ? '<a class="btn label-default'.($order->status_id != $viewSettings->display['button3_status'] ? '" href="'.$buttonUrl.$viewSettings->display['button3_status'].'" style="background-color:'.$viewSettings->display['button3_color'].'!important";' : ' btn-light"').'>'.$viewSettings->display['button3_text'].'</a>' : '').'
							',
							'assign' => $viewSettings->display['assign'] ? Staffs_model::getDropdownOptions() : '',
							'assign_url' => $assignUrl,
							'assigned_to' => $order->assignee ? $order->assignee->staff_id : -1,
							'print' => isset($viewSettings->display['print']) && $viewSettings->display['print'] ? \Thoughtco\Printer\Models\Printer::all()->pluck('label', 'id') : '',
						];
					}
				}

			}

		}

    }

}
