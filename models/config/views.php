<?php

return [
    'list' => [
        'toolbar' => [
            'buttons' => [
		        'create' => [
		            'label' => 'lang:admin::lang.button_new',
		            'class' => 'btn btn-primary',
		            'href' => 'thoughtco/kitchendisplay/views/create',
		        ],	            
                'delete' => ['label' => 'lang:admin::lang.button_delete', 'class' => 'btn btn-danger', 'data-request-form' => '#list-form', 'data-request' => 'onDelete', 'data-request-data' => "_method:'DELETE'", 'data-request-data' => "_method:'DELETE'", 'data-request-confirm' => 'lang:admin::lang.alert_warning_confirm'],
            ],
        ],
		'filter' => [
			'scopes' => [
				'is_enabled' => [
					'label' => 'lang:admin::lang.text_filter_status',
					'type' => 'switch',
					'conditions' => 'is_enabled = :filtered',
				],
			],	
		],
        'columns' => [
            'edit' => [
                'type' => 'button',
                'iconCssClass' => 'fa fa-pencil',
                'attributes' => [
                    'class' => 'btn btn-edit',
                    'href' => 'thoughtco/kitchendisplay/views/edit/{id}',
                ],
            ],
			'name' => [
                'label' => 'lang:thoughtco.kitchendisplay::default.column_name',
                'type' => 'text',
                'sortable' => TRUE,
            ],						  
			'is_enabled' => [
				'label' => 'lang:thoughtco.kitchendisplay::default.column_status',
				'type' => 'switch',
				'sortable' => FALSE,
			],
			'id' => [
				'label' => '',
				'type' => 'text',
				'sortable' => FALSE,
				'formatter' => function($record, $column, $value){
					return '<a class="btn btn-primary" href="'.admin_url('thoughtco/kitchendisplay/summary/'.$value).'">View</a>';
				}
			],	
        ],
    ],

    'form' => [
        'toolbar' => [
            'buttons' => [
                'back' => ['label' => 'lang:admin::lang.button_icon_back', 'class' => 'btn btn-default', 'href' => 'thoughtco/maxorders/timeslots'],
                'save' => [
                    'label' => 'lang:admin::lang.button_save',
                    'class' => 'btn btn-primary',
                    'data-request' => 'onSave',
                ],
                'saveClose' => [
                    'label' => 'lang:admin::lang.button_save_close',
                    'class' => 'btn btn-default',
                    'data-request' => 'onSave',
                    'data-request-data' => 'close:1',
                ],
            ],
        ],
        'tabs' => [
	        'fields' => [
	            'name' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_settings',
	                'label' => 'lang:thoughtco.kitchendisplay::default.label_name',
	                'type' => 'text',
					'span' => 'left',
	            ],
				'is_enabled' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_settings',
					'label' => 'lang:thoughtco.kitchendisplay::default.label_status',
					'type' => 'switch',
					'span' => 'right',
					'default' => TRUE,
				],
	            'locations' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_settings',
	                'label' => 'lang:thoughtco.kitchendisplay::default.label_locations',
	                'type' => 'selectlist',
					'span' => 'left',
	            ],
	            'order_status' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_settings',
		            'label' => 'thoughtco.kitchendisplay::default.label_order_status',
		            'type' => 'select',
		            'options' => ['Admin\Models\Statuses_model', 'getDropdownOptionsForOrder'],
		            'span' => 'right',
	            ],
		        'order_types' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_settings',
		            'label' => 'lang:thoughtco.kitchendisplay::default.label_ordertypes',
		            'type' => 'radiotoggle',
		            'span' => 'left',
		            'options' => [
		            	'lang:thoughtco.kitchendisplay::default.value_all',
		            	'lang:thoughtco.kitchendisplay::default.value_delivery',
		            	'lang:thoughtco.kitchendisplay::default.value_collection',
		            ],
		        ],	
		        'categories' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_settings',
		            'label' => 'lang:thoughtco.kitchendisplay::default.label_categories',
		            'type' => 'selectlist',
					'span' => 'right',
		        ],
				
	            'display[order_count]' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_display',
	                'label' => 'lang:thoughtco.kitchendisplay::default.label_ordercount',
	                'type' => 'text',
					'span' => 'left',
					'default' => 15,
	            ],		
	            'display[refresh_interval]' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_display',
	                'label' => 'lang:thoughtco.kitchendisplay::default.label_refreshinterval',
	                'type' => 'text',
					'span' => 'right',
					'default' => 30,
	            ],
	            'display[button1_enable]' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_display',
		            'label' => 'thoughtco.kitchendisplay::default.label_button1_status',
		            'type' => 'switch',
					'default' => TRUE,
	            ],
	            'display[button1_text]' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_display',
		            'label' => 'thoughtco.kitchendisplay::default.label_button1_text',
		            'type' => 'text',
					'default' => 'Prep',
		            'span' => 'left',
					'cssClass' => 'flex-width',
		            'trigger' => [
		                'action' => 'show',
		                'field' => 'display[button1_enable]',
		                'condition' => 'checked',
		            ],
	            ],
	            'display[button1_status]' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_display',
		            'label' => 'thoughtco.kitchendisplay::default.label_button1_status',
		            'type' => 'select',
		            'options' => ['Admin\Models\Statuses_model', 'getDropdownOptionsForOrder'],
		            'span' => 'left',
					'cssClass' => 'flex-width',
		            'trigger' => [
		                'action' => 'show',
		                'field' => 'display[button1_enable]',
		                'condition' => 'checked',
		            ],
	            ],
	            'display[button1_color]' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_display',
		            'label' => 'lang:thoughtco.kitchendisplay::default.label_button1_color',
		            'type' => 'colorpicker',
		            'span' => 'left',
					'cssClass' => 'flex-width',					
					'default' => '#1abc9c',
		            'trigger' => [
		                'action' => 'show',
		                'field' => 'display[button1_enable]',
		                'condition' => 'checked',
		            ],
	            ],
	            'display[button2_enable]' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_display',
		            'label' => 'thoughtco.kitchendisplay::default.label_button2_status',
		            'type' => 'switch',
					'default' => TRUE,
	            ],
	            'display[button2_text]' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_display',
		            'label' => 'thoughtco.kitchendisplay::default.label_button2_text',
		            'type' => 'text',
					'default' => 'Ready',
		            'span' => 'left',
					'cssClass' => 'flex-width',
		            'trigger' => [
		                'action' => 'show',
		                'field' => 'display[button2_enable]',
		                'condition' => 'checked',
		            ],
	            ],
	            'display[button2_status]' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_display',
		            'label' => 'thoughtco.kitchendisplay::default.label_button2_status',
		            'type' => 'select',
		            'options' => ['Admin\Models\Statuses_model', 'getDropdownOptionsForOrder'],
		            'span' => 'left',
					'cssClass' => 'flex-width',
					'trigger' => [
		                'action' => 'show',
		                'field' => 'display[button2_enable]',
		                'condition' => 'checked',
		            ],
	            ],
	            'display[button2_color]' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_display',
		            'label' => 'lang:thoughtco.kitchendisplay::default.label_button2_color',
		            'type' => 'colorpicker',
		            'span' => 'left',
					'cssClass' => 'flex-width',
					'default' => '#9b59b6',
		            'trigger' => [
		                'action' => 'show',
		                'field' => 'display[button2_enable]',
		                'condition' => 'checked',
		            ],
	            ],
	            'display[button3_enable]' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_display',
		            'label' => 'thoughtco.kitchendisplay::default.label_button3_status',
		            'type' => 'switch',
					'default' => TRUE,
	            ],
	            'display[button3_text]' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_display',
		            'label' => 'thoughtco.kitchendisplay::default.label_button3_text',
		            'type' => 'text',
					'default' => 'Completed',
		            'span' => 'left',
					'cssClass' => 'flex-width',					
		            'trigger' => [
		                'action' => 'show',
		                'field' => 'display[button3_enable]',
		                'condition' => 'checked',
		            ],
	            ],
	            'display[button3_status]' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_display',
		            'label' => 'thoughtco.kitchendisplay::default.label_button3_status',
		            'type' => 'select',
		            'options' => ['Admin\Models\Statuses_model', 'getDropdownOptionsForOrder'],
		            'span' => 'left',
					'cssClass' => 'flex-width',					
		            'trigger' => [
		                'action' => 'show',
		                'field' => 'display[button3_enable]',
		                'condition' => 'checked',
		            ],
	            ],
	            'display[button3_color]' => [
	                'tab' => 'lang:thoughtco.kitchendisplay::default.tab_display',
		            'label' => 'lang:thoughtco.kitchendisplay::default.label_button3_color',
		            'type' => 'colorpicker',
					'default' => '#f1c40f',
		            'span' => 'left',
					'cssClass' => 'flex-width',
		            'trigger' => [
		                'action' => 'show',
		                'field' => 'display[button3_enable]',
		                'condition' => 'checked',
		            ],
	            ],				
			]
        ]
    ],
];