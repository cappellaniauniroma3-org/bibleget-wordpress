<?php
/*
Plugin Name: BibleGet I/O
Version: 2.0
Plugin URI: http://www.bibleget.io/
Description: Easily insert Bible quotes from a choice of Bible versions into your articles or pages with the shortcode [bibleget].
Author: John Romano D'Orazio
Author URI: http://www.cappellaniauniroma3.org/
Text Domain: bibleget-io
Domain Path: /languages/
License: GPL v3

WordPress BibleGet I/O Plugin
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

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

function BibleGet_on_activation()
{
	if ( ! current_user_can( 'activate_plugins' ) )
		return;
	$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
	check_admin_referer( "activate-plugin_{$plugin}" );

	# Uncomment the following line to see the function in action
	# exit( var_dump( $_GET ) );
	SetOptions();
	
}

function BibleGet_on_deactivation()
{
	if ( ! current_user_can( 'activate_plugins' ) )
		return;
	$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
	check_admin_referer( "deactivate-plugin_{$plugin}" );

	# Uncomment the following line to see the function in action
	# exit( var_dump( $_GET ) );
	DeleteOptions();
}

function BibleGet_on_uninstall()
{
	if ( ! current_user_can( 'activate_plugins' ) )
		return;
	check_admin_referer( 'bulk-plugins' );

	// Important: Check if the file is the one
	// that was registered during the uninstall hook.
	if ( __FILE__ != WP_UNINSTALL_PLUGIN )
		return;

	# Uncomment the following line to see the function in action
	# exit( var_dump( $_GET ) );
	DeleteOptions();
	delete_option('bibleget_settings');
}

register_activation_hook(   __FILE__, 'BibleGet_on_activation' );
register_deactivation_hook( __FILE__, 'BibleGet_on_deactivation' );
register_uninstall_hook(    __FILE__, 'BibleGet_on_uninstall' );




/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
*/
function bibleget_load_textdomain() {
    $domain = 'bibleget-io';
    // The "plugin_locale" filter is also used in load_plugin_textdomain()
    $locale = apply_filters('plugin_locale', get_locale(), $domain);
    // Allow users to add their own custom translations by dropping them in the Wordpress 'languages' directory
    load_textdomain($domain, WP_LANG_DIR.'/plugins/'.$domain.'-'.$locale.'.mo');
    
    load_plugin_textdomain( $domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
//should the action be 'init' instead of 'plugins_loaded'? see http://geertdedeckere.be/article/loading-wordpress-language-files-the-right-way
add_action( 'plugins_loaded', 'bibleget_load_textdomain' ); 





// [bibleget query="Matthew1:1-5" version="CEI2008"]
// [bibleget query="Matthew1:1-5" versions="CEI2008,NVBSE"]

function bibleget_shortcode($atts, $content = null) {
  wp_enqueue_style('biblegetio-styles', plugins_url('css/styles.css', __FILE__), false, '1.0', 'all');
  
  $a = shortcode_atts(array(
    'query' => "Matthew1:1-5",
    'version' => "",
  	'versions' => ""), $atts);
	
   	//echo "<div style=\"border:10px solid Blue;\">".$a["query"]."</div>";
  
  	// Determine bible version(s)
  	if($a["versions"] !== ""){
  		$a["version"] = $a["versions"];
  	}
  	else if($a["version"] === ""){ $a["version"] = "NVBSE"; }
  	$versions = explode(",",$a["version"]);
  	if(count($versions)<1){
  		$options = get_option( 'bibleget_settings', array() );
  		$versions = isset($options["favorite_version"]) ? explode(",",$options["favorite_version"]) : array();
  	} 	
  	if(count($versions)<1){
    	/* translators: do NOT translate the parameter names \"version\" or \"versions\" !!! */
  		$output = '<span style="color:Red;font-weight:bold;">'.__('You must indicate the desired version with the parameter "version" (or the desired versions as a comma separated list with the parameter "versions")',"bibleget-io").'</span>';
       	return '<div class="bibleget-quote-div">' . $output . '</div>';
   	}

   	$vversions = get_option("bibleget_versions",array());
   	if(count($vversions)<1){
   		SetOptions();
   		$vversions = get_option("bibleget_versions",array());
   	}
   	$validversions = array_keys($vversions);
   	//echo "<div style=\"border:10px solid Blue;\">".print_r($validversions)."</div>";
   	
   	foreach($versions as $version){
   		if(!in_array($version,$validversions)){
			$optionsurl = admin_url("options-general.php?page=bibleget-settings-admin");
   			/* translators: you must not change the placeholders \"%s\" or the html <a href=\"%s\">, </a>  */
			$output = '<span style="color:Red;font-weight:bold;">'.sprintf(__('The requested version "%s" is not valid, please check the list of valid versions in the <a href="%s">settings page</a>',"bibleget-io"),$version,$optionsurl).'</span>';
   			return '<div class="bibleget-quote-div">' . $output . '</div>';
   		}
   	}
     
    $queries = queryClean($a["query"]);
	if (is_array ( $queries )) {
		$goodqueries = processQueries ( $queries, $versions );
		// write_log("value of goodqueries after processQueries:");
		// write_log($goodqueries);
		if ($goodqueries === false) {
			$output = "goodqueries returned false";
			return '<div class="bibleget-quote-div">' . $output . '</div>';
		}
		$finalquery = "query=";
		$finalquery .= implode ( ";", $goodqueries );
		$finalquery .= "&version=";
		$finalquery .= implode ( ",", $versions );
		// write_log("value of finalquery = ".$finalquery);
		if ($finalquery != "") {
			// $output = $finalquery;
			// return '<div class="bibleget-quote-div">' . $output . '</div>';
			$output = queryServer ( $finalquery );
			return '<div class="bibleget-quote-div">' . $output . '</div>';
		}
	} else {
		/* translators: do not translate "shortcode" unless the version of wordpress in your language uses a translated term to refer to shortcodes */
		$output = '<span style="color:Red;font-weight:bold;">' . __( "There are errors in the shortcode, please check carefully your query syntax:", "bibleget-io" ) . '&lt;'.$a['query'].'&gt;<br />' . $queries . '</span>';
		return '<div class="bibleget-quote-div">' . $output . '</div>';
	}
    
}
add_shortcode('bibleget', 'bibleget_shortcode');

function queryServer($finalquery){
	
	$ch = curl_init("query.bibleget.io/index2.php?".$finalquery."&return=html&appid=wordpress");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    	
   	if( ini_get('safe_mode') || ini_get('open_basedir') ){
   		// safe mode is on, we can't use some settings
   	}else{
   		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
   		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
   	}
   	$output = curl_exec($ch);
   	if($output && !curl_errno($ch)){
   		// remove style and title tags from the output
   		$output = substr($output,0,strpos($output, "<style")) . substr($output,strpos($output, "</style"),strlen($output));
   		$output = substr($output,0,strpos($output, "<title")) . substr($output,strpos($output, "</title"),strlen($output));
   	}
   	else{
   		$output = '<span style="color:Red;font-weight:bold;">'.__("There was an error communicating with the BibleGet server, please wait a few minutes and try again: ","bibleget-io").' &apos;'.curl_error($ch).'&apos;: '.$finalquery.'</span>';
   	}
   	curl_close($ch);    	 
   
	return $output;
}

function processQueries($queries,$versions){
	$goodqueries = array();
		
	$thisbook = null;
	if(get_option("bibleget_".$versions[0]."IDX")===false){
		SetOptions();
	}
	$indexes = array();
	foreach($versions as $key => $value){
		if($temp = get_option("bibleget_".$value."IDX")){
  		//write_log("retrieving option ["."bibleget_".$value."IDX"."] from wordpress options...");
      //write_log($temp);
      if(is_object($temp)){
        //write_log("temp variable is an object, now converting to an array with key '".$value."'...");
        $indexes[$value] = json_decode(json_encode($temp),true);
        //write_log($indexes[$value]);
      }
      elseif(is_array($temp)){
        //write_log("temp variable is an array, hurray!");
        $indexes[$value] = $temp;
        //write_log($indexes[$value]);
      }
      else{
        //write_log("temp variable is neither an object or an array. What the heck is it?");
        //write_log($temp);
      }
    }
    else{
      //write_log("option ["."bibleget_".$value."IDX"."] does not exist. Now attempting to set options...");
      SetOptions();
      if($temp = get_option("bibleget_".$value."IDX")){
    		//write_log("retrieving option ["."bibleget_".$value."IDX"."] from wordpress options...");
        	//write_log($temp);
        	//$temp1 = json_encode($temp);
    		$indexes[$value] = json_decode($temp,true);    
      }
      else{
        //write_log("Could not either set or get option ["."bibleget_".$value."IDX"."]");
      }
    }
	}
	//write_log("indexes array should now be populated:");
  	//write_log($indexes);
  
	$notices= get_option('bibleget_error_admin_notices', array());
	
	foreach($queries as $key => $value){
		$thisquery = toProperCase($value); //shouldn't be necessary because already array_mapped, but better safe than sorry
		if($key===0){
			if(!preg_match("/^[1-3]{0,1}((\p{L}\p{M}*)+)/",$thisquery)){
				/* translators: do not change the placeholders <%s> */
				$notices[] = sprintf(__("The first query <%s> in the querystring <%s> must start with a valid book indicator!","bibleget-io"),$thisquery,implode(";",$queries));
				continue;
			}
		}
		$thisbook = checkQuery($thisquery,$indexes,$thisbook);
		//write_log("value of thisbook after checkQuery = ".$thisbook);
		if($thisbook !== false){
			array_push($goodqueries,$thisquery);
		}
		else{
			return $thisbook;
		}
	}
	update_option('bibleget_error_admin_notices',$notices);
	return $goodqueries;
	
}

function checkQuery($thisquery,$indexes,$thisbook=""){
	//write_log("value of thisquery = ".$thisquery);
	$errorMessages = array();
	/* translators: 'commas', 'dots', and 'dashes' refer to the bible citation notation; in some notations (such as english notation) colons are used instead of commas, and commas are used instead of dots */
	$errorMessages[0] = __("There cannot be more commas than there are dots.","bibleget-io");
	$errorMessages[1] = __("You must have a valid chapter following the book indicator!","bibleget-io");
	$errorMessages[2] = __("The book indicator is not valid. Please check the documentation for a list of valid book indicators.","bibleget-io");
	/* translators: 'commas', 'dots', and 'dashes' refer to the bible citation notation; in some notations (such as english notation) colons are used instead of commas, and commas are used instead of dots */
	$errorMessages[3] = __("You cannot use a dot without first using a comma. A dot is a liason between verses, which are separated from the chapter by a comma.","bibleget-io");
	/* translators: 'commas', 'dots', and 'dashes' refer to the bible citation notation; in some notations (such as english notation) colons are used instead of commas, and commas are used instead of dots */
	$errorMessages[4] = __("A dot must be preceded and followed by 1 to 3 digits of which the first digit cannot be zero.","bibleget-io");
	/* translators: 'commas', 'dots', and 'dashes' refer to the bible citation notation; in some notations (such as english notation) colons are used instead of commas, and commas are used instead of dots */
	$errorMessages[5] = __("A comma must be preceded and followed by 1 to 3 digits of which the first digit cannot be zero.","bibleget-io");
	$errorMessages[6] = __("A dash must be preceded and followed by 1 to 3 digits of which the first digit cannot be zero.","bibleget-io");
	$errorMessages[7] = __("If there is a chapter-verse construct following a dash, there must also be a chapter-verse construct preceding the same dash.","bibleget-io");
	/* translators: 'commas', 'dots', and 'dashes' refer to the bible citation notation; in some notations (such as english notation) colons are used instead of commas, and commas are used instead of dots */
	$errorMessages[8] = __("There are multiple dashes in the query, but there are not enough dots. There can only be one more dash than dots.","bibleget-io");
	/* translators: the expressions %1$d, %2$d, and %3$s must be left as is, they will be substituted dynamically by values in the script. See http://php.net/sprintf. */
	$errorMessages[9] = __('The values concatenated by the dot must be consecutive, instead %1$d >= %2$d in the expression <%3$s>',"bibleget-io");
	$errorMessages[10] = __("A query that doesn't start with a book indicator must however start with a valid chapter indicator!","bibleget-io");
	
	$errs= get_option('bibleget_error_admin_notices', array());
	$dummy = array(); // to avoid error messages on systems with PHP < 5.4 which required third parameter in preg_match_all
	
	if(preg_match("/^([1-3]{0,1}((\p{L}\p{M}*)+))/",$thisquery,$res)){
		$thisbook = $res[0];
		if(!preg_match("/^[1-3]{0,1}((\p{L}\p{M}*)+)[1-9][0-9]{0,2}/",$thisquery) || preg_match_all("/^[1-3]{0,1}((\p{L}\p{M}*)+)/",$thisquery) != preg_match_all("/^[1-3]{0,1}((\p{L}\p{M}*)+)[1-9][0-9]{0,2}/",$thisquery)){
			$errs[] = $errorMessages[1];			
			update_option('bibleget_error_admin_notices',$errs);
			return false;	
		}		
		
		$validBookIndex = (int) isValidBook($thisbook);
		if($validBookIndex != -1){
			$thisquery = str_replace($thisbook,"",$thisquery);	
		
			if(strpos($thisquery,".")){
				if(!strpos($thisquery,",") || strpos($thisquery,",") > strpos($thisquery,".")){
					// error message: You cannot use a dot without first using a comma. A dot is a liason between verses, which are separated from the chapter by a comma.
					$errs[] = "ERROR in query <".$thisquery.">: " . $errorMessages[3];
					update_option('bibleget_error_admin_notices',$errs);	
					return false;
				}
				if(substr_count($thisquery,",") > substr_count($thisquery,".")){
					$errs[] = "ERROR in query <".$thisquery.">: " . $errorMessages[0];
					update_option('bibleget_error_admin_notices',$errs);	
					return false;
				}
				
				//if(preg_match_all("/(?=[1-9][0-9]{0,2}\.[1-9][0-9]{0,2})/",$query) != substr_count($query,".") ){
				//if(preg_match_all("/(?=([1-9][0-9]{0,2}\.[1-9][0-9]{0,2}))/",$query) < substr_count($query,".") ){
				if(preg_match_all("/(?<![0-9])(?=([1-9][0-9]{0,2}\.[1-9][0-9]{0,2}))/",$thisquery,$dummy) != substr_count($thisquery,".") ){
					// error message: A dot must be preceded and followed by 1 to 3 digits etc.
					$errs[] = "ERROR in query <".$thisquery.">: " . $errorMessages[4];
					update_option('bibleget_error_admin_notices',$errs);	
					return false;
				}
				if(preg_match_all("/(?<![0-9])(?=([1-9][0-9]{0,2}\.[1-9][0-9]{0,2}))/",$thisquery,$dummy)){
					foreach($dummy[1] as $match){
						$ints = explode('.',$match);
						if(intval($ints[0]) >= intval($ints[1]) ){
							$str = sprintf($errorMessages[9],$ints[0],$ints[1],$match);
							$errs[] = "ERROR in query <".$thisquery.">: ".$str;
							update_option('bibleget_error_admin_notices',$errs);
							return false;
						}
					}
				}
			}
			if(strpos($thisquery,",")){
				if(preg_match_all("/[1-9][0-9]{0,2}\,[1-9][0-9]{0,2}/",$thisquery,$dummy) != substr_count($thisquery,",")){
					// error message: A comma must be preceded and followed by 1 to 3 digits etc.
					//echo "There are ".preg_match_all("/(?=[1-9][0-9]{0,2}\,[1-9][0-9]{0,2})/",$query)." matches for commas preceded and followed by valid 1-3 digit sequences;<br>";
					//echo "There are ".substr_count($query,",")." matches for commas in this query.";
					$errs[] = "ERROR in query <".$thisquery.">: " . $errorMessages[5];
					update_option('bibleget_error_admin_notices',$errs);	
					return false;
				}
				else{
					if(preg_match_all("/([1-9][0-9]{0,2})\,/",$thisquery,$matches) ){
						if(!is_array($matches[1])){
							$matches[1] = array($matches[1]);
						}
						$myidx = $validBookIndex+1;
						//write_log("myidx = ".$myidx);
            foreach($matches[1] as $match){
				foreach($indexes as $jkey => $jindex){
                //write_log("jindex array contains:");
                //write_log($jindex);
				$bookidx = array_search($myidx,$jindex["book_num"]);
				//write_log("bookidx for ".$jkey." = ".$bookidx);
                $chapter_limit = $jindex["chapter_limit"][$bookidx];
				//write_log("chapter_limit for ".$jkey." = ".$chapter_limit);
                write_log("match for ".$jkey." = ".$match);
                if($match>$chapter_limit){
					/* translators: the expressions <%1$d>, <%2$s>, <%3$s>, and <%4$d> must be left as is, they will be substituted dynamically by values in the script. See http://php.net/sprintf. */
					$msg = __('A chapter in the query is out of bounds: there is no chapter <%1$d> in the book <%2$s> in the requested version <%3$s>, the last possible chapter is <%4$d>',"bibleget-io");
					$errs[] = sprintf($msg,$match,$thisbook,$jkey,$chapter_limit);
					update_option('bibleget_error_admin_notices',$errs);	
					return false;
				}
			}
		}
						
		$commacount = substr_count($thisquery,",");
		//write_log("commacount = ".$commacount);
        if($commacount>1){
							if(!strpos($thisquery,'-')){
								/* translators: 'commas', 'dots', and 'dashes' refer to the bible citation notation; in some notations (such as english notation) colons are used instead of commas, and commas are used instead of dots */
								$errs[] = __("You cannot have more than one comma and not have a dash!","bibleget-io")." <".$thisquery.">";
								update_option('bibleget_error_admin_notices',$errs);	
								return false;
							}
							$parts = explode("-",$thisquery);
							if(count($parts)!=2){
								$errs[] = __("You seem to have a malformed querystring, there should be only one dash.","bibleget-io")." <".$thisquery.">";
								update_option('bibleget_error_admin_notices',$errs);	
								return false;
							}
							foreach($parts as $part){
								$pp = array_map("intval",explode(",",$part));
								foreach($indexes as $jkey => $jindex){
									$bookidx = array_search($myidx,$jindex["book_num"]); 
									$chapters_verselimit = $jindex["verse_limit"][$bookidx];
									$verselimit = intval($chapters_verselimit[$pp[0]-1]);
									if($pp[1]>$verselimit){
										/* translators: the expressions <%1$d>, <%2$s>, <%3$d>, <%4$s> and %5$d must be left as is, they will be substituted dynamically by values in the script. See http://php.net/sprintf. */
										$msg = __('A verse in the query is out of bounds: there is no verse <%1$d> in the book <%2$s> at chapter <%3$d> in the requested version <%4$s>, the last possible verse is <%5$d>',"bibleget-io");
										$errs[] = sprintf($msg,$pp[1],$thisbook,$pp[0],$jkey,$verselimit);
										update_option('bibleget_error_admin_notices',$errs);	
										return false;
									}
								}
							}
						}
						elseif($commacount==1){
							//write_log("commacount has been detected as 1, now exploding on comma the query [".$thisquery."]");
              				$parts = explode(",",$thisquery);
							//write_log($parts);
             				// write_log("checking for presence of dashes in the right-side of the comma...");
              				if(strpos($parts[1],'-')){
								//write_log("a dash has been detected in the right-side of the comma (".$parts[1].")");
                				if(preg_match_all("/[,\.][1-9][0-9]{0,2}\-([1-9][0-9]{0,2})/",$thisquery,$matches)){
									if(!is_array($matches[1])){
										$matches[1] = array($matches[1]);
									}
									$highverse = intval(array_pop($matches[1]));
									//write_log("highverse = ".$highverse);
                  					foreach($indexes as $jkey => $jindex){
										$bookidx = array_search($myidx,$jindex["book_num"]);
										$chapters_verselimit = $jindex["verse_limit"][$bookidx];
										$verselimit = intval($chapters_verselimit[intval($parts[0])-1]);
										//write_log("verselimit for ".$jkey." = ".$verselimit);
                    					if($highverse>$verselimit){
											/* translators: the expressions <%1$d>, <%2$s>, <%3$d>, <%4$s> and %5$d must be left as is, they will be substituted dynamically by values in the script. See http://php.net/sprintf. */
											$msg = __('A verse in the query is out of bounds: there is no verse <%1$d> in the book <%2$s> at chapter <%3$d> in the requested version <%4$s>, the last possible verse is <%5$d>',"bibleget-io");
											$errs[] = sprintf($msg,$highverse,$thisbook,$parts[0],$jkey,$verselimit);
											update_option('bibleget_error_admin_notices',$errs);	
											return false;
										}
									}
								}
                else{
                  //write_log("something is up with the regex check...");
                }
							}
							else{
								if(preg_match("/,([1-9][0-9]{0,2})/",$thisquery,$matches)){
									$highverse = intval($matches[1]);
									foreach($indexes as $jkey => $jindex){
										$bookidx = array_search($myidx,$jindex["book_num"]);
										$chapters_verselimit = $jindex["verse_limit"][$bookidx];
										$verselimit = intval($chapters_verselimit[intval($parts[0])-1]);
										if($highverse>$verselimit){
											/* translators: the expressions <%1$d>, <%2$s>, <%3$d>, <%4$s> and %5$d must be left as is, they will be substituted dynamically by values in the script. See http://php.net/sprintf. */
											$msg = __('A verse in the query is out of bounds: there is no verse <%1$d> in the book <%2$s> at chapter <%3$d> in the requested version <%4$s>, the last possible verse is <%5$d>',"bibleget-io");
											$errs[] = sprintf($msg,$highverse,$thisbook,$parts[0],$jkey,$verselimit);
											update_option('bibleget_error_admin_notices',$errs);	
											return false;
										}
									}
								}
							}
							
							if(preg_match_all("/\.([1-9][0-9]{0,2})$/",$thisquery,$matches)){
								if(!is_array($matches[1])){
									$matches[1] = array($matches[1]);
								}
								$highverse = array_pop($matches[1]);
								foreach($indexes as $jkey => $jindex){
									$bookidx = array_search($myidx,$jindex["book_num"]);
									$chapters_verselimit = $jindex["verse_limit"][$bookidx];
									$verselimit = intval($chapters_verselimit[intval($parts[0])-1]);
									if($highverse>$verselimit){
										/* translators: the expressions <%1$d>, <%2$s>, <%3$d>, <%4$s> and %5$d must be left as is, they will be substituted dynamically by values in the script. See http://php.net/sprintf. */
										$msg = __('A verse in the query is out of bounds: there is no verse <%1$d> in the book <%2$s> at chapter <%3$d> in the requested version <%4$s>, the last possible verse is <%5$d>',"bibleget-io");
										$errs[] = sprintf($msg,$highverse,$thisbook,$parts[0],$jkey,$verselimit);
										update_option('bibleget_error_admin_notices',$errs);	
										return false;
									}
								}
							}
						}
					}
				}
			}
			else{
				$chapters = explode("-",$thisquery);
				foreach($chapters as $zchapter){
					foreach($indexes as $jkey => $jindex){
						$myidx = $validBookIndex+1; 
						$bookidx = array_search($myidx,$jindex["book_num"]);
						$chapter_limit = $jindex["chapter_limit"][$bookidx];
						if(intval($zchapter)>$chapter_limit){
							/* translators: the expressions <%1$d>, <%2$s>, <%3$s>, and <%4$d> must be left as is, they will be substituted dynamically by values in the script. See http://php.net/sprintf. */
							$msg = __('A chapter in the query is out of bounds: there is no chapter <%1$d> in the book <%2$s> in the requested version <%3$s>, the last possible chapter is <%4$d>',"bibleget-io");
							$errs[] = sprintf($msg,$zchapter,$thisbook,$jkey,$chapter_limit);
							update_option('bibleget_error_admin_notices',$errs);	
							return false;
						}
					}
				}
			}
				
			if(strpos($thisquery,"-")){
				if(preg_match_all("/[1-9][0-9]{0,2}\-[1-9][0-9]{0,2}/",$thisquery,$dummy) != substr_count($thisquery,"-")){
					// error message: A dash must be preceded and followed by 1 to 3 digits etc.
					//echo "There are ".preg_match("/(?=[1-9][0-9]{0,2}\-[1-9][0-9]{0,2})/",$query)." matches for dashes preceded and followed by valid 1-3 digit sequences;<br>";
					//echo "There are ".substr_count($query,"-")." matches for dashes in this query.";
					$errs[] = "ERROR in query <".$thisquery.">: " . $errorMessages[6];
					update_option('bibleget_error_admin_notices',$errs);	
					return false;
				}
				if(preg_match("/\-[1-9][0-9]{0,2}\,/",$thisquery) && (!preg_match("/\,[1-9][0-9]{0,2}\-/",$thisquery) || preg_match_all("/(?=\,[1-9][0-9]{0,2}\-)/",$thisquery,$dummy) > preg_match_all("/(?=\-[1-9][0-9]{0,2}\,)/",$thisquery,$dummy) )){
					// error message: there must be as many comma constructs preceding dashes as there are following dashes
					$errs[] = "ERROR in query <".$thisquery.">: " . $errorMessages[7];
					update_option('bibleget_error_admin_notices',$errs);	
					return false;
				}
				if(substr_count($thisquery,"-") > 1 && (!strpos($thisquery,".") || (substr_count($thisquery,"-")-1 > substr_count($thisquery,".")) )){
					// error message: there cannot be multiple dashes in a query if there are not as many dots minus 1.
					$errs[] = "ERROR in query <".$thisquery.">: " . $errorMessages[8];
					update_option('bibleget_error_admin_notices',$errs);	
					return false;
				}
					
				//if there's a comma before
				if(preg_match("/([1-9][0-9]{0,2}\,[1-9][0-9]{0,2}\-[1-9][0-9]{0,2})/",$thisquery,$matchA) ){
					// if there's a comma after, we're dealing with chapter,verse to chapter,verse
					if(preg_match("/([1-9][0-9]{0,2}\,[1-9][0-9]{0,2}\-[1-9][0-9]{0,2}\,[1-9][0-9]{0,2})/",$thisquery,$matchB) ){
						$matchesB = explode("-",$matchB[1]);
						$matchesB_LEFT = explode(",",$matchesB[0]);
						$matchesB_RIGHT = explode(",",$matchesB[1]);
						if($matchesB_LEFT[0] >= $matchesB_RIGHT[0]){
							/* translators: do not change the placeholders <%s>, they will be substituted dynamically by values in the script. See http://php.net/sprintf. */
							$errs[] = "ERROR in query <".$thisquery.">: " . sprintf(__("Chapters must be consecutive. Instead the first chapter indicator <%s> is greater than or equal to the second chapter indicator <%s> in the expression <%s>"),$matchesB_LEFT[0],$matchesB_RIGHT[0],$matchB[1]);
							update_option('bibleget_error_admin_notices',$errs);	
							return false;
						}						
					}
					// if there's no comma after, we're dealing with chapter,verse to verse
					else{
						$matchesA_temp = explode(",",$matchA[1]);
						$matchesA = explode("-",$matchesA_temp[1]);
						if($matchesA[0] >= $matchesA[1]){
							/* translators: do not change the placeholders <%s>, they will be substituted dynamically by values in the script. See http://php.net/sprintf. */
							$errs[] = "ERROR in query <".$thisquery.">: " . sprintf(__("Verses in the same chapter must be consecutive. Instead verse <%s> is greater than verse <%s> in the expression <%s>"),$matchesA[0],$matchesA[1],$matchA[1]);
							update_option('bibleget_error_admin_notices',$errs);	
							return false;
						}
					}
				}
				if(preg_match_all("/\.([1-9][0-9]{0,2}\-[1-9][0-9]{0,2})/",$thisquery,$matches) ){
					foreach($matches[1] as $match){
						$ints = explode("-",$match);
						if($ints[0] >= $ints[1]){
							/* translators: do not change the placeholders <%s>, they will be substituted dynamically by values in the script. See http://php.net/sprintf. */
							$errs[] = "ERROR in query <".$thisquery.">: " . sprintf(__("Verses concatenated by a dash must be consecutive, instead <%s> is greater than or equal to <%s> in the expression <%s>"),$ints[0],$ints[1],$match);
							update_option('bibleget_error_admin_notices',$errs);	
							return false;
						}
					}
				}
				/*
					if(preg_match_all("/(?<![0-9])(?=([1-9][0-9]{0,2}\-[1-9][0-9]{0,2}))/",$query,$dummy)){
				foreach($dummy[1] as $match){
				$ints = explode('.',$match);
				if(intval($ints[0]) >= intval($ints[1]) ){
				$errs[] = "ERROR in query <".$query.">: i valori concatenati dal punto devono essere consecutivi, invece ".$ints[0]." >= ".$ints[1]." nell'espressione <".$match.">";
				}
				}
				}
				*/
			}
			return $thisbook;
		}
		else{
			$errs[] = $errorMessages[2]." <".$thisquery.">";
			update_option('bibleget_error_admin_notices',$errs);	
			return false;
		}
	}else{
		if(!preg_match("/^[1-9][0-9]{0,2}/",$thisquery)){
			$errs[] = $errorMessages[10]." <".$thisquery.">";
			update_option('bibleget_error_admin_notices',$errs);	
			return false;
		}
	}
	return $thisbook;	
}

/* Mighty fine and dandy helper function I created! */
function toProperCase($txt){
  preg_match("/\p{L}/u", $txt, $mList, PREG_OFFSET_CAPTURE);
  $idx=$mList[0][1];
  $chr = mb_substr($txt,$idx,1,'UTF-8');
  if(preg_match("/\p{L&}/u",$chr)){
    $post = mb_substr($txt,$idx+1,null,'UTF-8'); 
    return mb_substr($txt,0,$idx,'UTF-8') . mb_strtoupper($chr,'UTF-8') . mb_strtolower($post,'UTF-8');
  }
  else{
    return $txt;
  }
}

function idxOf($needle,$haystack){
  foreach($haystack as $index => $value){
    if(is_array($haystack[$index])){
      foreach($haystack[$index] as $index2 => $value2){
        if(in_array($needle,$haystack[$index][$index2])){
          return $index;
        }
      }
    }
    else if(in_array($needle,$haystack[$index])){
      return $index;
    }
  }
  return false;
}

/*
 * FUNCTION isValidBook
* @var book
*/
function isValidBook($book){
	$biblebooks = array();
	if(get_option("bibleget_biblebooks0")===false){
		SetOptions();
	}
	for($i=0;$i<73;$i++){
		$usrprop = "bibleget_biblebooks".$i;
		$jsbook = json_decode(get_option($usrprop),true);
		array_push($biblebooks,$jsbook);
	}
	return idxOf($book,$biblebooks);
}


/*
 * FUNCTION getMetaData
* @var request
*/
function getMetaData($request){
	//request can be for building the biblebooks variable, or for building version indexes, or for requesting current validversions
	$notices= get_option('bibleget_error_admin_notices', array());	
	
	$url = "http://query.bibleget.io/metadata.php?query=" . $request . "&return=json";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	if( ini_get('safe_mode') || ini_get('open_basedir') ){
		// safe mode is on, we can't use some settings
	}else{
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	}
	 
	$response = curl_exec($ch);
	if(curl_errno($ch)){
		$optionsurl = admin_url("options-general.php?page=bibleget-settings-admin");
		/* translators: do not change the placeholders or the html markup, though you can translate the anchor title*/
		$notices[]= sprintf(__("There was a problem communicating with the BibleGet server. <a href=\"%s\" title=\"update metadata now\">Metadata needs to be manually updated</a>."),$optionsurl);
		update_option('bibleget_error_admin_notices', $notices);
		return false;
	}
	else{
		$info = curl_getinfo($ch);		
		//echo 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'];
		if($info["http_code"]!=200){
			$optionsurl = admin_url("options-general.php?page=bibleget-settings-admin");
			/* translators: do not change the placeholders or the html markup, though you can translate the anchor title*/
			$notices[]= sprintf(__("There may have been a problem communicating with the BibleGet server. <a href=\"%s\" title=\"update metadata now\">Metadata needs to be manually updated</a>."),$optionsurl);
			update_option('bibleget_error_admin_notices', $notices);
			return false;
		}		
	}
	curl_close($ch);
		
	$myjson = json_decode($response);
	if(property_exists($myjson,"results")){
		return $myjson;
		//var verses = myjson.results;
	}
	else{
		$optionsurl = admin_url("options-general.php?page=bibleget-settings-admin");
		/* translators: do not change the placeholders or the html markup, though you can translate the anchor title*/
		$notices[]= sprintf(__("There may have been a problem communicating with the BibleGet server. <a href=\"%s\" title=\"update metadata now\">Metadata needs to be manually updated</a>."),$optionsurl);
		update_option('bibleget_error_admin_notices', $notices);
		return false;
	}
}

function queryClean($query){
	// enforce query rules
	if($query===''){
		return __("You cannot send an empty query.","bibleget-io");
	}
	$query = trim($query);
	$query = preg_replace('/\s+/', '', $query);
	$query = str_replace(' ','',$query);

	if(strpos($query,':') && strpos($query,'.')){
		return __("Mixed notations have been detected. Please use either english notation or european notation.","bibleget-io").'<'+$query+'>';
	}
	else if(strpos($query,':')){ //if english notation is detected, translate it to european notation
		if(strpos($query,',') != -1){
			$query = str_replace(',','.',$query);
		}
		$query = str_replace(':',',',$query);
	}
	$queries=array_values(array_filter(explode(';',$query), function($var){
		return $var !== "";
	}));
	
	return array_map("toProperCase",$queries);
}


function bibleget_admin_notices() {
	if ($notices= get_option('bibleget_error_admin_notices')) {
		foreach ($notices as $notice) {
			echo "<div class='error'><p>$notice</p></div>";
		}
		delete_option('bibleget_error_admin_notices');
	}
	if ($notices= get_option('bibleget_admin_notices')) {
		foreach ($notices as $notice) {
			echo "<div class='updated'><p>$notice</p></div>";
		}
		delete_option('bibleget_admin_notices');
	}
	
}
add_action('admin_notices', 'bibleget_admin_notices');

function DeleteOptions(){
	for($i=0;$i<73;$i++){
		delete_option("bibleget_biblebooks".$i);
	}
	delete_option("bibleget_languages");
	$bibleversions = json_decode(get_option("bibleget_versions"));
	delete_option("bibleget_versions");
	$bibleversionsabbrev = get_object_vars($bibleversions);
	foreach($bibleversionsabbrev as $abbrev){
		delete_option("bibleget_".$abbrev."IDX");
	}
}

function SetOptions(){
	$metadata = getMetaData("biblebooks");
	if($metadata !== false){
		//write_log("Retrieved biblebooks metadata...");
    	//write_log($metadata);
    	if(property_exists($metadata,"results")){
			$biblebooks = $metadata->results;
			foreach($biblebooks as $key => $value){
				$biblebooks_str = json_encode($value);
				$option = "bibleget_biblebooks".$key;
				update_option($option,$biblebooks_str);
			}
		}
		if(property_exists($metadata,"languages")){
			$languages = array_map("toProperCase",$metadata->languages);
			//$languages_str = json_encode($languages);
			update_option("bibleget_languages",$languages);
		}		
	}
	
	$metadata = getMetaData("bibleversions");
	$versionsabbrev = array();
	if($metadata !== false){
		//write_log("Retrieved bibleversions metadata");
    	//write_log($metadata);
    	if(property_exists($metadata,"validversions_fullname")){
			$bibleversions = $metadata->validversions_fullname;
      		$versionsabbrev = array_keys(get_object_vars($bibleversions));
			$bibleversions_str = json_encode($bibleversions);
			$bbversions = json_decode($bibleversions_str,true);
			update_option("bibleget_versions",$bbversions);				
		}
    //write_log("versionsabbrev should now be populated:");
    //write_log($versionsabbrev);
	}
	
	if(count($versionsabbrev)>0){
		$versionsstr = implode(',',$versionsabbrev);
		$metadata = getMetaData("versionindex&versions=".$versionsstr);
		if($metadata !== false){
			//write_log("Retrieved versionindex metadata");
      		//write_log($metadata);
			if(property_exists($metadata,"indexes")){
				foreach($metadata->indexes as $versabbr => $value){
					$temp = array();
					$temp["book_num"] = $value->book_num;
					$temp["chapter_limit"] = $value->chapter_limit;
					$temp["verse_limit"] = $value->verse_limit;
					//$versionindex_str = json_encode($temp);
          			//write_log("creating new option: ["."bibleget_".$versabbr."IDX"."] with value:");
          			//write_log($temp);
					update_option("bibleget_".$versabbr."IDX",$temp);
				}
			}
		}
	}	
	
	//we only want the script to die if it's an ajax request...
	if(isset($_POST["isajax"]) && $_POST["isajax"]==1){
   		$notices=get_option('bibleget_admin_notices',array());
   		$notices[]=__("BibleGet Server data has been successfully renewed.","bibleget-io");
   		update_option('bibleget_admin_notices',$notices);
		echo "datarefreshed";
		wp_die();
	}
}

add_action( 'wp_ajax_refresh_bibleget_server_data', 'SetOptions' );


$langcodes= array(
      "af" => "Afrikaans",
      "ak" => "Akan",
      "sq" => "Albanian",
      "am" => "Amharic",
      "ar" => "Arabic",
      "hy" => "Armenian",
      "az" => "Azerbaijani",
      "eu" => "Basque",
      "be" => "Belarusian",
      "bn" => "Bengali",
      "bh" => "Bihari",
      "bs" => "Bosnian",
      "br" => "Breton",
      "bg" => "Bulgarian",
      "km" => "Cambodian",
      "ca" => "Catalan",
      "ny" => "Chichewa",
      "zh" => "Chinese",
      "co" => "Corsican",
      "hr" => "Croatian",
      "cs" => "Czech",
      "da" => "Danish",
      "nl" => "Dutch",
      "en" => "English",
      "eo" => "Esperanto",
      "et" => "Estonian",
      "fo" => "Faroese",
      "tl" => "Filipino",
      "fi" => "Finnish",
      "fr" => "French",
      "fy" => "Frisian",
      "gl" => "Galician",
      "ka" => "Georgian",
      "de" => "German",
      "el" => "Greek",
      "gn" => "Guarani",
      "gu" => "Gujarati",
      "ht" => "Haitian Creole",
      "ha" => "Hausa",
      "iw" => "Hebrew",
      "hi" => "Hindi",
      "hu" => "Hungarian",
      "is" => "Icelandic",
      "ig" => "Igbo",
      "id" => "Indonesian",
      "ia" => "Interlingua",
      "ga" => "Irish",
      "it" => "Italian",
      "ja" => "Japanese",
      "jw" => "Javanese",
      "kn" => "Kannada",
      "kk" => "Kazakh",
      "rw" => "Kinyarwanda",
      "rn" => "Kirundi",
      "kg" => "Kongo",
      "ko" => "Korean",
      "ku" => "Kurdish",
      "ky" => "Kyrgyz",
      "lo" => "Laothian",
      "la" => "Latin",
      "lv" => "Latvian",
      "ln" => "Lingala",
      "lt" => "Lithuanian",
      "lg" => "Luganda",
      "mk" => "Macedonian",
      "mg" => "Malagasy",
      "ms" => "Malay",
      "ml" => "Malayalam",
      "mt" => "Maltese",
      "mi" => "Maori",
      "mr" => "Marathi",
      "mo" => "Moldavian",
      "mn" => "Mongolian",
      "ne" => "Nepali",
      "no" => "Norwegian",
      "oc" => "Occitan",
      "or" => "Oriya",
      "om" => "Oromo",
      "ps" => "Pashto",
      "fa" => "Persian",
      "pl" => "Polish",
      "pt" => "Portuguese",
      "pa" => "Punjabi",
      "qu" => "Quechua",
      "ro" => "Romanian",
      "rm" => "Romansh",
      "ru" => "Russian",
      "gd" => "Scots Gaelic",
      "sr" => "Serbian",
      "sh" => "Serbo-Croatian",
      "st" => "Sesotho",
      "tn" => "Setswana",
      "sn" => "Shona",
      "sd" => "Sindhi",
      "si" => "Sinhalese",
      "sk" => "Slovak",
      "sl" => "Slovenian",
      "so" => "Somali",
      "es" => "Spanish",
      "su" => "Sundanese",
      "sw" => "Swahili",
      "sv" => "Swedish",
      "tg" => "Tajik",
      "ta" => "Tamil",
      "tt" => "Tatar",
      "te" => "Telugu",
      "th" => "Thai",
      "ti" => "Tigrinya",
      "to" => "Tonga",
      "tr" => "Turkish",
      "tk" => "Turkmen",
      "tw" => "Twi",
      "ug" => "Uighur",
      "uk" => "Ukrainian",
      "ur" => "Urdu",
      "uz" => "Uzbek",
      "vi" => "Vietnamese",
      "cy" => "Welsh",
      "wo" => "Wolof",
      "xh" => "Xhosa",
      "yi" => "Yiddish",
      "yo" => "Yoruba",
      "zu" => "Zulu"
);

$worldlanguages = array(
        "Afrikaans" => array(
          "en" => "Afrikaans",
          "it" => "Afrikaans",
          "es" => "Afrikáans",
          "fr" => "Afrikaans",
          "de" => "Afrikaans"
        ),
        "Albanian" => array(
          "en" => "Albanian",
          "it" => "Albanese",
          "es" => "Albanés",
          "fr" => "Albanais",
          "de" => "Albanisch"
        ),  
        "Arabic" => array(
          "en" => "Arabic",
          "it" => "Arabo",
          "es" => "Árabe",
          "fr" => "Arabe",
          "de" => "Arabisch"
        ),  
        "Chinese" => array(
          "en" => "Chinese",
          "it" => "Cinese",
          "es" => "Chino",
          "fr" => "Chinois",
          "de" => "Chinesische"
        ),  
        "Croatian" => array(
          "en" => "Croatian",
          "it" => "Croato",
          "es" => "Croata",
          "fr" => "Croate",
          "de" => "Kroatisch"
        ),  
        "Czech" => array(
          "en" => "Czech",
          "it" => "Ceco",
          "es" => "Checo",
          "fr" => "Tchèque",
          "de" => "Tschechisch"
        ),  
        "English" => array(
          "en" => "English",
          "it" => "Inglese",
          "es" => "Inglés",
          "fr" => "Anglais",
          "de" => "Griechisch"
        ),  
        "French" => array(
          "en" => "French",
          "it" => "Francese",
          "es" => "Francés",
          "fr" => "Français",
          "de" => "Ungarisch"
        ),  
        "German" => array(
          "en" => "German",
          "it" => "Tedesco",
          "es" => "Alemán",
          "fr" => "Allemand",
          "de" => "Deutsch"
        ),  
        "Greek" => array(
          "en" => "Greek",
          "it" => "Greco",
          "es" => "Griego",
          "fr" => "Grec",
          "de" => "Japanisch"
        ),  
        "Hungarian" => array(
          "en" => "Hungarian",
          "it" => "Ungherese",
          "es" => "Húngaro",
          "fr" => "Hongrois",
          "de" => "Koreanisch"
        ),  
        "Italian" => array(
          "en" => "Italian",
          "it" => "Italiano",
          "es" => "Italiano",
          "fr" => "Italien",
          "de" => "Latein"
        ),  
        "Japanese" => array(
          "en" => "Japanese",
          "it" => "Giapponese",
          "es" => "Japonés",
          "fr" => "Japonais",
          "de" => "Japanese"
        ),  
        "Korean" => array(
          "en" => "Korean",
          "it" => "Coreano",
          "es" => "Coreano",
          "fr" => "Coréen",
          "de" => "Korean"
        ),  
        "Latin" => array(
          "en" => "Latin",
          "it" => "Latino",
          "es" => "Latín",
          "fr" => "Latin",
          "de" => "Latin"
        ),  
        "Polish" => array(
          "en" => "Polish",
          "it" => "Polacco",
          "es" => "Polaco",
          "fr" => "Polonais",
          "de" => "Russisch"
        ),  
        "Portuguese" => array(
          "en" => "Portuguese",
          "it" => "Portoghese",
          "es" => "Portugués",
          "fr" => "Portugais",
          "de" => "Portugiesisch"
        ),  
        "Romanian" => array(
          "en" => "Romanian",
          "it" => "Rumeno",
          "es" => "Rumano",
          "fr" => "Roumain",
          "de" => "Rumänischen"
        ),        
        "Russian" => array(
          "en" => "Russian",
          "it" => "Russo",
          "es" => "Ruso",
          "fr" => "Russe",
          "de" => "Russian"
        ),  
        "Spanish" => array(
          "en" => "Spanish",
          "it" => "Spagnolo",
          "es" => "Español",
          "fr" => "Espagnol",
          "de" => "Thailändisch"
        ),  
        "Tagalog" => array(
          "en" => "Tagalog",
          "it" => "Tagalog",
          "es" => "Tagalo",
          "fr" => "Tagalog",
          "de" => "Tagalog"
        ),  
        "Tamil" => array(
          "en" => "Tamil",
          "it" => "Tamil",
          "es" => "Tamil",
          "fr" => "Tamoul",
          "de" => "Tamil"
        ),  
        "Thai" => array(
          "en" => "Thai",
          "it" => "Thai",
          "es" => "Thai",
          "fr" => "Thaï",
          "de" => "Thai"
        ),  
        "Vietnamese" => array(
          "en" => "Vietnamese",
          "it" => "Vietnamita",
          "es" => "Vietnamita",
          "fr" => "Vietnamien",
          "de" => "Vietnamesisch"
        )      
);

function Sortify($string)
{
	return preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1' . chr(255) . '$2', htmlentities($string, ENT_QUOTES, 'UTF-8'));
}

require_once(plugin_dir_path( __FILE__ ) . "options.php");

if( is_admin() ){
	$my_settings_page = new MySettingsPage();
}

/*
 * Function write_log
 * useful for debugging purposes 
 */
function write_log ( $log )  {
  $debugfile = get_stylesheet_directory()."/debug.txt";
  $datetime = strftime("%Y%m%d %H:%M:%S",time());
  if($myfile = fopen($debugfile, "a")){
    if ( is_array( $log ) || is_object( $log ) ) {
        fwrite($myfile, "[" . $datetime . "] " . print_r( $log, true) ."\n");
    } else {
        fwrite($myfile, "[" . $datetime . "] " . $log . "\n");
    }
    fclose($myfile);
  }
  else{
    echo '<div style="border: 1px solid Red;background-color:LightRed;">impossible to open or write to: '.$debugfile.'</div>';
  }
}