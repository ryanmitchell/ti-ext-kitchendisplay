<?php namespace Thoughtco\KitchenDisplay\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System\Actions\SettingsModel'];

    // A unique code
    public $settingsCode = 'thoughtco_kitchendisplay_settings';

    // Reference to field configuration
    public $settingsFieldsConfig = 'settings';

}
