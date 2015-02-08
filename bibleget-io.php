<?php
/*
Plugin Name: BibleGet IO
Version: 1.5
Plugin URI: http://www.bibleget.io/
Description: Effettua citazioni della Bibbia al volo, con shortcode [bibleget].
Author: John Romano D'Orazio
Author URI: http://johnrdorazio.altervista.org/
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
	
  	// Determine bible version(s)
  	if($a["versions"] !== ""){
  		$a["version"] = $a["versions"];
  	}
  	else if($a["version"] === ""){ $a["version"] = "NVBSE"; }
  	$versions = explode(",",$a["version"]);
  	if(count($versions)<1){
    	$output = '<span style="color:Red;font-weight:bold;">'.__("Devi indicare la versione desiderata con il parametro \"version\" (o le versioni desiderate separate con virgola con il parametro \"versions\")","bibleget-io").'</span>';
       	return '<div class="bibleget-quote-div">' . $output . '</div>';
   	}

   	$queries = queryClean($a["query"]);
	if(is_array($queries)){
		$goodqueries = processQueries($queries,$versions);
		$finalquery = implode(";",$goodqueries);
		if($finalquery != ""){
			//WIP continue HERE
		}	
	}
    else{
    	$output = '<span style="color:Red;font-weight:bold;">'.__("Ci sono errori nello shortcode","bibleget-io").' &apos;[bibleget]&apos;:</span><br />' . $queries;
       	return '<div class="bibleget-quote-div">' . $output . '</div>';
    }

    
    //TO BE UPDATED FROM HERE ON
    
    if(is_array($queryCheck) && count($queryCheck)==1 && $queryCheck[0]=="goodtogo"){
    	$ch = curl_init("www.bibleget.io/query/index2?query=".$a["query"]."&version=".$a["version"]."&return=html");
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    	
    	if( ini_get('safe_mode') || ini_get('open_basedir') ){
    		// safe mode is on, we can't use some settings
    	}else{
    		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    	}
    	
    	$output = curl_exec($ch);
    	// remove style and title tags from the output
    	$output = substr($output,0,strpos($output, "<style")) . substr($output,strpos($output, "</style"),strlen($output));
    	$output = substr($output,0,strpos($output, "<title")) . substr($output,strpos($output, "</title"),strlen($output));
    	
    	curl_close($ch);    	 
    }
    else{
    	$output = '<span style="color:Red;font-weight:bold;">'.__("Ci sono errori nello shortcode","bibleget-io").' &apos;[bibleget]&apos;:</span><br />' . implode('<br />',$queryCheck);
    }
    
    return '<div class="bibleget-quote-div">' . $output . '</div>';
}
add_shortcode('bibleget', 'bibleget_shortcode');

function processQueries($queries,$versions){
	$goodqueries = array();
		
	$thisbook = null;
	if(get_option("bibleget_".$versions[0]."IDX")===false){
		SetOptions();
	}
	$indexes = array();
	foreach($versions as $key => $value){
		$temp = get_option("bibleget_".$value."IDX");
		$temp1 = json_encode($temp);
		$indexes[$value] = json_decode($temp,true);
	}
	
	$notices= get_option('bibleget_error_admin_notices', array());
	foreach($queries as $key => $value){
		$thisquery = toProperCase($value); //shouldn't be necessary because already array_mapped, but better safe than sorry
		if($key===0){
			if(!preg_match("/^[1-3]{0,1}((\p{L}\p{M}*)+)/",$thisquery)){
				$notices[] = __("First query string must start with a valid book abbreviation!","bibleget-io");
				continue;
			}
		}
		$thisbook = checkQuery($thisquery,$indexes,$thisbook);
		if($thisbook !== false){
			array_push($goodqueries,$thisquery);
		}
	}
	update_option('bibleget_error_admin_notices',$notices);
	return $goodqueries;
	
}

function checkQuery($thisquery,$indexes,$thisbook=""){
	
	$errorMessages = array();
	$errorMessages[0] = __("There cannot be more commas than there are dots.","bibleget-io");
	$errorMessages[1] = __("You must have a valid chapter following the book abbreviation!","bibleget-io");
	$errorMessages[2] = __("The book abbreviation is not a valid abbreviation. Please check the documentation for a list of correct abbreviations.","bibleget-io");
	$errorMessages[3] = __("You cannot use a dot without first using a comma. A dot is a liason between verses, which are separated from the chapter by a comma.","bibleget-io");
	$errorMessages[4] = __("A dot must be preceded and followed by 1 to 3 digits of which the first digit cannot be zero.","bibleget-io");
	$errorMessages[5] = __("A comma must be preceded and followed by 1 to 3 digits of which the first digit cannot be zero.","bibleget-io");
	$errorMessages[6] = __("A dash must be preceded and followed by 1 to 3 digits of which the first digit cannot be zero.","bibleget-io");
	$errorMessages[7] = __("If there is a chapter-verse construct following a dash, there must also be a chapter-verse construct preceding the same dash.","bibleget-io");
	$errorMessages[8] = __("There are multiple dashes in the query, but there are not enough dots. There can only be one more dash than dots.","bibleget-io");
	/* translators: the expressions %1$d, %2$d, and %3$s must be left as is, they will be substituted dynamically by values in the script. See http://php.net/sprintf. */
	$errorMessages[9] = __('the values concatenated by the dot must be consecutive, instead %1$d >= %2$d in the expression <%3$s>',"bibleget-io");
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
						foreach($matches[1] as $match){
							foreach($indexes as $jkey => $jindex){
								$bookidx = array_search($myidx,$jindex["book_num"]);
								$chapter_limit = $jindex["chapter_limit"][$bookidx];
								if($match>$chapter_limit){
									/* translators: the expressions <%1$d>, <%2$s>, <%3$s>, and <%4$d> must be left as is, they will be substituted dynamically by values in the script. See http://php.net/sprintf. */
									$msg = __('A chapter in the query is out of bounds: there is no chapter <%1$d> in <%2$s> in the requested version <%3$s>, the last possible chapter is {%4$d}',"bibleget-io");
									$errs[] = sprintf($msg,$match,$thisbook,$jkey,$chapter_limit);
									update_option('bibleget_error_admin_notices',$errs);	
									return false;
								}
							}
						}
						
						$commacount = substr_count($thisquery,",");
						if($commacount>1){
							if(!str_pos($thisquery,'-')){
								$errs[] = __("You cannot have more than one colon and not have a dash!","bibleget-io")." <".$thisquery.">";
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
										$msg = __('A verse in the query is out of bounds: there is no verse <%1$d> in <%2$s> chapter <%3$d> in the requested version <%4$s>, the last possible verse is %5$d',"bibleget-io");
										$errs[] = sprintf($msg,$pp[1],$thisbook,$pp[0],$jkey,$verselimit);
										update_option('bibleget_error_admin_notices',$errs);	
										return false;
									}
								}
							}
						}
						elseif($commacount==1){
							$parts = explode(",",$thisquery);
							if(str_pos($parts[1],'-')){
								if(preg_match_all("/[,\.][1-9][0-9]{0,2}\-([1-9][0-9]{0,2})/",$thisquery,$matches)){
									if(!is_array($matches[1])){
										$matches[1] = array($matches[1]);
									}
									$highverse = intval(array_pop($matches[1]));
									foreach($indexes as $jkey => $jindex){
										$bookidx = array_search($myidx,$jindex["book_num"]);
										$chapters_verselimit = $jindex["verse_limit"][$bookidx];
										$verselimit = intval($chapters_verselimit[intval($parts[0])-1]);
										if($highverse>$verselimit){
											/* translators: the expressions <%1$d>, <%2$s>, <%3$d>, <%4$s> and %5$d must be left as is, they will be substituted dynamically by values in the script. See http://php.net/sprintf. */
											$msg = __('A verse in the query is out of bounds: there is no verse <%1$d> in <%2$s> chapter <%3$d> in the requested version <%4$s>, the last possible verse is %5$d',"bibleget-io");
											$errs[] = sprintf($msg,$highverse,$thisbook,$parts[0],$jkey,$verselimit);
											update_option('bibleget_error_admin_notices',$errs);	
											return false;
										}
									}
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
											$msg = __('A verse in the query is out of bounds: there is no verse <%1$d> in <%2$s> chapter <%3$d> in the requested version <%4$s>, the last possible verse is %5$d',"bibleget-io");
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
										$msg = __('A verse in the query is out of bounds: there is no verse <%1$d> in <%2$s> chapter <%3$d> in the requested version <%4$s>, the last possible verse is %5$d',"bibleget-io");
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
							$msg = __('A chapter in the query is out of bounds: there is no chapter <%1$d> in <%2$s> in the requested version <%3$s>, the last possible chapter is {%4$d}',"bibleget-io");
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
							$errs[] = "ERROR in query <".$thisquery.">: " . "chapters must be consecutive. Instead the first chapter indication <" . $matchesB_LEFT[0] . "> is greater than the second chapter indication <". $matchesB_RIGHT[0] ."> in the expression <".$matchB[1].">";
							update_option('bibleget_error_admin_notices',$errs);	
							return false;
						}
						/*
							else if($matchesB_LEFT[0] == $matchesB_RIGHT[0]){
						if($matchesB_LEFT[1] >= $matchesB_RIGHT[1]){
						$err[] = "ERROR in query <".$query.">: " . "verses in the same chapter must be consecutive. Instead the first verse indication <" . $matchesB_LEFT[1] . "> is greater than or equal to the second verse indication <" . $matchesB_RIGHT[1] . ">";
						}
						}
						else{
						// if chapters are consecutive then verses are too
						}
						*/
					}
					// if there's no comma after, we're dealing with chapter,verse to verse
					else{
						$matchesA_temp = explode(",",$matchA[1]);
						$matchesA = explode("-",$matchesA_temp[1]);
						if($matchesA[0] >= $matchesA[1]){
							$errs[] = "ERROR in query <".$thisquery.">: " . "verses in the same chapter must be consecutive. Instead verse <".$matchesA[0]."> is greater than verse <".$matchesA[1].">";
							update_option('bibleget_error_admin_notices',$errs);	
							return false;
						}
					}
				}
				if(preg_match_all("/\.([1-9][0-9]{0,2}\-[1-9][0-9]{0,2})/",$thisquery,$matches) ){
					foreach($matches[1] as $match){
						$ints = explode("-",$match);
						if($ints[0] >= $ints[1]){
							$errs[] = "ERROR in query <".$thisquery.">: " . "verses concatenated by the dash must be consecutive, instead <".$ints[0]."> is greater than or equal to <".$ints[1]."> in the expression <".$match.">";
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
		} // end if from line 225
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
		$notices[]= sprintf(__("There was a problem communicating with the server. <a href='%s' title='update metadata now'>Metadata needs to be manually updated</a>."),$optionsurl);
		update_option('bibleget_error_admin_notices', $notices);
		return false;
	}
	else{
		$info = curl_getinfo($ch);		
		//echo 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'];
		if($info["http_code"]!=200){
			$optionsurl = admin_url("options-general.php?page=bibleget-settings-admin");
			$notices[]= sprintf(__("There may have been a problem communicating with the server. <a href='%s' title='update metadata now'>Metadata needs to be manually updated</a>."),$optionsurl);
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
		$notices[]= sprintf(__("There may have been a problem communicating with the server. <a href='%s' title='update metadata now'>Metadata needs to be manually updated</a>."),$optionsurl);
		update_option('bibleget_error_admin_notices', $notices);
		return false;
	}
}

function queryClean($query){
	// enforce query rules
	if($query===''){
		return __("I cannot send an empty query.");
	}
	$query = trim($query);
	$query = preg_replace('/\s+/', '', $query);
	$query = str_replace(' ','',$a["query"]);

	if(strpos($query,':') && strpos($query,'.')){
		return __("Mixed notations have been detected. Please use either english notation or european notation.").'<'+$query+'>';
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
		if(property_exists($metadata,"validversions_fullname")){
			$bibleversions = $metadata->validversions_fullname;
			$versionsabbrev = get_object_vars($bibleversions);
			$bibleversions_str = json_encode($bibleversions);
			$bbversions = json_decode($bibleversions_str,true);
			update_option("bibleget_versions",$bbversions);				
		}
	}
	
	if(count($versionsabbrev)>0){
		$versionsstr = implode(',',$versionsabbrev);
		$metadata = getMetaData("versionindex&versions=".$versionsstr);
		if($metadata !== false){
			if(property_exists($metadata,"indexes")){
				foreach($metadata->indexes as $versabbr => $value){
					$temp = new stdClass();
					$temp->book_num = $metadata["indexes"][$versabbr]["book_num"];
					$temp->chapter_limit = $metadata["indexes"][$versabbr]["chapter_limit"];
					$temp->verse_limit = $metadata["indexes"][$versabbr]["verse_limit"];
					//$versionindex_str = json_encode($temp);
					update_option("bibleget_".$versabbr."IDX",$temp);
				}
			}
		}
	}	
}

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

require_once(plugin_dir_path( __FILE__ ) . "options.php");

if( is_admin() ){
	$my_settings_page = new MySettingsPage();
}