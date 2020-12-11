<?php

namespace Thoughtco\KitchenDisplay\Models;

use Admin\Models\Categories_model;
use Admin\Models\Locations_model;
use ApplicationException;
use Exception;
use Igniter\Flame\Database\Traits\Validation;
use Illuminate\Support\Facades\Log;
use Model;

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
        'display' => 'serialize',
    ];

    public $rules = [
        'locations' => 'sometimes|required',
        'categories' => 'sometimes|required',
        'display.order_count' => 'required|int|min:0',
        'display.refresh_interval' => 'required|int|min:10',
    ];

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
                ];

            break;

            case 3:

                return [
                    0 => lang('lang:thoughtco.kitchendisplay::default.option_blank'),
                    1 => lang('lang:thoughtco.kitchendisplay::default.option_address'),
                ];

            break;

            case 4:

                return [
                    0 => lang('lang:thoughtco.kitchendisplay::default.option_blank'),
                    1 => lang('lang:thoughtco.kitchendisplay::default.option_payment_code'),
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
