<?php

/**
 * Fired during plugin deactivation.
 */
class SWM_Deactivator {

	public static function deactivate() {		
		
		global $wpdb;
        $table = $wpdb->prefix . "StickWithMe";

		$wpdb->query("DROP TABLE IF EXISTS $table");
	}
}
?>