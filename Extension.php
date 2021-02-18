<?php namespace Thoughtco\KitchenDisplay;

use AdminAuth;
use Admin\Facades\AdminLocation;
use Admin\Widgets\Form;
use DB;
use Event;
use System\Classes\BaseExtension;
use Thoughtco\KitchenDisplay\Models\Views as KitchenViews;

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

            if (AdminAuth::isSuperUser())
                return;

            if ($listWidget->getController() instanceof \Thoughtco\KitchenDisplay\Controllers\Views) {

                // build list of views the user is allowed to access by location
		        $viewList = KitchenViews::where(['is_enabled' => true])
                ->get()
				->map(function($view) {
                    if (AdminLocation::getId() === NULL || in_array(AdminLocation::getId(), $view->locations)) {

                        $limitedUsers = array_get($view->display, 'users_limit', []);

                        // if users is blank or in array
						if (!count($limitedUsers) OR in_array(AdminAuth::getId(), $limitedUsers))
			        		return $view->id;

			        }

                    return;
		        })
                ->whereNotNull();

                $query->whereIn('id', $viewList->toArray());
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
