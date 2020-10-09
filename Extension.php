<?php namespace Thoughtco\KitchenDisplay;

use DB;
use Event;
use Admin\Widgets\Form;
use System\Classes\BaseExtension;

/**
 * Extension Information File
**/
class Extension extends BaseExtension
{
    public function boot()
    {
	    	    	    
    }
    
    public function registerPermissions()
    {
        return [
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
                        'href' => admin_url('thoughtco/kitchendisplay/summary'),
                        'title' => lang('lang:thoughtco.kitchendisplay::default.text_title'),
                        'permission' => 'Thoughtco.KitchenDisplay.View',
                    ],
                ],
            ],
        ];
    } 

}

?>
