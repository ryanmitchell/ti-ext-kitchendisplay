<?php

namespace Thoughtco\KitchenDisplay\Models;

use Admin\Models\Categories_model;
use Admin\Models\Locations_model;
use ApplicationException;
use Exception;
use Igniter\Flame\Database\Traits\Validation;
use Illuminate\Support\Facades\Log;
use Model;
use Request;

class Views extends Model
{
    use Validation;

    /**
     * @var string The database table name
     */
    protected $table = 'thoughtco_kitchendisplay';

    public $timestamps = TRUE;

    public $casts = [
        'locations' => 'array',
        'categories' => 'array',
        'order_status' => 'array',
        'order_types' => 'array',
        'display' => 'serialize',
    ];

    public $rules = [
        'name' => 'required',
        'locations' => 'required',
        'display.order_count' => 'required|int|min:0',
        'display.refresh_interval' => 'required|int|min:10',
        'display.orders_forXhours' => 'required|int|min:0|max:168',
    ];

    public function beforeSave()
    {
        if (!Request::input('View.locations'))
            $this->locations = [];

        if (!Request::input('View.categories'))
            $this->categories = [];

        if (!Request::input('View.order_status'))
            $this->order_status = [];
    }

    public static function getCardLineOptions($line)
    {

        switch ($line)
        {
            case 1:

                return [
                    0 => lang('lang:thoughtco.kitchendisplay::default.option_blank'),
                    1 => lang('lang:thoughtco.kitchendisplay::default.option_name_id'),
                    2 => lang('lang:thoughtco.kitchendisplay::default.option_id_name'),
                ];

            break;

            case 2:

                return [
                    0 => lang('lang:thoughtco.kitchendisplay::default.option_blank'),
                    1 => lang('lang:thoughtco.kitchendisplay::default.option_phone_time_total'),
                    2 => lang('lang:thoughtco.kitchendisplay::default.option_time_phone_total'),
                    3 => lang('lang:thoughtco.kitchendisplay::default.option_phone_time_total_code'),
                    4 => lang('lang:thoughtco.kitchendisplay::default.option_time_phone_total_code'),
                    5 => lang('lang:thoughtco.kitchendisplay::default.option_phone_datetime_total'),
                    6 => lang('lang:thoughtco.kitchendisplay::default.option_datetime_phone_total'),
                    7 => lang('lang:thoughtco.kitchendisplay::default.option_phone_datetime_total_code'),
                    8 => lang('lang:thoughtco.kitchendisplay::default.option_datetime_phone_total_code'),
                ];

            break;

            case 3:

                return [
                    0 => lang('lang:thoughtco.kitchendisplay::default.option_blank'),
                    1 => lang('lang:thoughtco.kitchendisplay::default.option_address'),
                    2 => lang('lang:thoughtco.kitchendisplay::default.option_address_maps_bing'),
                    3 => lang('lang:thoughtco.kitchendisplay::default.option_address_maps'),
                    4 => lang('lang:thoughtco.kitchendisplay::default.option_address_bing'),
                ];

            break;
        }

        return [];
    }

    public static function getCategoriesOptions()
    {
	    return Categories_model::all()->pluck('name', 'category_id');
    }

    public static function getLocationsOptions()
    {
	    $locations = [];
	    foreach (Locations_model::isEnabled()->get() as $location){
	    	$locations[$location->location_id] = $location->location_name;
	    };
	    return collect($locations);
    }
}
