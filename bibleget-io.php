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

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}



function bibleget_shortcode($atts) {
  wp_enqueue_style('biblegetio-styles', plugins_url('css/styles.css', __FILE__), false, '1.0', 'all');
  
  $a = shortcode_atts(array(
    'query' => "",
    'format' => "html"  // default value if none supplied
    ), $atts);

    //remove whitespace from query
	$a["query"] = preg_replace('/\s+/', '', $a["query"]);
	$a["query"] = trim($a["query"]);
	$a["query"] = str_replace(' ','',$a["query"]);
	
    $queryCheck = queryCheck($a["query"]);
    if(count($queryCheck)==1 && $queryCheck[0]=="goodtogo"){
    	$ch = curl_init("www.bibleget.io/query/?query=".$a["query"]."&return=".$a["format"]);
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
    	$output = '<span style="color:Red;font-weight:bold;">Ci sono errori nello shortcode &apos;[bibleget]&apos;:</span><br />' . implode('<br />',$queryCheck);
    }
    
    return '<div class="bibleget-quote-div">' . $output . '</div>';
}
add_shortcode('bibleget', 'bibleget_shortcode');

function queryCheck($querystring){
	$abbreviations = array(
			"Gen",
			"Es",
			"Lv",
			"Nm",
			"Dt",
			"Gs",
			"Gdc",
			"Rt",
			"1Sam",
			"2Sam",
			"1Re",
			"2Re",
			"1Cr",
			"2Cr",
			"Esd",
			"Ne",
			"Tb",
			"Gdt",
			"Est",
			"1Mac",
			"2Mac",
			"Gb",
			"Sal",
			"Pr",
			"Qo",
			"Ct",
			"Sap",
			"Sir",
			"Is",
			"Ger",
			"Lam",
			"Bar",
			"Ez",
			"Dn",
			"Os",
			"Gl",
			"Am",
			"Abd",
			"Gn",
			"Mi",
			"Na",
			"Ab",
			"Sof",
			"Ag",
			"Zc",
			"Ml",
			"Mt",
			"Mc",
			"Lc",
			"Gv",
			"At",
			"Rm",
			"1Cor",
			"2Cor",
			"Gal",
			"Ef",
			"Fil",
			"Col",
			"1Ts",
			"2Ts",
			"1Tm",
			"2Tm",
			"Tt",
			"Fm",
			"Eb",
			"Gc",
			"1Pt",
			"2Pt",
			"1Gv",
			"2Gv",
			"3Gv",
			"Gd",
			"Ap"
	);
	
	$biblebooks = array(
			"Genesi",
			"Esodo",
			"Levitico",
			"Numeri",
			"Deuteronomio",
			"Giosue",
			"Giudici",
			"Rut",
			"1Samuele",
			"2Samuele",
			"1Re",
			"2Re",
			"1Cronache",
			"2Cronache",
			"Esdra",
			"Neemia",
			"Tobia",
			"Giuditta",
			"Ester",
			"1Maccabei",
			"2Maccabei",
			"Giobbe",
			"Salmi",
			"Proverbi",
			"Qoelet",
			"Cantico",
			"Sapienza",
			"Siracide",
			"Isaia",
			"Geremia",
			"Lamentazioni",
			"Baruc",
			"Ezechiele",
			"Daniele",
			"Osea",
			"Giole",
			"Amos",
			"Abdia",
			"Giona",
			"Michea",
			"Naum",
			"Abacuc",
			"Sofonia",
			"Aggeo",
			"Zaccaria",
			"Malachia",
			"Matteo",
			"Marco",
			"Luca",
			"Giovanni",
			"Atti",
			"Romani",
			"1Corinzi",
			"2Corinzi",
			"Galati",
			"Efesi",
			"Filippesi",
			"Colossesi",
			"1Tessalonicesi",
			"2Tessalonicesi",
			"1Timoteo",
			"2Timoteo",
			"Tito",
			"Filemone",
			"Ebrei",
			"Giacomo",
			"1Pietro",
			"2Pietro",
			"1Giovanni",
			"2Giovanni",
			"3Giovanni",
			"Giuda",
			"Apocalisse"
	);
	
	$errorMessages = array();
	$errorMessages[0] = "First query string must start with a valid book abbreviation!";
	$errorMessages[1] = "You must have a valid chapter following the book abbreviation!";
	$errorMessages[2] = "The book abbreviation is not a valid abbreviation. Please check the documentation for a list of correct abbreviations.";
	$errorMessages[3] = "You cannot use a dot without first using a comma. A dot is a liason between verses, which are separated from the chapter by a comma.";
	$errorMessages[4] = "A dot must be preceded and followed by 1 to 3 digits of which the first digit cannot be zero.";
	$errorMessages[5] = "A comma must be preceded and followed by 1 to 3 digits of which the first digit cannot be zero.";
	$errorMessages[6] = "A dash must be preceded and followed by 1 to 3 digits of which the first digit cannot be zero.";
	$errorMessages[7] = "If there is a chapter-verse construct following a dash, there must also be a chapter-verse construct preceding the same dash.";
	$errorMessages[8] = "There are multiple dashes in the query, but there are not enough dots. There can only be one more dash than dots.";
	$errorMessages[9] = "";
	
	$queries = array();
	$queries=explode(";",$querystring);
	$queries=array_values(array_filter($queries, function($var){
		return $var !== "";
	}));
	
	for($n=0;$n<count($queries);$n++){
		$queries[$n] = toProperCase($queries[$n]);
	}
		
	$errs = array();
	$goodtogo = true; //innocent until proven guilty
	$dummy = array(); // to avoid error messages on systems with PHP < 5.4 which required third parameter in preg_match_all
	
	// At least the first query must start with a book reference, which may have a number from 1 to 3 at the beginning
	if(!preg_match("/^[1-3]{0,1}[A-Z][a-z]{1,2}/",$queries[0])){
		// error message: querystring must have book indication at the very start...
		$errs[] = "ERROR in query <".$queries[0].">: " . $errorMessages[0];
		$goodtogo = false;
		//echo "<div>&lt;".$querystring."&gt;</div>";
	}
	
	foreach($queries as $query){
		if(preg_match("/^[1-3]{0,1}[A-Z][a-z]+/",$query) != preg_match("/^[1-3]{0,1}[A-Z][a-z]+[1-9][0-9]{0,2}/",$query)){
			// error message: every book indication must be followed by a valid chapter indication
			$errs[] = "ERROR in query <".$query.">: " . $errorMessages[1];
			$goodtogo = false;
		}
		if(preg_match("/^([1-3]{0,1}[A-Z][a-z]+)/",$query,$res)){
			if(!in_array($res[0],$biblebooks) && !in_array($res[0],$abbreviations)){
				// error message: unrecognized book abbreviation
				$errs[] = "ERROR in query <".$query.">: " . $errorMessages[2];
				$goodtogo = false;
			}
		}
		if(strpos($query,".")){
			if(!strpos($query,",") || strpos($query,",") > strpos($query,".")){
				// error message: You cannot use a dot without first using a comma. A dot is a liason between verses, which are separated from the chapter by a comma.
				$errs[] = "ERROR in query <".$query.">: " . $errorMessages[3];
				$goodtogo = false;
			}
			//if(preg_match_all("/(?=[1-9][0-9]{0,2}\.[1-9][0-9]{0,2})/",$query) != substr_count($query,".") ){
			//if(preg_match_all("/(?=([1-9][0-9]{0,2}\.[1-9][0-9]{0,2}))/",$query) < substr_count($query,".") ){
			if(preg_match_all("/(?<![0-9])(?=([1-9][0-9]{0,2}\.[1-9][0-9]{0,2}))/",$query,$dummy) != substr_count($query,".") ){
				// error message: A dot must be preceded and followed by 1 to 3 digits etc.
				$errs[] = "ERROR in query <".$query.">: " . $errorMessages[4];
				$goodtogo = false;
			}
			if(preg_match_all("/(?<![0-9])(?=([1-9][0-9]{0,2}\.[1-9][0-9]{0,2}))/",$query,$dummy)){
				foreach($dummy[1] as $match){
					$ints = explode('.',$match);
					if(intval($ints[0]) >= intval($ints[1]) ){
						$errs[] = "ERROR in query <".$query.">: i valori concatenati dal punto devono essere consecutivi, invece ".$ints[0]." >= ".$ints[1]." nell'espressione <".$match.">";
					}
				}
			}
		}
		
		
		if(strpos($query,",")){
			if(preg_match_all("/[1-9][0-9]{0,2}\,[1-9][0-9]{0,2}/",$query,$dummy) != substr_count($query,",")){
				// error message: A comma must be preceded and followed by 1 to 3 digits etc.
				//echo "There are ".preg_match_all("/(?=[1-9][0-9]{0,2}\,[1-9][0-9]{0,2})/",$query)." matches for commas preceded and followed by valid 1-3 digit sequences;<br>";
				//echo "There are ".substr_count($query,",")." matches for commas in this query.";
				$errs[] = "ERROR in query <".$query.">: " . $errorMessages[5];
				$goodtogo = false;
			}
		}
		
		if(strpos($query,"-")){
			if(preg_match_all("/[1-9][0-9]{0,2}\-[1-9][0-9]{0,2}/",$query,$dummy) != substr_count($query,"-")){
				// error message: A dash must be preceded and followed by 1 to 3 digits etc.
				//echo "There are ".preg_match("/(?=[1-9][0-9]{0,2}\-[1-9][0-9]{0,2})/",$query)." matches for dashes preceded and followed by valid 1-3 digit sequences;<br>";
				//echo "There are ".substr_count($query,"-")." matches for dashes in this query.";
				$errs[] = "ERROR in query <".$query.">: " . $errorMessages[6];
				$goodtogo = false;
			}
			if(preg_match("/\-[1-9][0-9]{0,2}\,/",$query) && (!preg_match("/\,[1-9][0-9]{0,2}\-/",$query) || preg_match_all("/(?=\,[1-9][0-9]{0,2}\-)/",$query,$dummy) > preg_match_all("/(?=\-[1-9][0-9]{0,2}\,)/",$query,$dummy) )){
				// error message: there must be as many comma constructs preceding dashes as there are following dashes
				$errs[] = "ERROR in query <".$query.">: " . $errorMessages[7];
				$goodtogo = false;
			}
			if(substr_count($query,"-") > 1 && (!strpos($query,".") || (substr_count($query,"-")-1 > substr_count($query,".")) )){
				// error message: there cannot be multiple dashes in a query if there are not as many dots minus 1.
				$errs[] = "ERROR in query <".$query.">: " . $errorMessages[8];
				$goodtogo = false;
			}
			
			//if there's a comma before
			if(preg_match("/([1-9][0-9]{0,2}\,[1-9][0-9]{0,2}\-[1-9][0-9]{0,2})/",$query,$matchA) ){
				// if there's a comma after, we're dealing with chapter,verse to chapter,verse
				if(preg_match("/([1-9][0-9]{0,2}\,[1-9][0-9]{0,2}\-[1-9][0-9]{0,2}\,[1-9][0-9]{0,2})/",$query,$matchB) ){
					$matchesB = explode("-",$matchB[1]);
					$matchesB_LEFT = explode(",",$matchesB[0]);
					$matchesB_RIGHT = explode(",",$matchesB[1]);
					if($matchesB_LEFT[0] >= $matchesB_RIGHT[0]){
						$errs[] = "ERROR in query <".$query.">: " . "chapters must be consecutive. Instead the first chapter indication <" . $matchesB_LEFT[0] . "> is greater than the second chapter indication <". $matchesB_RIGHT[0] ."> in the expression <".$matchB[1].">";
						$goodtogo = false;
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
						$errs[] = "ERROR in query <".$query.">: " . "verses in the same chapter must be consecutive. Instead verse <".$matchesA[0]."> is greater than verse <".$matchesA[1].">";
						$goodtogo = false;
					}
				}
			}
			if(preg_match_all("/\.([1-9][0-9]{0,2}\-[1-9][0-9]{0,2})/",$query,$matches) ){
				foreach($matches[1] as $match){
					$ints = explode("-",$match);
					if($ints[0] >= $ints[1]){
						$errs[] = "ERROR in query <".$query.">: " . "verses concatenated by the dash must be consecutive, instead <".$ints[0]."> is greater than or equal to <".$ints[1]."> in the expression <".$match.">";
						$goodtogo = false;
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
		
	
	}
	
	//if we have passed all checks
	if($goodtogo){
		return array("goodtogo");
	}
	else{
		return $errs;
	}
}


/* Mighty fine and dandy helper function I created! */
function toProperCase($str) {
	return preg_replace_callback("/\w\S*/",function ($str) {
		$txt=$str[0];
		preg_match("/\D/is", $txt, $mList, PREG_OFFSET_CAPTURE);
		$idx=$mList[0][1];
		return substr($txt,0,$idx) . strtoupper($txt[$idx]) . strtolower(substr($txt,$idx+1));
	},$str);
}


require_once(plugin_dir_path( __FILE__ ) . "options.php");
