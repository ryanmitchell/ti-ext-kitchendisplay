<?php namespace Thoughtco\ProcessOrders;

use DB;
use Event;
use Admin\Widgets\Form;
use System\Classes\BaseExtension;
use Thoughtco\Irtouch\Models\Settings;
use Thoughtco\Irtouch\Classes\LocationRequest;
use Thoughtco\Irtouch\Resources\TouchJsonClient;

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
            'Thoughtco.ProcessOrders.View' => [
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
                        'href' => admin_url('thoughtco/processorders/summary'),
                        'title' => lang('lang:thoughtco.processorders::default.text_title'),
                        'permission' => 'Thoughtco.ProcessOrders.View',
                    ],
                ],
            ],
        ];
    } 

}

?>
