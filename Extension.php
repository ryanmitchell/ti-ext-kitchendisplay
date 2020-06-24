<?php namespace Thoughtco\Runningorder;

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
            'Thoughtco.Runningorder.View' => [
                'description' => 'View running order',
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
                        'href' => admin_url('thoughtco/runningorder/summary'),
                        'title' => lang('lang:thoughtco.runningorder::default.text_title'),
                        'permission' => 'Thoughtco.Runningorder.View',
                    ],
                ],
            ],
        ];
    } 

}

?>