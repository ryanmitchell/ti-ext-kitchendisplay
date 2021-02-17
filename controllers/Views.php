<?php

namespace Thoughtco\KitchenDisplay\Controllers;

use AdminAuth;
use AdminMenu;
use Admin\Facades\AdminLocation;
use ApplicationException;
use Template;

class Views extends \Admin\Classes\AdminController
{
    public $implement = [
        'Admin\Actions\FormController',
        'Admin\Actions\ListController',
    ];

    public $listConfig = [
        'list' => [
            'model' => 'Thoughtco\KitchenDisplay\Models\Views',
            'title' => 'lang:thoughtco.kitchendisplay::default.text_title',
            'emptyMessage' => 'lang:thoughtco.kitchendisplay::default.text_empty',
            'defaultSort' => ['id', 'DESC'],
            'configFile' => 'views',
        ],
    ];

    public $formConfig = [
        'name' => 'lang:thoughtco.kitchendisplay::default.text_form_name',
        'model' => 'Thoughtco\KitchenDisplay\Models\Views',
        'create' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'thoughtco/kitchendisplay/views/edit/{id}',
            'redirectClose' => 'thoughtco/kitchendisplay/views',
        ],
        'edit' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'thoughtco/kitchendisplay/views/edit/{id}',
            'redirectClose' => 'thoughtco/kitchendisplay/views',
        ],
        'preview' => [
            'title' => 'lang:admin::lang.form.preview_title',
            'redirect' => 'thoughtco/kitchendisplay/views',
        ],
        'delete' => [
            'redirect' => 'thoughtco/kitchendisplay/views',
        ],
        'configFile' => 'views',
    ];

    protected $requiredPermissions = 'Thoughtco.KitchenDisplay.*';

    public function __construct()
    {
        parent::__construct();

        AdminMenu::setContext('sales', 'summary');
        Template::setTitle(lang('lang:thoughtco.kitchendisplay::default.text_title'));
    }

    public function index()
    {
        $this->asExtension('ListController')->index();
    }

    public function create()
    {
        if (!AdminAuth::user()->hasPermission('Thoughtco.KitchenDisplay.Manage'))
            throw new ApplicationException('Permission denied');

        return parent::create();
    }

    public function edit($a, $b)
    {
        if (!AdminAuth::user()->hasPermission('Thoughtco.KitchenDisplay.Manage'))
            throw new ApplicationException('Permission denied');

        return parent::edit($a, $b);
    }
}
