<?php namespace Thoughtco\KitchenDisplay;

use AdminAuth;
use Admin\Widgets\Form;
use DB;
use Event;
use Illuminate\Support\Arr;
use Admin\Facades\AdminLocation;
use System\Classes\BaseExtension;
use Thoughtco\KitchenDisplay\Models\Views;

/**
 * Extension Information File
**/
class Extension extends BaseExtension
{
    public function boot()
    {
		Event::listen('admin.list.extendColumns', function (&$widget) {
			if ($widget->getController() instanceof \Thoughtco\KitchenDisplay\Controllers\Views){
                if (!AdminAuth::user()->hasPermission('Thoughtco.KitchenDisplay.Manage')) {
                    $widget->removeColumn('edit');
                }
			}
		});

		Event::listen('admin.toolbar.extendButtons', function (&$widget) {
			if ($widget->getController() instanceof \Thoughtco\KitchenDisplay\Controllers\Views){
                if (!AdminAuth::user()->hasPermission('Thoughtco.KitchenDisplay.Manage')) {
                    $widget->getController()->widgets['toolbar']->removeButton('create');
                    $widget->getController()->widgets['toolbar']->removeButton('delete');
                }
			}
		});

        Event::listen('admin.list.extendQuery', function ($listWidget, $query) {
            if ($listWidget->getController() instanceof \Thoughtco\KitchenDisplay\Controllers\Views){
                // build list of views the user is allowed to access by location
		        $viewList = [];
		        Views::where(['is_enabled' => true])
				->each(function($view) use (&$viewList) {
                        //if (AdminLocation::getId() === NULL || AdminLocation::getId() == $view->locations) {
                            $limitedUsers = Arr::get($view->display, 'users_limit', []);
							if (in_array(AdminAuth::getId(), $limitedUsers)) {
				        		$viewList[] = $view->id;
							}
				        //}
		        });
                $query->whereIn('id', $viewList);
			}
        });
    }

    public function registerPermissions()
    {
        return [
            'Thoughtco.KitchenDisplay.Manage' => [
                'description' => 'Manage Kitchen Display views',
                'group' => 'module',
            ],
            'Thoughtco.KitchenDisplay.View' => [
                'description' => 'View orders ready to process',
                'group' => 'module',
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'sales' => [
                'child' => [
                    'summary' => [
                        'priority' => 10,
                        'class' => 'pages',
                        'href' => admin_url('thoughtco/kitchendisplay/views'),
                        'title' => lang('lang:thoughtco.kitchendisplay::default.text_title'),
                        'permission' => 'Thoughtco.KitchenDisplay.View',
                    ],
                ],
            ],
        ];
    }
}

?>
