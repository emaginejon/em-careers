<?php

if ( ! function_exists('register_field_group') )
	return;
	
register_field_group(array(
	'id' => '100000000',
	'title' => 'Career Details',
	'fields' => array(
		array(
			'label' => 'Start Date',
			'name' => 'career_start_date',
			'type' => 'date_picker',
			'instructions' => 'The date the career will be published to the website.',
			'required' => 1,
			'date_format' => 'yy-mm-dd',
			'key' => 'field_4ffb00043a497',
			'order_no' => 0,
		),
		array(
			'label' => 'End Date',
			'name' => 'career_end_date',
			'type' => 'date_picker',
			'instructions' => 'The date the career will be unpublished from the website.',
			'required' => 1,
			'date_format' => 'yy-mm-dd',
			'key' => 'field_4ffb00043a709',
			'order_no' => 0,
		),
		array (
			'label' => 'Short Description',
			'name' => 'career_short_description',
			'type' => 'wysiwyg',
			'instructions' => 'Enter the text that will show up on the career listing page.',
			'required' => '1',
			'toolbar' => 'basic',
			'media_upload' => 'no',
			'key' => 'field_4ffb00043a94d',
			'order_no' => '2',
    	),
		array (
			'label' => 'Full Description',
			'name' => 'career_full_description',
			'type' => 'wysiwyg',
			'instructions' => 'Enter the full description of the career.',
			'required' => '1',
			'toolbar' => 'full',
			'media_upload' => 'yes',
			'key' => 'field_4ffb00043ab56',
			'order_no' => '3',
    	),    	
	),
	'location' => array(
		'rules' => array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'career',
				'order_no' => 0
			),
		),
		'allorany' => 'all',
	),
	'options' => array(
		'position' => 'normal',
		'layout' => 'default',
		'hide_on_screen' => array(),
  	),
	'menu_order' => 0,	
));