<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class FCC_Network_Sites_List_Table extends WP_List_Table {


  	public function get_columns() {

  		// site name, status, username connected under
  		$columns = array(
  			'blogname' => __( 'Site Name'  ),
  			'blog_path' => __( 'Path' ),
  			'connected' => __( 'Jetpack Connected' ),
        'jetpackemail' => __('Jetpack Master User E-mail'),
        'lastpost' => __( 'Last Post Date' ),
  		);

  		return $columns;
  	}

    public function get_sortable_columns(){
      $sortable_columns = array(
        'lastpost' => array(
          'lastpost', true
        )
  		);

  		return $sortable_columns;
    }

  	public function prepare_items() {
  		$jpms = Jetpack_Network::init();

  		// Deal with bulk actions if any were requested by the user
  		$this->process_bulk_action();


      //If has date-filter variable in url, then filter by date, else show all sites.
      if( $_GET['date-filter']){

        global $wpdb;
        $date = strtotime($_GET['date-filter']);
        //If last post column is sortable, sort the order, else dont
        if($_GET['orderby'] == 'lastpost' && $_GET['order']){
          $sites =  $wpdb->get_results( "SELECT * FROM $wpdb->blogs WHERE YEAR(last_updated) = " . date('Y', $date) . " AND MONTH(last_updated) = " . date('m', $date) ." ORDER BY last_updated " . $_GET['order']);
        }else{
          $sites =  $wpdb->get_results( "SELECT * FROM $wpdb->blogs WHERE YEAR(last_updated) = " . date('Y', $date) . " AND MONTH(last_updated) = " . date('m', $date) );
        }

      }else if( $_GET['activity-filter-active']){
        global $wpdb;

        //Get the amount of days to filter
        $days = $_GET['activity-filter-active'];

        if($_GET['orderby'] == 'lastpost' && $_GET['order']){
          $sites =  $wpdb->get_results( "SELECT * FROM $wpdb->blogs WHERE last_updated BETWEEN DATE_SUB(NOW(), INTERVAL ". $days . " DAY) AND NOW() ORDER BY last_updated " . $_GET['order']);
        }else{
          $sites =  $wpdb->get_results( "SELECT * FROM $wpdb->blogs WHERE last_updated BETWEEN DATE_SUB(NOW(), INTERVAL ". $days . " DAY) AND NOW() ORDER BY last_updated desc");
        }

      }else if( $_GET['activity-filter-inactive']){
        global $wpdb;

        //Get the amount of days to filter before
        $days = $_GET['activity-filter-inactive'];

        if($_GET['orderby'] == 'lastpost' && $_GET['order']){
          $sites =  $wpdb->get_results( "SELECT * FROM $wpdb->blogs WHERE DATE(last_updated) <= DATE_SUB(curdate(), INTERVAL $days DAY) ORDER BY last_updated " . $_GET['order']);
        }else{
          $sites =  $wpdb->get_results( "SELECT * FROM $wpdb->blogs WHERE DATE(last_updated) <= DATE_SUB(curdate(), INTERVAL $days DAY) ORDER BY last_updated desc");
        }

      }else {
        global $wpdb;
        // Get sites
        //If last post column is sortable, sort the order, else dont
        if($_GET['orderby'] == 'lastpost' && $_GET['order']){
          $sites =  $wpdb->get_results( "SELECT * FROM $wpdb->blogs ORDER BY last_updated " . $_GET['order']);
        }else{
          $sites =  $wpdb->get_results( "SELECT * FROM $wpdb->blogs");
        }

        //If Jetpack Filter is Jetpack Connected, disconnected, or email.
        if($_GET['jetpack-filter'] == "jetpack-connected"){
          $jpms = Jetpack_Network::init();
          $jp = Jetpack::init();
          foreach ($sites as $index=>$site){
            switch_to_blog( $site->blog_id );
            if( !$jp->is_active() ) {
                unset($sites[$index]);
                restore_current_blog();
            }else{
              restore_current_blog();
            }
          };
        }else if($_GET['jetpack-filter'] == "jetpack-disconnected"){
          $jpms = Jetpack_Network::init();
          $jp = Jetpack::init();
          foreach ($sites as $index=>$site){
            switch_to_blog( $site->blog_id );
            if( $jp->is_active() ) {
                unset($sites[$index]);
                restore_current_blog();
            }else{
              restore_current_blog();
            }
          };
        } else if( $_GET['jetpack-filter'] == "jetpack-incorrect" ) {
          $jpms = Jetpack_Network::init();
          $jp = Jetpack::init();
          foreach ( $sites as $index => $site ) {
            switch_to_blog( $site->blog_id );

            /* Jetpack: Get Master User Data for Current Blog */
            $master = Jetpack_Options::get_option( 'master_user' );
            if ( ! get_user_by( 'id', $master ) ) {
              unset($sites[$index]);
            } else {
              $master_user = get_userdata( $master );
              $master_user_data_com = Jetpack::get_connected_user_data( $master_user->ID );
              $jp_connected_email = $master_user_data_com['email'];
              if( $jp_connected_email == "fccd-support@forumcomm.com" ) {
                  unset($sites[$index]);
              }
            }

            restore_current_blog();
          }
        }
      }

      //Get items per page option
      $per_page = $this->get_items_per_page('sites_per_page', 250);
      $current_page = $this->get_pagenum();
      $total_items = count( $sites );
      $sites = array_slice( $sites, ( ( $current_page-1 ) * $per_page ), $per_page );
      $this->set_pagination_args( array(
        'total_items' => $total_items,
        'per_page'    => $per_page
      ) );

      $columns = $this->get_columns();
      $sortable = $this->get_sortable_columns();
      $hidden = array();
      $this->_column_headers = $this->get_column_info();

      $this->items = $sites;


  	}

    public function single_row($item){
      if($item->archived && !$item->spam){
        echo '<tr class="site-archived">';
          $this->single_row_columns( $item );
        echo '</tr>';
      }else if($item->spam && $item->archived || $item->spam){
        echo '<tr class="site-spammed">';
          $this->single_row_columns( $item );
        echo '</tr>';
      }else{
        echo '<tr>';
          $this->single_row_columns( $item );
        echo '</tr>';
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
        if($item->archived && !$item->spam){
          return sprintf('%1$s %2$s', '<strong>' . get_blog_option( $item->blog_id, 'blogname' ) . ' - Archived</strong>', $this->row_actions($actions) );
        }else if($item->spam && $item->archived){
          return sprintf('%1$s %2$s', '<strong>' . get_blog_option( $item->blog_id, 'blogname' ) . ' - Archived, Spam</strong>', $this->row_actions($actions) );
        }else if($item->spam){
          return sprintf('%1$s %2$s', '<strong>' . get_blog_option( $item->blog_id, 'blogname' ) . ' - Spam</strong>', $this->row_actions($actions) );
        }
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
  		    restore_current_blog();
  		    return '<p>Connected</p>';
  		}
  		restore_current_blog();
  		return '<p><span style="color:rgb(213, 78, 33)">Disconnected</span></p>';
  	}

    //Get Jetpack Email
    public function column_jetpackemail( $item ) {

      switch_to_blog( $item->blog_id );

      $jp_master_user_email = Jetpack::get_connected_user_data( Jetpack_Options::get_option( 'master_user' )->ID )[email];

      restore_current_blog();

      return $jp_master_user_email;
    }

    public function column_lastpost( $item ) {

      //Set Last Post Date
      set_lastupdated_to_lastpostdate($item->blog_id);

      if( $item->last_updated != '0000-00-00 00:00:00'){
        // Get last post date
        $last_post = date('F d, Y', strtotime($item->last_updated));
      }else{
        $last_post = '';
      }

      return $last_post;
    }




    function extra_tablenav( $which ) {
        global $wpdb, $testiURL, $tablename, $tablet;
        $date_url = '&date-filter=';
        if ( $which == "top" ){
            ?>
            <div class="alignleft actions bulkactions">

              <?php
              $sites = $wpdb->get_results('select * from av_blogs order by last_updated desc', ARRAY_A);
              if( $sites ){
                  ?>
                  <select name="date-filter" class="fcc-filter-date">
                    <?php
                      //If has date-filter variable in url, then filter by date, else show all sites.
                      if( $_GET['date-filter']){
                        ?>
                        <option value=""><?php echo 'Last Posts: ' . $_GET['date-filter']; ?></option>
                      <?php }else{
                        ?>
                        <option value="">Filter by Last Post Date</option>
                      <?php
                       }
                        //New Date array
                        $date_array = array();
                        foreach( $sites as $site ){
                          //If blog is not null
                          if($site['last_updated'] != '0000-00-00 00:00:00'){
                            //Get date by Month Year
                            $last_date = date('F Y', strtotime($site['last_updated']));
                            //Add date object to date_array if it does not exist
                            if(!in_array($last_date, $date_array)){
                              array_push($date_array, $last_date);
                            }
                          }
                        }

                        foreach($date_array as $thedate){
                          if($thedate){
                            $last_post = $thedate;
                          }

                      ?>
                          <option value="<?php echo $date_url . $last_post; ?>" <?php echo $selected; ?>><?php echo $last_post; ?></option>
                      <?php

                          }
                      ?>
                  </select>
                  <?php
              }
              ?>
                  <!-- Jetpack Filter -->
                  <select name="jetpack-filter" class="fcc-filter-jetpack">
                    <?php
                        //Add options specifying what option is selected
                        if($_GET['jetpack-filter'] == 'jetpack-all'){
                          echo "<option>Jetpack: Show All</option>";
                        }else if($_GET['jetpack-filter'] == 'jetpack-connected'){
                          echo "<option>Jetpack: Connected</option>";
                        }else if($_GET['jetpack-filter'] == 'jetpack-disconnected'){
                          echo "<option>Jetpack: Disconnected</option>";
                        }else if($_GET['jetpack-filter'] == 'jetpack-incorrect'){
                          echo "<option>Incorrect Jetpack Master User</option>";
                        }else{
                          echo "<option>Jetpack: Show All</option>";
                        };
                      ?>

                        <option value="&jetpack-filter=jetpack-all">Jetpack: Show All</option>
                        <option value="&jetpack-filter=jetpack-connected">Jetpack Connected</option>
                        <option value="&jetpack-filter=jetpack-disconnected">Jetpack Disconnected</option>
                        <option value="&jetpack-filter=jetpack-incorrect">Incorrect Jetpack Master User</option>
                  </select>

                <!-- Jetpack Filter -->
                <select name="activity-filter" class="fcc-filter-activity">
                  <?php
                      //Add options specifying what option is selected
                      if($_GET['activity-filter-all'] == 'activity-all'){
                        echo "<option>All Activity</option>";
                      }else if($_GET['activity-filter-active'] == '30'){
                        echo "<option>Active: Last 30 days</option>";
                      }else if($_GET['activity-filter-active'] == '60'){
                        echo "<option>Active: Last 60 days</option>";
                      }else if($_GET['activity-filter-active'] == '90'){
                        echo "<option>Active: Last 90 days</option>";
                      }else if($_GET['activity-filter-inactive'] == '30'){
                        echo "<option>Inactive: 30 days</option>";
                      }else if($_GET['activity-filter-inactive'] == '60'){
                        echo "<option>Inactive: 60 days</option>";
                      }else if($_GET['activity-filter-inactive'] == '90'){
                        echo "<option>Inactive: 90 days</option>";
                      }else if($_GET['activity-filter-inactive'] == '180'){
                        echo "<option>Inactive: 6 months</option>";
                      }else if($_GET['activity-filter-inactive'] == '365'){
                        echo "<option>Inactive: 1 year</option>";
                      }else if($_GET['activity-filter-inactive'] == '730'){
                        echo "<option>Inactive: 2 years</option>";
                      }
                    ?>

                      <option value="&activity-filter-all=activity-all">All Activity</option>
                      <option value="&activity-filter-active=30">Active: Last 30 days</option>
                      <option value="&activity-filter-active=60">Active: Last 60 days</option>
                      <option value="&activity-filter-active=90">Active: Last 90 days</option>
                      <option value="&activity-filter-inactive=30">Inactive: 30 days</option>
                      <option value="&activity-filter-inactive=60">Inactive: 60 days</option>
                      <option value="&activity-filter-inactive=90">Inactive: 90 days</option>
                      <option value="&activity-filter-inactive=180">Inactive: 6 months</option>
                      <option value="&activity-filter-inactive=365">Inactive: 1 year</option>
                      <option value="&activity-filter-inactive=730">Inactive: 2 years</option>
                </select>
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
          //Filter Date
          $('.fcc-filter-date').on('change', function(){
             var dateFilter = $(this).val();
             if( dateFilter != '' ){
                document.location.href = 'admin.php?page=fcc-network-admin-ui'+dateFilter;
             }
             });

           //Filter Jetpack
           $('.fcc-filter-jetpack').on('change', function(){
              var jetpackFilter = $(this).val();
              if( jetpackFilter != '' ){
                 document.location.href = 'admin.php?page=fcc-network-admin-ui'+jetpackFilter;
              }
              });

            //Filter Activity
            $('.fcc-filter-activity').on('change', function(){
               var activityFilter = $(this).val();
               if( activityFilter != '' ){
                  document.location.href = 'admin.php?page=fcc-network-admin-ui'+activityFilter;
               }
               });

          });
       </script>;
    <?php

  }
  add_action('admin_footer', 'dropdown_script');

  //Function to set the last updated to the last post date in the sites table
  function set_lastupdated_to_lastpostdate( $wpdb_blogid ) {
  	global $wpdb;

    switch_to_blog( $wpdb_blogid );

    $lastpostdate = get_lastpostdate( 'blog' );
  	$updated_array = array('last_updated' => $lastpostdate );
    //Update last_updated column in blogs table
  	$wpdb->update( $wpdb->blogs, $updated_array, array('blog_id' => $wpdb_blogid) );
    refresh_blog_details($wpdb_blogid);

    restore_current_blog();

  }
