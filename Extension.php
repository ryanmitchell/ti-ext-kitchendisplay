<?php namespace Thoughtco\KitchenDisplay;

use AdminAuth;
use Admin\Widgets\Form;
use DB;
use Event;
use System\Classes\BaseExtension;

/**
 * Extension Information File
**/
class Extension extends BaseExtension
{
    public function boot()
    {
		Event::listen('admin.list.extendColumns', function (&$widget) {
			if ($widget->getController() instanceof \Thoughtco\KitchenDisplay\Controllers\Views){
                if (!AdminAuth::user()->hasPermission('Thoughtco.KichenDisplay.Manage')) {
                    $widget->removeColumn('edit');
                }
			}
		});

		Event::listen('admin.toolbar.extendButtons', function (&$widget) {
			if ($widget->getController() instanceof \Thoughtco\KitchenDisplay\Controllers\Views){
                if (!AdminAuth::user()->hasPermission('Thoughtco.KichenDisplay.Manage')) {
                    $widget->getController()->widgets['toolbar']->removeButton('create');
                    $widget->getController()->widgets['toolbar']->removeButton('delete');
                }
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
