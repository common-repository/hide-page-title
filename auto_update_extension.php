<?php
if(!class_exists('georg_auto_update_plugins')) {
	class georg_auto_update_plugins_hide_page_title
	{
		public $current_version;
		
		public $update_path;
		
		public $plugin_slug;
		
		public $slug;
		
		public $plugin_information;
		
		function __construct($current_version, $update_path, $plugin_slug)
		{			
			// Set the class public variables
			$this->current_version = $current_version;
			$this->update_path = $update_path;	
			$this->plugin_slug = $plugin_slug;		
			list ($t1, $t2) = explode('/', $plugin_slug);
			$this->slug = str_replace('.php', '', $t2);	
			// define the alternative API for updating checking
			add_filter('pre_set_site_transient_update_plugins', array(&$this, 'check_update')); 
			// Define the alternative response for information checking
			add_filter('plugins_api', array(&$this, 'check_info'), 10, 3);
		}	 
	
		public function check_update($transient)
		{	 
			// Get the remote version
			$remote_version = $this->getRemote_version();
			// If a newer version is available, add the update
			if (version_compare($this->current_version, $remote_version, '<')) {
				$obj = new stdClass();
				$obj->slug = $this->slug;
				$obj->plugin = $this->plugin_slug;
				$obj->new_version = $remote_version;
				$obj->version = $remote_version;
				$obj->url = $this->getRemote_plugin_url();
				$obj->package = $this->getRemote_plugin_package();
				$transient->response[$this->plugin_slug] = $obj;
			}
			return $transient;			
		}

	 	public function getRemote_plugin_url(){
			$request = wp_remote_post($this->update_path.'&loc_action=cur_url');
			if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
				return $request['body'];
			}
			return false;
		}

		public function getRemote_plugin_package(){
			$request = wp_remote_post($this->update_path.'&loc_action=cur_package');
			if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
				return $request['body'];
			}
			return false;
		}

		public function check_info($false, $action, $arg){
			if ($arg->slug === $this->slug) {		
				$information = $this->getRemote_information();		   
				return $information;				
			}
			return false;
		}	

		public function getRemote_version(){
			$request = wp_remote_post($this->update_path.'&loc_action=version');
			if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
				return $request['body'];
			}			
			return false;
		}	 
		
		public function getRemote_information(){
			$request = wp_remote_post($this->update_path.'&loc_action=info');
			if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
				return unserialize($request['body']);
			}
			return false;
		}
	}
}
?>