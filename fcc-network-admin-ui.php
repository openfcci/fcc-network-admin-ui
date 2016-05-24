<?php
/*
Plugin Name: FCC Network Admin UI
Plugin URI: https://github.com/openfcci/fcc-network-admin-ui
Description: A series of modules that adds or extends the functionality, tools and UI of the admin dashboard.
Author: Forum Communications Company
Version: 0.16.05.24
Author URI: http://forumcomm.com/
*/

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/*--------------------------------------------------------------
# PLUGIN ACTIVATION/DEACTIVATION HOOKS
--------------------------------------------------------------*/

/**
 * Plugin Activation Hook
 */
function fcc_network_admin_ui_plugin_activation() {
	// Flush our rewrite rules on activation.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'fcc_network_admin_ui_plugin_activation' );

/**
 * Plugin Deactivation Hook
 */
function fcc_network_admin_ui_plugin_deactivation() {
	// Flush our rewrite rules on deactivation.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'fcc_network_admin_ui_plugin_deactivation' );

/*--------------------------------------------------------------
# LOAD INCLUDES FILES
--------------------------------------------------------------*/


/* Create admin page */

add_action('network_admin_menu', 'fcc_create_network_sites_menu');

#create settings menu
function fcc_create_network_sites_menu(){

	$hook = add_menu_page(
	    'FCC Network Admin UI',
	    'FCC Network Admin UI',
	    'manage_network',
	    'fcc-network-admin-ui',
	    'fcc_network_sites_page',
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBzdGFuZGFsb25lPSJubyI/PjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMjAgMjAiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgc3R5bGU9ImZpbGwtcnVsZTpldmVub2RkO2NsaXAtcnVsZTpldmVub2RkO3N0cm9rZS1saW5lam9pbjpyb3VuZDtzdHJva2UtbWl0ZXJsaW1pdDoxLjQxNDIxOyI+ICAgIDxnIHRyYW5zZm9ybT0ibWF0cml4KDAuNTY4Njk5LDAsMCwwLjU2ODY5OSwwLjA0Nzc3MDcsNC4zMTMwMykiPiAgICAgICAgPHBhdGggZD0iTTE5LjYwNCwxOS45OTRMMTkuNjA0LDAuMDM4TDIyLjYyNywwLjAzOEwyMi42MjcsMTUuMDE1TDIyLjcwOSwxNS4wMzlDMjMuMDgxLDE0LjQwNyAyMy40NTQsMTMuNzc2IDIzLjgyNiwxMy4xNDRDMjYuMzY0LDguODM1IDI4LjksNC41MjYgMzEuNDQyLDAuMjE5QzMxLjQ5NCwwLjEzMiAzMS42MDQsMC4wMjIgMzEuNjg3LDAuMDIxQzMyLjc5NywwLjAwOCAzMy45MDYsMC4wMTIgMzUuMDg0LDAuMDEyQzM0LjgwOCwwLjQ4OSAzNC41NjMsMC45MTYgMzQuMzEyLDEuMzQxQzMxLjA4LDYuODI1IDI3Ljg0NiwxMi4zMDkgMjQuNjEyLDE3Ljc5M0MyNC4yMTgsMTguNDYxIDIzLjgyNywxOS4xMzEgMjMuNDI1LDE5Ljc5NEMyMy4zNzMsMTkuODc5IDIzLjI2MywxOS45ODUgMjMuMTc5LDE5Ljk4NkMyMS45OTksMTkuOTk4IDIwLjgxOSwxOS45OTQgMTkuNjA0LDE5Ljk5NEwxOS42MDQsMTkuOTk0Wk0xMy4yMDIsNC43N0MxMy4xNzksNC43NjYgMTMuMTU2LDQuNzYyIDEzLjEzMyw0Ljc1OEMxMS41NTIsNy4yNTMgOS45NzIsOS43NDcgOC4zNzQsMTIuMjY4TDEzLjIwMiwxMi4yNjhMMTMuMjAyLDQuNzdMMTMuMjAyLDQuNzdaTTEzLjIzLDE1LjMzM0MxMC45NzYsMTUuMzMzIDguNzk4LDE1LjMyOSA2LjYyMSwxNS4zNDVDNi41MDIsMTUuMzQ2IDYuMzQ3LDE1LjQ5MyA2LjI3MSwxNS42MTFDNS4zOTYsMTYuOTc2IDQuNTI5LDE4LjM0NSAzLjY3MSwxOS43MkMzLjU0NywxOS45MTggMy40MTksMjAuMDAzIDMuMTc4LDIwQzIuMTE0LDE5Ljk4NiAxLjA0OSwxOS45OTQgLTAuMDg0LDE5Ljk5NEMtMC4wMDQsMTkuODU0IDAuMDQ2LDE5Ljc1OSAwLjEwMywxOS42NjhDMy4xOTYsMTQuNzc1IDYuMjksOS44ODMgOS4zODQsNC45OUMxMC4zNjgsMy40MzQgMTEuMzU4LDEuODgxIDEyLjMzLDAuMzE4QzEyLjQ3OCwwLjA4MSAxMi42MzcsLTAuMDA0IDEyLjkxMiwwQzEzLjkwOCwwLjAxNiAxNC45MDUsMC4wMDcgMTUuOTAxLDAuMDA4QzE2LjAxNCwwLjAwOCAxNi4xMjcsMC4wMTggMTYuMjU2LDAuMDI0TDE2LjI1NiwxOS45N0wxMy4yMywxOS45N0wxMy4yMywxNS4zMzNMMTMuMjMsMTUuMzMzWiIgc3R5bGU9ImZpbGw6d2hpdGU7Ii8+ICAgIDwvZz48L3N2Zz4=', // Icon Url
	 6
	  );
		add_action( "load-{$hook}", 'site_table_load_hook' );

};

//Adds Screen Options
function site_table_load_hook() {
	global $fcc_network_table;
	if ( get_current_screen() ) {

		//Add Pagination option
		$option = 'per_page';
		$args = array(
				'label' => 'Number of items per page:',
				'default' => 50,
				'option' => 'sites_per_page'
		);
		add_screen_option( $option, $args );

		//If Jetpack is installed
		if(is_plugin_active('jetpack/jetpack.php')){
			require_once( 'includes/fcc-network-table.php' );
			//WP_List_Table automatically adds options
			$fcc_network_table = new FCC_Network_Sites_List_Table();
		}

	}

}

//Sets screen options
add_filter('set-screen-option', 'fcc_network_table_set_option', 10, 3);
function fcc_network_table_set_option($status, $option, $value) {
  return $value;
}

//Set up page
function fcc_network_sites_page(){

   if (is_multisite() && current_user_can('manage_network'))  {
		 //If Jetpack isn't installed, display a message.
		 if(is_plugin_active('jetpack/jetpack.php')){

			 require_once( 'includes/fcc-network-table.php' );
			 $myListTable = new FCC_Network_Sites_List_Table();

			 echo '<div class="wrap"><h2>' . __( 'Sites', 'jetpack' ) . '</h2>';
			 echo '<form method="post">';
			 $myListTable->prepare_items();
			 $myListTable->display();
			 echo '</form></div>';

			 echo'<div class="wrap"><div class=""><div class="inside">';
			 sites_json_create_form();
			 echo '</div></div></div>';

		 }else{
			 echo '<p>Jetpack must be installed and activated to use this plugin.';
		 }
   }
}

/*--------------------------------------------------------------
# JSON FEED
--------------------------------------------------------------*/

/**
 * Add 'sites' JSON Feed
 *
 * @since 1.16.04.24
 * @version 1.16.04.24
 */
function fcc_sites_do_json_feed(){
	add_feed('sites', 'add_sites_feed');
}
add_action('init', 'fcc_sites_do_json_feed');

/**
 * Load JSON Feed Template
 *
 * @since 1.16.04.24
 * @version 1.16.04.24
 */
function add_sites_feed(){
	load_template( plugin_dir_path( __FILE__ ) . 'template/feed-json.php' );
}


/*************************** FORM: Update Sites JSON **************************
*******************************************************************************
* Create form function.
*/

function sites_json_create_form(){

?>
<div class="wrap">
<?php
	switch ( $_GET['action'] ) {
		//---------------------------------------------------//
			default:
?>
		<div class="wrap">
			<form method="post" action="<?php echo network_admin_url(); ?>admin.php?page=fcc-network-admin-ui&action=sitesjson">
				<p class="submit">
					<input type="submit" class="button-primary" name="Submit" value="<?php _e( 'Refresh Sites JSON Feed' ) ?>" />
				</p>
			</form>
		</div>

		<?php
			break;
		//---------------------------------------------------//
			case "sitesjson":
				cache_site_json();
				echo 'Sites JSON Feed Updated';
				break;
	}
?>
</div>
<?php
}


function cache_site_json() {
	/*** Refresh the Cache ***/
	global $wpdb;
	$json = array();

		$blogs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		if (!empty($blogs)) {
		    foreach ($blogs as $blog) {
		        switch_to_blog($blog);
		        //////////////////////

		        $blog_detail = get_blog_details( $blog, 1 );
						$admin_email = get_option('admin_email');
		        $blogusers = get_users( array( 'blog_id' => $blog ) );
		        $user_count = count_users();

						if (!empty($blogusers)) {
					    foreach ($blogusers as $user) {
							if ($user->roles[0] == 'site-owner' && $user->data->user_email == $admin_email ) {
								$primary_user_role = 'Site Owner';
								$primary_user = $user->data->user_email;
								$primary_user_id = $user->ID;
								break;

							} elseif ($user->roles[0] == 'administrator' && $user->data->user_email == $admin_email ) {
								$primary_user_role = 'Administrator';
								$primary_user = $user->data->user_email;
								$primary_user_id = $user->ID;
								break;
							}
							else {
								$primary_user_role = 'no match';
								$primary_user = 'no match';
								$primary_user_id = 'no match';
							}
						}
					}

					if ( get_site_option( 'sites-roles' ) ) {
						$roles = $user_count[avail_roles];
					} else {
						$roles = '';
					}

						$site = array(
							'id'							=> $blog,
							'name'						=> $blog_detail->blogname,
							'url'							=> $blog_detail->siteurl,
							'theme'						=> wp_get_theme()->get( 'Name' ),
							'registered'			=> date('Y-m-d', strtotime($blog_detail->registered)),
							'last-post-date'	=> date('Y-m-d', strtotime(get_lastpostdate())),
							'total_users'			=> $user_count[total_users],
							'roles' 					=> $roles,
							'admin_email'			=> $admin_email,
							'primary-user-email'			=> $primary_user,
							'primary-user-role'			=> $primary_user_role,
							);

		        //////////////////////
		        restore_current_blog();
						$json[] = $site;
		    }
		}

		update_site_option( 'sites-json', $json );
}
