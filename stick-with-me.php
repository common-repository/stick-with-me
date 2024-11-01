<?php

/**
 * Plugin Name:       Stick With Me
 * Description:       Make elements sticky (modified version of Sticky Element plugin by Heckenberg)
 * Version:           1.0.0
 * Author:            Raymund Edgar S. Alvarez
 * Author URI:        https://wordpress.org/support/profile/iamraymund
 * License:           GPL3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

function swm_page_tabs($current = 'first') {
    $tabs = array(
        'first'   => __("Main Menu", 'plugin-textdomain'), 
        'second'  => __("FAQ", 'plugin-textdomain'),
		'third'  => __("Credits", 'plugin-textdomain')
    );
    $html =  '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ){
        $class = ($tab == $current) ? 'nav-tab-active' : '';
        $html .=  '<a class="nav-tab ' . $class . '" href="?page=stick-with-me%2Fstick-with-me.php&tab=' . $tab . '">' . $name . '</a>';
    }
    $html .= '</h2>';
    echo $html;
}
 
function swm_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'swm-activator.php';
	SWM_Activator::activate();
} 

function swm_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'swm-deactivator.php';
	SWM_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'swm_activate' );
register_deactivation_hook( __FILE__, 'swm_deactivate' );

function swm_enqueue_script() {
  wp_enqueue_script('jquery');
} 

function swm_footer() {
  
  $sel = get_option('swm');
  $end = get_option('swm_end');
  $max = get_option('swm_max');
  $z_index = get_option('swm_z_index');
  if ( !isset($sel) || !isset($end) || !isset($max) ) return;
  if ( $sel == '' || $end == '' || $max == '' ) return;

echo <<<EOT
	<script>
	jQuery(document).ready(function($) {

	  var winwidth = $(window).width();

	  $(window).resize(function() {
		  clearTimeout(this.id);
		  this.id = setTimeout(doneResizing, 500);
	  });

	  function doneResizing(){
		if ($(window).width() != winwidth) {
		  window.location.reload();
		}
	  };

	  if ( !$('$sel').length && console) {
		console.log('"$sel" element not found, please check Sticky Element plugin settings');
		return;
	  }
	  if ( !$('$end').length && console) {
		console.log('"$end" element not found, please check Sticky Element plugin settings');
		return;
	  }

	  if (winwidth >= $max) {

		var el = $("$sel");
		var elwidthpx = el.css('width');
		var elleftmar = el.css('margin-left');
		var eltop;
		var elheight;
		var elleft = el.offset().left;
				
		$('<div/>').attr('id', 'clone').css('width',elwidthpx).insertAfter( $('$sel') );

		var style = document.createElement('style');
		style.type = 'text/css';
		style.innerHTML = '.sticky-element-fixed { position: fixed; z-index: $z_index; top: 0; width: ' + elwidthpx + ' !important; }';
		document.getElementsByTagName('head')[0].appendChild(style);

		$(window).scroll(function() {

		  if (typeof eltop === "undefined" ) {
			eltop = el.offset().top;
		  }
		  elheight = el.outerHeight();

		  var end = $("$end");
		  endtop = end.offset().top;
		  var winscroll = $(window).scrollTop();

		  if (winscroll > eltop) {
			$("$sel").addClass('sticky-element-fixed');
			$("$sel").css("left", elleft);
			$("$sel").css("margin-left", 0);
			$('#clone').css('height',elheight);
			$('#clone').css('width',elwidthpx);
		  } else {
			$("$sel").removeClass('sticky-element-fixed');
			$("$sel").css("left", "auto");
			$("$sel").css("margin-left", elleftmar);
			$('#clone').css('height',0);
			$('#clone').css('width',0);
		  }

		  if (winscroll + elheight > endtop) {
			var amount = endtop - (winscroll + elheight);
			$("$sel").css("top", amount + "px");
		  } else {
			var amount = endtop - (winscroll + elheight);
			$("$sel").css("top", "");
		  }

		});
	  }

	});
	</script>
EOT;
}

if (!is_admin()){
	add_action('wp_enqueue_scripts', 'swm_enqueue_script');
	add_action('wp_footer', 'swm_footer', 1);
}
else{
 
	add_action('admin_menu', 'swm_admin_actions');
	function swm_admin_actions() {
		//add_menu_page('Stick With Me Settings', 'Stick With Me', 'administrator', __FILE__, 'swm_admin', plugins_url('/logo.png', __FILE__));
		add_menu_page('Stick With Me Settings', 'Stick With Me', 'administrator', __FILE__, 'swm_admin');
		add_action( 'admin_init', 'swm_register_mysettings' );
	}

	function swm_register_mysettings() {
		register_setting( 'swm-settings-group', 'swm', 'swm_validate_selector' );
		register_setting( 'swm-settings-group', 'swm_end', 'swm_validate_selector' );
		register_setting( 'swm-settings-group', 'swm_max', 'swm_validate_number' );
		register_setting( 'swm-settings-group', 'swm_z_index', 'swm_validate_index' );
	}

	function swm_validate_selector($input) {
	  if ($input == '') return $input;
	  if (!preg_match("/^[#\w\s\.\-\[\]\=\^\~\:]+$/", $input)) return '#invalid-selector';
	  return $input;
	}
	function swm_validate_number($input) {
	  if ($input == '') return $input;
	  if (!is_numeric($input)) return '0';
	  return $input;
	}
	function swm_validate_index($input) {
	  if ($input == '' || !is_numeric($input)) return '0';
	  return $input;
	}
	 
	function swm_admin() {
	
		$tab = (!empty($_GET['tab']))? esc_attr($_GET['tab']) : 'first';
		swm_page_tabs($tab);
		
		if($tab == 'third'){ ?>
			<div class="wrap">
				<h2 style="float: left;">Thank you!</h2>
				<table class="form-table">
					<tr valign="top">
					<th scope="row" style="text-align:right;">Stew Heckenberg</th>
					<td>- for the Sticky Element plugin code</td>
					</tr>
					
					<tr valign="top">
					<th scope="row" style="text-align:right;">@Dexter0015 of stackoverflow.com</th>
					<td>- for the page tabs code</td>
					</tr>
				</table>
			</div>
		<?php
		}
		
		if($tab == 'second'){ ?>
			<div class="wrap">
				<h2 style="float: left;">== Frequently Asked Questions ==</h2>
				<table class="form-table">
					<tr valign="top"  style="padding-bottom: 0px;">
					<td><strong>Q1: What is z-index?<strong></td>
					</tr>
					
					<tr valign="top">
					<td>A1: The z-index property specifies the stack order of an element. The higher the value, the higher the chance your element will appear in top (or front) of all the elements</td>
					</tr>
				</table>
			</div>
			
		<?php
		}
		
		if($tab == 'first' ) {?>
			<div class="wrap">
			<h2 style="float: left;">Sticky Element</h2>
			
			<form method="post" action="options.php">
			<?php settings_fields( 'swm-settings-group' ); ?>
				<table class="form-table">
					<tr valign="top">
					<th scope="row">Element you want to make sticky</th>
					<td><input type="text" name="swm" value="<?php echo get_option('swm'); ?>" placeholder="e.g. #sidebar" required/></td>
					</tr>
					 
					<tr valign="top">
					<th scope="row">Element that pushes sticky upward</th>
					<td><input type="text" name="swm_end" value="<?php echo get_option('swm_end'); ?>" placeholder="e.g. #footer" required/></td>
					</tr>
					
					<tr valign="top">
					<th scope="row">Active when window is wider than</th>
					<td><input type="text" name="swm_max" value="<?php echo get_option('swm_max'); ?>" placeholder="e.g. 960" required/>px</td>
					</tr>
					
					<tr valign="top">
					<th scope="row">Z-index (optional)</th>
					<td><input type="text" name="swm_z_index" value="<?php echo get_option('swm_z_index'); ?>" placeholder="e.g. 20" /></td>
					</tr>
				</table>
				
				<?php submit_button(); ?>
			</form>
			</div>
		<?php
		}
		
	}
}

?>
