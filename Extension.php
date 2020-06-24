<?php namespace Thoughtco\Ordersummary;

use DB;
use Event;
use Admin\Widgets\Form;
use System\Classes\BaseExtension;
use Thoughtco\Irtouch\Models\Settings;
use Thoughtco\Irtouch\Classes\LocationRequest;
use Thoughtco\Irtouch\Resources\TouchJsonClient;

/**
 * IRTOUCH Extension Information File
**/
class Extension extends BaseExtension
{
    public function boot()
    {
	    	    	    
    }
    
    public function registerPermissions()
    {
        return [
            'Thoughtco.Ordersummary.View' => [
                'description' => 'View order summary',
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
                        'href' => admin_url('thoughtco/ordersummary/summary'),
                        'title' => lang('lang:thoughtco.ordersummary::default.text_title'),
                        'permission' => 'Thoughtco.Ordersummary.View',
                    ],
                ],
            ],
        ];
    } 

}

?>