<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class FCC_Network_Sites_List_Table extends WP_List_Table {


  	public function get_columns() {
  		// site name, status, username connected under
  		$columns = array(
  			'cb'        => '<input type="checkbox" />',
  			'blogname' => __( 'Site Name', 'jetpack'  ),
  			'blog_path' => __( 'Path', 'jetpack' ),
  			'connected' => __( 'Jetpack Connected', 'jetpack' ),
        'jetpackemail' => __('Jetpack Master User E-mail', 'jetpack'),
        'lastpost' => __( 'Last Post Date', 'jetpack' ),
  		);

  		return $columns;
  	}

  	public function prepare_items() {
  		$jpms = Jetpack_Network::init();

  		// Deal with bulk actions if any were requested by the user
  		$this->process_bulk_action();

  		// Get sites
  		$sites = $jpms->wp_get_sites( array(
  			'exclude_blogs' => array( 1 ),
  			'archived'      => false,
  		) );

      //If has date-filter variable in url, then filter by date, else show all sites.
      if( $_GET['date-filter']){
            // $query = $wpdb->get_results('select * from av_blogs order by blog_id asc', ARRAY_A);
            $date = strtotime($_GET['cat-filter']);
            // Setup pagination
        		$per_page = 25;
        		$current_page = $this->get_pagenum();
        		$total_items = count( $sites );
        		$sites = $wpdb->get_results('select * from av_blogs order by blog_id asc', ARRAY_A);

        		$this->set_pagination_args( array(
        			'total_items' => $total_items,
        			'per_page'    => $per_page
        		) );

        		$columns = $this->get_columns();
        		$hidden = array();
        		$sortable = array();
        		$this->_column_headers = array( $columns, $hidden, $sortable );
        		$this->items = $sites;
        }else{

      		// Setup pagination
      		$per_page = 25;
      		$current_page = $this->get_pagenum();
      		$total_items = count( $sites );
      		$sites = array_slice( $sites, ( ( $current_page-1 ) * $per_page ), $per_page );

      		$this->set_pagination_args( array(
      			'total_items' => $total_items,
      			'per_page'    => $per_page
      		) );

      		$columns = $this->get_columns();
      		$hidden = array();
      		$sortable = array();
      		$this->_column_headers = array( $columns, $hidden, $sortable );
      		$this->items = $sites;
        }


  	}

  	public function column_blogname( $item ) {
  		// http://jpms/wp-admin/network/site-info.php?id=1
  		switch_to_blog( $item->blog_id );
  		$jp_url = admin_url( 'admin.php?page=jetpack' );
  		restore_current_blog();

  		$actions = array(
              		'edit'      	=> '<a href="' . network_admin_url( 'site-info.php?id=' . $item->blog_id )  .  '">' . __( 'Edit', 'jetpack' ) . '</a>',
          		'dashboard'	=> '<a href="' . get_admin_url( $item->blog_id, '', 'admin' ) . '">Dashboard</a>',
  			'view'		=> '<a href="' . get_site_url( $item->blog_id, '', 'admin' ) . '">View</a>',
  			'jetpack-' . $item->blog_id	=> '<a href="' . $jp_url . '">Jetpack</a>',
  		);

    		return sprintf('%1$s %2$s', '<strong>' . get_blog_option( $item->blog_id, 'blogname' ) . '</strong>', $this->row_actions($actions) );
  	}

  	public function column_blog_path( $item ) {
  		return
                           '<a href="' .
                           get_site_url( $item->blog_id, '', 'admin' ) .
                           '">' .
                           str_replace( array( 'http://', 'https://' ), '', get_site_url( $item->blog_id, '', 'admin' ) ) .
                           '</a>';
  	}

  	public function column_connected( $item ) {
  		$jpms = Jetpack_Network::init();
  		$jp = Jetpack::init();

  		switch_to_blog( $item->blog_id );
  		if( $jp->is_active() ) {
  		   // Build url for disconnecting
  		    $url = $jpms->get_url( array(
  			'name'	    => 'subsitedisconnect',
  			'site_id'   => $item->blog_id,

  		    ) );
  		    restore_current_blog();
  		    return '<a href="' . $url . '">Disconnect</a>';
  		}
  		restore_current_blog();

  		// Build URL for connecting
  		$url = $jpms->get_url( array(
  		    'name'	=> 'subsiteregister',
  		    'site_id'	=> $item->blog_id,
  		) );
  		return '<a href="' . $url . '">Connect</a>';
  	}

    public function column_jetpackemail( $item ) {

      switch_to_blog( $item->blog_id );

      $jp_master_user_email = Jetpack::get_connected_user_data( Jetpack_Options::get_option( 'master_user' )->ID )[email];

      restore_current_blog();

      return $jp_master_user_email;
    }

    public function column_lastpost( $item ) {
      // http://jpms/wp-admin/network/site-info.php?id=1
      switch_to_blog( $item->blog_id );
      if(get_lastpostdate('gmt')){
        $last_post = date('F d, Y', strtotime(get_lastpostdate('gmt')));
      }else{
        $last_post = '';
      }
      restore_current_blog();

      return $last_post;
    }

  	public function get_bulk_actions() {
  	    $actions = array(
  		'connect'	=> 'Connect',
  		'disconnect'	=> 'Disconnect'
  	    );

  	    return $actions;
  	}

  	function column_cb($item) {
          	return sprintf(
              		'<input type="checkbox" name="bulk[]" value="%s" />', $item->blog_id
          	);
      	}

  	public function process_bulk_action() {
  		if( !isset( $_POST['bulk'] ) || empty ( $_POST['bulk'] ) )
  			return; // Thou shall not pass! There is nothing to do


  		$jpms = Jetpack_Network::init();

  		$action = $this->current_action();
  		switch ( $action ) {

              		case 'connect':
                  		foreach( $_POST['bulk'] as $k => $site ) {
  							$jpms->do_subsiteregister( $site );
  						}
  				break;
              		case 'disconnect':
                  		foreach( $_POST['bulk'] as $k => $site ) {
  							$jpms->do_subsitedisconnect( $site );
  						}
  				break;
  		}
  	}


    function extra_tablenav( $which ) {
        global $wpdb, $testiURL, $tablename, $tablet;
        $move_on_url = '&date-filter=';
        if ( $which == "top" ){
            ?>
            <div class="alignleft actions bulkactions">
            <?php
            $sites = $wpdb->get_results('select * from av_blogs order by blog_id asc', ARRAY_A);
            if( $sites ){
                ?>
                <select name="date-filter" class="fcc-filter-date">
                    <option value="">Filter by Last Post Date</option>
                      <?php
                      //New Date array
                      $date_array = array();
                      foreach( $sites as $site ){

                        switch_to_blog( $site['blog_id'] );

                        //If blog has a last post date
                        if(get_lastpostdate('gmt')){
                          //Get date by Month Year
                          $last_date = date('F Y', strtotime(get_lastpostdate('gmt')));
                          restore_current_blog();

                          //Add date object to date_array if it does not exist
                          if(!in_array($last_date, $date_array)){
                            array_push($date_array, $last_date);
                          }
                        }
                      }

                      //Sort Dates Function
                      function sortDate( $a, $b ) {
                          return strtotime($b) - strtotime($a);
                      }

                      //Sort the date array
                      usort($date_array, 'sortDate');

                      foreach($date_array as $thedate){
                        if($thedate){
                          $last_post = $thedate;
                        }

                    ?>
                        <option value="<?php echo $move_on_url . $last_post; ?>" <?php echo $selected; ?>><?php echo $last_post; ?></option>
                    <?php

                        }
                    ?>
                </select>
                <?php
            }
            ?>
            </div>
            <?php
        }
        if ( $which == "bottom" ){
            //The code that goes after the table is there

        }
    }

  }

  function dropdown_script(){
    ?>
    <script>
    jQuery(document).ready(function($) {
          $('.fcc-filter-date').live('change', function(){
             var dateFilter = $(this).val();
             if( dateFilter != '' ){
                document.location.href = 'admin.php?page=fcc-network-admin-ui'+dateFilter;
             }
             });
           });
       </script>;
    <?php

  }
  add_action('admin_footer', 'dropdown_script');
