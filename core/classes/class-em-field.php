<?php

class Em_Field
{
	var $args = array();

	var $html;
	
	var $settings = array();	
	
	function __construct( $args )
	{
		$this->settings = get_option('em_careers_settings');
		$this->args = wp_parse_args($args, array(
			'default_val' => '',
			'name' => '',
			'id' => empty($args['id']) ? str_replace('_', '-', $args['name']) : '',
			'type' => 'text',
			'class' => 'default-text',
		));
		$this->output();
	}
	
	function get_val()
	{
		if ( isset($this->settings[$this->args['name']]) )
			return $this->settings[$this->args['name']];
		elseif ( ! empty($this->args['default_val']) )
			return $this->args['default_val'];
		
		return false;
	}
	
	function output()
	{
		switch ( $this->args['type'] ) {
			case 'page_dropdown' :
				wp_dropdown_pages(array(
					'id' => $this->args['id'],
					'name' => $this->args['name'],
					'show_option_none' => 'Select One',
					'selected' => $this->get_val(),
				));
			break;
			
			case 'textarea' :
				printf(
					'<textarea name="%s" id="%s" class="%s" cols="50" rows="10">%s</textarea>',
					$this->args['name'],
					$this->args['id'],
					$this->args['class'],
					$this->get_val()
				);
			break;
			
			case 'wysiwyg' :
				wp_editor($this->get_val(), $this->args['id'], array(
					'textarea_name' => $this->args['name'],
				));
			break;
			
			default :
				printf(
					'<input name="%s" id="%s" type="%s" class="%s" %s />',
					$this->args['name'],
					$this->args['id'],
					$this->args['type'],
					$this->args['class'],
					$this->get_val() ? sprintf('value="%s"', $this->get_val()) : ''
				);
			break;
		} 
	}
}