<?php
/**
 * Fired during plugin activation.
 */
class SWM_Activator {

	public static function activate() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'StickWithMe';
		
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE StickWithMe (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  stickyElement varchar(50) NOT NULL,
			  pushStickyUpward varchar(50) NOT NULL,
			  windowWidth mediumint(9) NOT NULL,
			  zIndex mediumint(9) DEFAULT '0',
			  UNIQUE KEY id (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
					
			$wpdb->insert( 
				$table_name, 
				array( 
					'stickyElement' => 'Sticky', 
					'pushStickyUpward' => 'Sticky-push', 
					'windowWidth' => 10, 
					'zIndex' => 20,
				) 
			);
		}
	}

}
?>