<?php
/**
 * Forwarder to doku.php
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 */
//start edit jayjay
//header("Location: doku.php");
if (isset($_GET)) {
	$query = array();
	foreach(array_keys($_GET) as $key){
		if(empty($_GET[$key])) continue;
		$query[] = $key."=".$_GET[$key];
	}
	$query_string = "?".implode("&", $query);
}else{
	$query_string = "";
}
header("Location: doku.php".$query_string);
//end edit jayjay
?>
