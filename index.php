<?php
/**
 * Created by PhpStorm.
 * User: arthurturlak
 * Date: 12/19/18
 * Time: 11:30 AM
 */

/*Composer autoload*/
require __DIR__ . '/vendor/autoload.php';

/* Include the meekro DB class */
require_once __DIR__ . '/includes/meekrodb.2.3.class.php';
DB::$user = 'englund';
DB::$password = 'englund';
DB::$dbName = 'englund';

//$results = DB::query("SELECT * FROM view_dw_class");
////print_r($results);
//
//foreach ($results as $row) {
////	echo "Name: " . $row['name'] . "\n";
////	echo "Age: " . $row['age'] . "\n";
////	echo "Height: " . $row['height'] . "\n";
////	echo "-------------\n";
//
////print_R($row);
////print "<br/>";
//
//}


/*General Select */
//$sku = 'RUL10';

//build a list of skus
//TODO: Build this list from another query or a some form input
$skus = array('BLU','RUL10');

//$results = DB::query("SELECT * FROM dw_item WHERE dwin_item_number = %s",$sku);
////print_r($results);
//
//foreach ($results as $row) {
////	echo "Name: " . $row['name'] . "\n";
////	echo "Age: " . $row['age'] . "\n";
////	echo "Height: " . $row['height'] . "\n";
////	echo "-------------\n";
//
//
//	foreach ($row as $key=>$val){
//	echo $key.":"  . $val . "<br/>";
//
//	}
//
////	print_R($row);
//	print "\n";
//
//}

//cycle through all the skus
foreach ($skus as $sku){

//initialize $note;
	$note = '';

//this will output a note (aka detailed description) for the product
//each db row is an individual row of the description.
	$notes = DB::query("SELECT mx_text FROM view_item_notes WHERE mg_group_name = %s ORDER BY mx_line_nbr",$sku);
	foreach ($notes as $row) {

		foreach ($row as $key=>$val){
			//concatenate each row's note value
			//add space to end of each line to ensure a space exists between words
			$note .= $val." ";

			//	print $key.":"  . $val . "<br/>";

		}

//	print_R($row);
//	print "\n";
	}


//<xmp> tags will show raw html
	print '<xmp>';
//print_R($note);
	print '</xmp>';


//TODO: get all child products under the
	$results = DB::query(
			"SELECT * FROM `dw_item` INNER JOIN `in` ON `dw_item`.`dwin_item_number`=`in`.`in_item_number` AND `dw_item`.`dwin_store`=`in`.`in_store` INNER JOIN `view_dw_department` ON `dw_item`.`dwin_store`=`view_dw_department`.`dwde_store_number` AND `dw_item`.`dwin_department`=`view_dw_department`.`dwde_department` INNER JOIN `view_dw_class` ON `dw_item`.`dwin_store`=`view_dw_class`.`dwcl_store` AND `dw_item`.`dwin_class`=`view_dw_class`.`dwcl_class` INNER JOIN `view_dw_fineline` ON `dw_item`.`dwin_store`=`view_dw_fineline`.`dwfi_store` AND `dw_item`.`dwin_fineline`=`view_dw_fineline`.`dwfi_fineline_code`INNER JOIN `view_dw_vendor` ON `dw_item`.`dwin_store`=`view_dw_vendor`.`dwvm_store` AND `dw_item`.`dwin_primary_vendor`=`view_dw_vendor`.`dwvm_vendor_code`INNER JOIN `view_dw_manufacturer` ON `dw_item`.`dwin_store`=`view_dw_manufacturer`.`dwvm_store` AND `dw_item`.`dwin_manufacturer`=`view_dw_manufacturer`.`dwvm_vendor_code` WHERE dwin_item_number = %s",$sku);

	foreach ($results as $key=>$row){

		$notes = DB::query("SELECT mx_text FROM view_item_notes WHERE mg_group_name = %s ORDER BY mx_line_nbr",$sku);
		foreach ($row as $key1=>$val){
			//concatenate each row's note value
			//add space to end of each line to ensure a space exists between words
			$note .= $val." ";

			//	print $key.":"  . $val . "<br/>";

		}
		$results[$key]['item_notes_concat'] = '<xmp>'.$note.'</xmp>';


	}

//	print_R($row);
//	print "\n";
//}


//<xmp> tags will show raw html
//print '<xmp>';
//print_R($note);
//print '</xmp>';


//foreach ($results as $row) {




//	$rows[] = $row;
////	print '<xmp>';
//	print'<pre>';
//	print_R($row);
//	print'</pre>';
//	//print '<br/>';
////	print '</xmp>';
//}
//var_dump($rows);

	print'<pre>';
	print_R($results);
	print'</pre>';




}

