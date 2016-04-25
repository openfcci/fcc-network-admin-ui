<?php
/**
 * JSON Feed Template
 *
 * @since 0.16.03.02
 */
$callback = trim(esc_html(get_query_var('callback')));
$charset  = get_option('charset');

//$deletion_ids = get_site_option('deletion_ids');
//$deletion_ids = explode( ',', $deletion_ids );
//update_site_option( 'sites-roles', '1' );
//echo get_site_option( 'sites-roles' );

$json = array();

	//$blogthemeinfo = 'id,name,url,theme,last updated,user count,user id,user email,admin email' . '<br>';
	$blogs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
	if (!empty($blogs)) {
	    foreach ($blogs as $blog) {
	        switch_to_blog($blog);
	        //////////////////////

	        $blog_detail = get_blog_details( $blog, 1 );
					$admin_email = get_option('admin_email');
	        $blogusers = get_users( array( 'blog_id' => $blog ) );
	        $user_count = count_users();

	        //$user_id = $blogusers[0]->ID;
	        //$user_email = $blogusers[0]->user_email;

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
						//'primary-user-id'				=> $primary_user_id,
						'primary-user-role'			=> $primary_user_role,

						//'date'	=> get_the_date('Y-m-d H:i:s','','',false)
						);

	        //////////////////////
	        restore_current_blog();
					$json[] = $site;
	    }
	}

if ( $json ) {

	$json = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

	nocache_headers();
	if (!empty($callback)) {
		header("Content-Type: application/x-javascript; charset={$charset}");
		echo "{$callback}({$json});";
	} else {
		header("Content-Type: application/json; charset={$charset}");
		echo $json;
	}

} else {
	status_header('404');
	wp_die("404 Not Found");
}
