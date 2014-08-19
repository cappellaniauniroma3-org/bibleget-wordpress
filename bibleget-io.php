<?php
/*
Plugin Name: BibleGet IO
Version: 1.0
Plugin URI: http://www.bibleget.de/
Description: Effettua citazioni della Bibbia al volo, con shortcode [bibleget].
Author: John Romano D'Orazio
Author URI: http://johnrdorazio.altervista.org/
Text Domain: bibleget-io
Domain Path: /languages/
License: GPL v3

WordPress BibleGet IO Plugin
Copyright (C) 20014-2020, John Romano D'Orazio - john.dorazio@cappellaniauniroma3.org

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}



function bibleget_shortcode($atts) {
  //$options = get_option('bibleget_settings', array() );
  wp_enqueue_style('biblegetio-styles', plugins_url('css/styles.css', __FILE__), false, '1.0', 'all');
  
  $a = shortcode_atts(array(
    'query' => "",
    'format' => "html"  // default value if none supplied
    ), $atts);

    $ch = curl_init("www.bibleget.de/query/?query=".$a["query"]."&return=".$a["format"]);
    //curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    $output = curl_exec($ch);

    //$response = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
    $start = strpos($output, "<style");
    $end = strpos($output, "</style");

    $output = substr($output,0,$start) . substr($output,$end,strlen($output));

    $start = strpos($output, "<title");
    $end = strpos($output, "</title");

    $output = substr($output,0,$start) . substr($output,$end,strlen($output));

    curl_close($ch);
    return '<div class="bibleget-quote-div">' . $output . '</div>';

}
add_shortcode('bibleget', 'bibleget_shortcode');


require_once(plugin_dir_path( __FILE__ ) . "options.php");

?>
