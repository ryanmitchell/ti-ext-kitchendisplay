<?php

/**
 * Model configuration options for settings model.
 */

$config = [
    'form' => [
        'toolbar' => [
            'buttons' => [
                'save' => ['label' => 'lang:admin::lang.button_save', 'class' => 'btn btn-primary', 'data-request' => 'onSave'],
                'saveClose' => [
                    'label' => 'lang:admin::lang.button_save_close',
                    'class' => 'btn btn-default',
                    'data-request' => 'onSave',
                    'data-request-data' => 'close:1',
                ],
            ],
        ],
        'tabs' => [],
        'fields' => []
    ],
];


$config['form']['tabs'] = [
    'fields' => [
        'prep_status' => [
            'tab' => 'thoughtco.kitchendisplay::default.tab_settings',
            'label' => 'thoughtco.kitchendisplay::default.label_prep_status',
            'type' => 'select',
            'options' => ['Admin\Models\Statuses_model', 'getDropdownOptionsForOrder'],
            'span' => 'left',
        ],
        'prep_color' => [
            'tab' => 'thoughtco.kitchendisplay::default.tab_settings',
            'label' => 'thoughtco.kitchendisplay::default.label_prep_color',
            'type' => 'colorpicker',
            'span' => 'right',
        ],        
        'ready_status' => [
            'tab' => 'thoughtco.kitchendisplay::default.tab_settings',
            'label' => 'thoughtco.kitchendisplay::default.label_ready_status',
            'type' => 'select',
            'options' => ['Admin\Models\Statuses_model', 'getDropdownOptionsForOrder'],
            'span' => 'left',
        ],
        'ready_color' => [
            'tab' => 'thoughtco.kitchendisplay::default.tab_settings',
            'label' => 'thoughtco.kitchendisplay::default.label_ready_color',
            'type' => 'colorpicker',
            'span' => 'right',
        ],
        'completed_status' => [
            'tab' => 'thoughtco.kitchendisplay::default.tab_settings',
            'label' => 'thoughtco.kitchendisplay::default.label_completed_status',
            'type' => 'select',
            'options' => ['Admin\Models\Statuses_model', 'getDropdownOptionsForOrder'],
            'span' => 'left',
        ],
        'completed_color' => [
            'tab' => 'thoughtco.kitchendisplay::default.tab_settings',
            'label' => 'thoughtco.kitchendisplay::default.label_completed_color',
            'type' => 'colorpicker',
            'span' => 'right',
        ], 
		  'show_address' => [
		      'label' => 'thoughtco.kitchendisplay::default.label_show_address',
				'tab' => 'thoughtco.kitchendisplay::default.tab_settings',
				'type' => 'switch',
				'default' => FALSE,
				'comment' => 'thoughtco.kitchendisplay::default.help_show_address',
		  ],		  
    ],
];

return $config;