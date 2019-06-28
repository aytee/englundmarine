<?php
/**
 * Created by PhpStorm.
 * User: arthurturlak
 * Date: 12/19/18
 * Time: 11:30 AM
 */

//set PHP error reporting
// Report simple running errors
//error_reporting(E_ERROR | E_WARNING | E_PARSE);

error_reporting(E_ERROR | E_PARSE);

// Report all errors except E_NOTICE
//error_reporting(E_ALL & ~E_NOTICE);


/*Composer autoload*/
require __DIR__ . '/vendor/autoload.php';

if(file_exists('../permanent/settings.php')){
	include '../permanent/settings.php';
}else{
	print "<strong>You are missing the settings.php file in the /permanent directory</strong>";

}

/* Include the meekro DB class */
require_once __DIR__ . '/includes/meekrodb.2.3.class.php';
require_once __DIR__ . '/includes/englundproducts.class.php';

//primary DB
$db = new MeekroDB($db_host, $db_user, $db_password,$db_name,$db_port);

//secondary datasource
$db2 = new MeekroDB($db2_host, $db2_user, $db2_password,$db2_name,$db2_port);



//filter the input just in case somebody gets access to this and wants to do something nefarious
//add new $_GET args here
$args = array(
		'department' => FILTER_SANITIZE_STRING,
		'type' => FILTER_SANITIZE_STRING,
		'section' => FILTER_SANITIZE_STRING
);

//use the $clean array for all the sanitized $_GET values
$clean = filter_input_array(INPUT_GET,$args);

//Build products list
$eproducts = new EnglundProducts();

$eproducts->db = $db;
$eproducts->db2 = $db2;

//department code (BI, DN, etc)
$eproducts->department = $clean['department'];
$eproducts->section = $clean['section'];

// Create a new DOM document  (XML)
$newdoc = new DOMDocument;
$newdoc->encoding = 'utf-8';
$newdoc->formatOutput = true;

//create outer wrapper for all products
$products = $newdoc->createElement('products');
$newdoc->appendChild($products);

//build temp section  and sectionclass tables
$eproducts->buildSectionTable();
$eproducts->buildSectionClassTable();

//get Product Type List
$eproducts->getProductTypeList();



//holds the product type list array of parent / child products
//$eproducts->product_type_list;

//holds the product list of "regular" products
//$eproducts->product_type_list_solo;

//print_r($eproducts->product_type_list);

//track <class> value so that each time a new one shows up, we add a <class_section></class_section>
//track <fineline> value so that each time a new one shows up, we add a <fineline_section></fineline_section>
$current_class = '';
$current_fineline = '';


/*General Select */


//$query = 'SELECT * FROM `dw_item`
//INNER JOIN `IN` ON `dw_item`.`dwin_item_number`=`IN`.`in_item_number` AND `dw_item`.`dwin_store`=`IN`.`in_store` AND `dw_item`.`dwin_store`=1 AND `IN`.`in_store`=1
//INNER JOIN `view_dw_department` ON `dw_item`.`dwin_store`=`view_dw_department`.`dwde_store_number` AND `dw_item`.`dwin_department`=`view_dw_department`.`dwde_department` AND `view_dw_department`.`dwde_store_number`=1
//INNER JOIN `view_dw_class` ON `dw_item`.`dwin_store`=`view_dw_class`.`dwcl_store` AND `dw_item`.`dwin_class`=`view_dw_class`.`dwcl_class` AND `view_dw_class`.`dwcl_store`=1
//INNER JOIN `view_dw_fineline` ON `dw_item`.`dwin_store`=`view_dw_fineline`.`dwfi_store` AND `dw_item`.`dwin_fineline`=`view_dw_fineline`.`dwfi_fineline_code` AND `view_dw_fineline`.`dwfi_store`=1
//INNER JOIN `view_dw_vendor` ON `dw_item`.`dwin_store`=`view_dw_vendor`.`dwvm_store` AND `dw_item`.`dwin_primary_vendor`=`view_dw_vendor`.`dwvm_vendor_code` AND `view_dw_vendor`.`dwvm_store`=1
//INNER JOIN `view_dw_manufacturer` ON `dw_item`.`dwin_store`=`view_dw_manufacturer`.`dwvm_store` AND `dw_item`.`dwin_manufacturer`=`view_dw_manufacturer`.`dwvm_vendor_code` AND `view_dw_manufacturer`.`dwvm_store`=1
//WHERE dw_item.dwin_manufacturer = %s ';
//
//if($_GET['type']=='short'){
//	$skus = array('RUL37A','RUL-BP12V','BLU5063','BLU-ESBS');
//
//}
//if($_GET['type']=='blu'){
//	$topid = 'blu';
//	//get all items that have dw_item.manufacturer_id = "BLU"
//}

//JEREMY ADDED THE ORDER BY....NOT SURE WHICH QUERY IT NEEDS TO GO IN






//query for all skus by department
if($clean['type']=='all'){

//	$query = 'SELECT * FROM `dw_item`
//		INNER JOIN `IN` ON `dw_item`.`dwin_item_number`=`IN`.`in_item_number` AND `dw_item`.`dwin_store`=`IN`.`in_store` AND `dw_item`.`dwin_store`=1 AND `IN`.`in_store`=1	AND `dw_item`.`dwin_department`="'.$eproducts->department.'"
//		INNER JOIN `view_dw_department` ON `dw_item`.`dwin_store`=`view_dw_department`.`dwde_store_number` AND `dw_item`.`dwin_department`=`view_dw_department`.`dwde_department` AND `view_dw_department`.`dwde_store_number`=1
//		INNER JOIN `view_dw_class` ON `dw_item`.`dwin_store`=`view_dw_class`.`dwcl_store` AND `dw_item`.`dwin_class`=`view_dw_class`.`dwcl_class` AND `view_dw_class`.`dwcl_store`=1
//		INNER JOIN `view_dw_fineline` ON `dw_item`.`dwin_store`=`view_dw_fineline`.`dwfi_store` AND `dw_item`.`dwin_fineline`=`view_dw_fineline`.`dwfi_fineline_code` AND `view_dw_fineline`.`dwfi_store`=1
//		INNER JOIN `view_dw_vendor` ON `dw_item`.`dwin_store`=`view_dw_vendor`.`dwvm_store` AND `dw_item`.`dwin_primary_vendor`=`view_dw_vendor`.`dwvm_vendor_code` AND `view_dw_vendor`.`dwvm_store`=1
//		INNER JOIN `view_dw_manufacturer` ON `dw_item`.`dwin_store`=`view_dw_manufacturer`.`dwvm_store` AND `dw_item`.`dwin_manufacturer`=`view_dw_manufacturer`.`dwvm_vendor_code` AND `view_dw_manufacturer`.`dwvm_store`=1
//		ORDER BY `dw_item`.`dwin_class` ASC, `dw_item`.`dwin_fineline`ASC, `dw_item`.`dwin_display_item_number`ASC   ' ;
//

	$query = 'SELECT * FROM `dw_item` 
INNER JOIN section_class ON dw_item.dwin_class=section_class.class AND section_class.section="'.$eproducts->section.'"
		INNER JOIN `view_dw_department` ON `dw_item`.`dwin_store`=`view_dw_department`.`dwde_store_number` AND `dw_item`.`dwin_department`=`view_dw_department`.`dwde_department` AND `view_dw_department`.`dwde_store_number`=1
		INNER JOIN `view_dw_class` ON `dw_item`.`dwin_store`=`view_dw_class`.`dwcl_store` AND `dw_item`.`dwin_class`=`view_dw_class`.`dwcl_class` AND `view_dw_class`.`dwcl_store`=1
		INNER JOIN `view_dw_fineline` ON `dw_item`.`dwin_store`=`view_dw_fineline`.`dwfi_store` AND `dw_item`.`dwin_fineline`=`view_dw_fineline`.`dwfi_fineline_code` AND `view_dw_fineline`.`dwfi_store`=1
		INNER JOIN `view_dw_vendor` ON `dw_item`.`dwin_store`=`view_dw_vendor`.`dwvm_store` AND `dw_item`.`dwin_primary_vendor`=`view_dw_vendor`.`dwvm_vendor_code` AND `view_dw_vendor`.`dwvm_store`=1
		INNER JOIN `view_dw_manufacturer` ON `dw_item`.`dwin_store`=`view_dw_manufacturer`.`dwvm_store` AND `dw_item`.`dwin_manufacturer`=`view_dw_manufacturer`.`dwvm_vendor_code` AND `view_dw_manufacturer`.`dwvm_store`=1 
		ORDER BY `dw_item`.`dwin_class` ASC, `dw_item`.`dwin_fineline`ASC, `dw_item`.`dwin_display_item_number`ASC   ' ;




}

print 'QUERY:: '.$query .'<br/><br/>';

$topproducts_results = $db->query($query);

//build a list of skus
foreach ($topproducts_results as $topkey=>$toprow) {

	//Not sure if this should be 'dwin_display_item_number' or 'dwin_item_number'
	$skus[] = $toprow['dwin_display_item_number'];

}



//cycle through all the skus
foreach ($skus as $sku){

	//cycle through product array and build array

	//if product sku is a "regular" item
	if(array_key_exists($sku, $eproducts->product_type_list_solo)){
		//print "hey, im a regular:".$sku."<br/>";
		$product_families[$sku]['type'] = 'regular';
		$product_families[$sku]['children'][]= $sku;  //should only ever be a single child for 'regular' item

	}else{
		//product is a child or parent

		//$child_products = array();
		$product_families = array();

		//see if it is in the $eproducts->product_type_list array
		foreach ($eproducts->product_type_list as $item_sku => $info){

			if($info['item_type']=='child' && $info['cluster']===$sku){
				//DO NOTHING
				//it's a child, so skip it here.  We only want parents here
				//	print "im a child";

				//	$product_families[$info['cluster']][] = $info['item_number'];


			}elseif($info['item_type']==='parent' && $item_sku === $sku  ){
				//print 'parent::';
				//	print_r( $eproducts->product_type_list[$sku]);
				//$product_families[$info['cluster']][] = $info['item_number'];


				//$temp_prod = $eproducts->product_type_list;
				//loop through the array again and
				//fetch all children of this parent sku from the array
				foreach ($eproducts->product_type_list as $sub_sku => $sub_info){


					//if it is a child and the child 'cluster' is the same as the parent sku
					if($sub_info['item_type']== 'child' && $sub_info['cluster'] == $sku){
						//print 'adding child here';
						//build the product families array
						$product_families[$sub_info['cluster']]['type'] = 'family';
						$product_families[$sub_info['cluster']]['children'][]= $sub_info['item_number'];


					}

				}



			}

		}

	}




//	print_R($temp_item);
//	print '<pre>Prod fam:';
//	print_R($product_families);
//	print '</pre>';

	foreach ($product_families as $pid => $pdata){
		foreach($pdata['children'] as $psid => $subid){



			$subproducts_results = $db->query(
					'SELECT * FROM `dw_item` 
						INNER JOIN `IN` ON `dw_item`.`dwin_item_number`=`IN`.`in_item_number` AND `dw_item`.`dwin_store`=`IN`.`in_store`  AND `dw_item`.`dwin_store`=1 AND `IN`.`in_store`=1 
						INNER JOIN `view_dw_department` ON `dw_item`.`dwin_store`=`view_dw_department`.`dwde_store_number` AND `dw_item`.`dwin_department`=`view_dw_department`.`dwde_department`  AND `view_dw_department`.`dwde_store_number`=1
						INNER JOIN `view_dw_class` ON `dw_item`.`dwin_store`=`view_dw_class`.`dwcl_store` AND `dw_item`.`dwin_class`=`view_dw_class`.`dwcl_class`  AND `view_dw_class`.`dwcl_store`=1
						INNER JOIN `view_dw_fineline` ON `dw_item`.`dwin_store`=`view_dw_fineline`.`dwfi_store` AND `dw_item`.`dwin_fineline`=`view_dw_fineline`.`dwfi_fineline_code` AND `view_dw_fineline`.`dwfi_store`=1
						INNER JOIN `view_dw_vendor` ON `dw_item`.`dwin_store`=`view_dw_vendor`.`dwvm_store` AND `dw_item`.`dwin_primary_vendor`=`view_dw_vendor`.`dwvm_vendor_code` AND `view_dw_vendor`.`dwvm_store`=1
						INNER JOIN `view_dw_manufacturer` ON `dw_item`.`dwin_store`=`view_dw_manufacturer`.`dwvm_store` AND `dw_item`.`dwin_manufacturer`=`view_dw_manufacturer`.`dwvm_vendor_code` AND `view_dw_manufacturer`.`dwvm_store`=1 
						WHERE dwin_display_item_number = %s ',$subid);

			foreach ($subproducts_results as $subkey=>$subrow1) {

				foreach($subrow1 as $subk =>$subv){
					//	print "<br/>".$subk.":"  . $subv . "<br/>";
					//$product_families[$pid]['children'][$psid][$subk]= (string)$subv;
					$product_families[$pid][$subid][$subk]= $subv;

				}
			}

		}

	}
	reset($product_families);

//	print '<pre>Prod fam193:';
//	print_R($product_families);
//	print '</pre>';


	//By the time we get here, we have built an entire nested array of products and subproducts

	///////
//

//initialize $note;
	$note = '';

	//set default
	$table_count = 0;

//this will output a note (aka detailed description) for the product
//each db row is an individual row of the description.
	//This builds an array of all the Production descriptions that is referenced further below
	//must link with the foreign key view_item_notes.mg_group_name = dw_item.dwin_item_number
	//$notes = $db->query("SELECT mx_text FROM view_item_notes WHERE mg_group_name = %s ORDER BY mx_line_nbr",$sku);

	//using this query resultsin the display_item_number being added to each row of the notes.
//	$notes = $db->query("SELECT dw_item.dwin_display_item_number, view_item_notes.mx_text
//FROM view_item_notes INNER JOIN dw_item ON view_item_notes.mg_group_name = dw_item.dwin_item_number
//WHERE  view_item_notes.mgdb_message_type=8 AND dw_item.dwin_store=1 AND dwin_display_item_number = %s
//    ORDER BY view_item_notes.mg_group_name, view_item_notes.mx_line_nbr",$sku);
//

	$notes = $db->query('SELECT view_item_notes.mx_text
FROM view_item_notes INNER JOIN dw_item ON view_item_notes.mg_group_name = dw_item.dwin_item_number AND view_item_notes.mgdb_message_type=8 AND dw_item.dwin_store=1 AND dwin_display_item_number = %s 
    ORDER BY view_item_notes.mg_group_name, view_item_notes.mx_line_nbr',$sku);
	foreach ($notes as $row) {

		foreach ($row as $key=>$val){
			//concatenate each row's note value
			//add space to end of each line to ensure a space exists between words
			$note .= $val." ";

		}

	}
//	print '<br/>NOTES:<br/>';
//	print_r($note);
//	print '<br/><br/>';

	//start multiple table scenario
	//todo: count the number of table columns for a table in the $note string
	//todo: This partial multi-table scenario is about half-baked, so revisit after proof of concept

	//count # of tables  (likely always 1, but still check)
//	$table_count = substr_count($note,'<table');
//	print "<br/>table count:".$table_count;
//
//	$table_list = array();
//	//explode the $note
//
//	//if tables exist, find each one and how many rows in that table
//	if($table_count > 0){
//
//		for($x=0; $x < $table_count; $x++){
//
//
//			$partial_table_chunk = get_string_between($note, '<table', '</table>');
//
//			//find number of columns (<th>) in that table chunk
//			$th_count = substr_count($partial_table_chunk,'<th');
//			print "<br/>th count:".$th_count;
//			//count number of table columns (<th>)
//			$table_list[$x]=$th_count;
//
//		}
//
//
//
//
//	}
//	print 'table_list:';
	//print_r($table_list);

	//print 'partial:'.$partial_table; // (result = dog)
//end multiple table scenario
//start single table scenario

	//$partial_table_chunk = get_string_between($note, '<table', '</table>');

	//set to zero for default
	$th_count = 0;
	//find number of columns (<th>) in the entire $note product description
	$th_count = substr_count($note,'<th');
	//print "<br/>th count:".$th_count;


	$tgroup_string = "<tgroup cols='".$th_count."' colsep='0'>";
	//JCD $tgroup_string = "<tgroup><theader cols='".$th_count."' colsep='0'>";

	//for the number of Th counts, add the <colspec>
	for($x=1; $x<=$th_count;$x++){
		$tgroup_string .= "<colspec colnum='".$x."' colname='col".$x."' colwidth='10mm'/>";
	}
	//$tgroup_string = "<tgroup cols='".$th_count."' colsep='0'>";

	//need two table tags per 4/29/19 request

	$tg_open = '<table type="outer"><table>'.$tgroup_string;
//JCD  $tg_open = '<table type="outer"><table>'.$tgroup_string .'</theader>';


	$tg_close = '</tgroup></table></table>';

	$note = str_replace('<table>',$tg_open,$note);
	$note = str_replace('</table>',$tg_close,$note);

	//JCD  more find replace to fix table format  **can find </th>\n*</tr>**
//	$note = str_replace('<tbody>','<thead>', $note);
	//$note = str_replace('</th>\\s*\\n*\\s*</tr>','</th></tr></thead><tbody>', $note);

	//replace <tr> with <row>
//	$note = str_replace('<tr','<row', $note);
	//$note = str_replace('</tr','</row', $note);

	//replace <th> and <td> with <entry> tags per the "englund-JCD.txt" file example
	//extra care to not replace <theader>
	//$note = str_replace('<th ','<entry ',$note);
	//$note = str_replace('</th ','</entry ',$note);

	//$note = str_replace('<th>','<entry>',$note);
	//$note = str_replace('</th>','</entry',$note);

	//$note = str_replace('<td','<entry',$note);
	//$note = str_replace('</td','</entry',$note);

//Get all child products under the product
	$results = $db->query(
			'SELECT * FROM `dw_item` INNER JOIN `IN` ON `dw_item`.`dwin_item_number`=`IN`.`in_item_number` AND `dw_item`.`dwin_store`=`IN`.`in_store` AND `dw_item`.`dwin_store`=1 AND `IN`.`in_store`=1 
			INNER JOIN `view_dw_department` ON `dw_item`.`dwin_store`=`view_dw_department`.`dwde_store_number` AND `dw_item`.`dwin_department`=`view_dw_department`.`dwde_department` AND `view_dw_department`.`dwde_store_number`=1
			INNER JOIN `view_dw_class` ON `dw_item`.`dwin_store`=`view_dw_class`.`dwcl_store` AND `dw_item`.`dwin_class`=`view_dw_class`.`dwcl_class` AND `view_dw_class`.`dwcl_store`=1 
			INNER JOIN `view_dw_fineline` ON `dw_item`.`dwin_store`=`view_dw_fineline`.`dwfi_store` AND `dw_item`.`dwin_fineline`=`view_dw_fineline`.`dwfi_fineline_code` AND `view_dw_fineline`.`dwfi_store`=1
			INNER JOIN `view_dw_vendor` ON `dw_item`.`dwin_store`=`view_dw_vendor`.`dwvm_store` AND `dw_item`.`dwin_primary_vendor`=`view_dw_vendor`.`dwvm_vendor_code` AND `view_dw_vendor`.`dwvm_store`=1
			INNER JOIN `view_dw_manufacturer` ON `dw_item`.`dwin_store`=`view_dw_manufacturer`.`dwvm_store` AND `dw_item`.`dwin_manufacturer`=`view_dw_manufacturer`.`dwvm_vendor_code` AND `view_dw_manufacturer`.`dwvm_store`=1 
			WHERE dwin_display_item_number = %s',$sku);


	//array of fields  to show on the XML output.
	//These are the fields that we really care about!
	$show = array('dwin_display_item_number','dwvm_vendor_name',
			'dwin_department','dwde_dept_name',
			'dwin_class', 'dwcl_class_name',
			'dwin_fineline','dwfi_fineline_name',
			'dwin_primary_vendor', 'dwvm_vendor_name',
			'dwin_manufacturer',
			'dwin_code_c2','in_catalogue_page'  //catalog related fields
	);
//	$show = array();
//print_r($show);



	//Add the 'notes' AKA description to each result array
	foreach ($results as $key=>$row1){

		foreach ($row1 as $key1=>$val1){
			//concatenate each row's note value
			//add space to end of each line to ensure a space exists between words

			if($key1 ==''){  //need an empty key for just the HTML notes
				$note .= $val1." ";

			}
		}
		$results[$key]['item_notes'] = $note ;
	}



	//cycle through each result array to create an XML node for each Product
	foreach($results as $key=>$val) {

		//only add products for items that have an item_type
		if(!empty($product_families)){
			//	print 'im  an array!';

			//add parent element
			$product = $newdoc->createElement('product');

			//rename dwin_display_item_number to product_name
			$product_name = $newdoc->createElement('product_name');
			$product_name->nodeValue = $val['dwin_display_item_number'];
			$product->appendChild($product_name);

			$products->appendChild($product);
			//set the item number as an attribute
			$product->setAttribute("id", $val['dwin_display_item_number']);
			//$dwin_item = $val['dwin_display_item_number'];

			// item type element
			$product_item_type = $newdoc->createElement('item_type');
			$product_item_type->nodeValue = $product_families[$val['dwin_display_item_number']]['type'];

			//class section
			if($val['dwcl_class_name'] != $current_class){
				$dwin_class_section = $newdoc->createElement('class_section');
				$dwin_class_section->nodeValue = htmlspecialchars($val['dwcl_class_name']);
				$product->appendChild($dwin_class_section);

				//now set the current class value to our existing class in our loop
				$current_class = $val['dwcl_class_name'];

			}


			//print "FINELINE:: ".$val['dwin_fineline']." -- " .$val['dwfi_fineline_name'] . "<br/>";



			//fineline section
			if($val['dwfi_fineline_name'] != $current_fineline){
				$dwin_fineline_section = $newdoc->createElement('fineline_section');
				$dwin_fineline_section->nodeValue = htmlspecialchars($val['dwfi_fineline_name']);
				$product->appendChild($dwin_fineline_section);

				//now set the current fineline value to our existing fineline in our loop
				$current_fineline = $val['dwfi_fineline_name'];

			}

			//logo image for manufacturer
			$manuf_image = $newdoc->createElement('logo');
			$logo_location = "ems-fs01/public share/catalog/logos/". $val['dwin_manufacturer']. ".jpg";
			$manuf_image->setAttribute("href", 'file:///' . $logo_location);
			$product->appendChild($manuf_image);


			//add product description
			$product_description = $newdoc->createElement('product_description');
			$product_description->nodeValue = $val['dwin_item_description'];
			$product->appendChild($product_description);

			//append child to Product node
			$product->appendChild($product_item_type);

			//Image
			$product_image = $newdoc->createElement('image');

			$image_location = "ems-fs01/public share/catalog/images/" . "g" . $val['dwin_display_item_number'] . '.jpg';


			$product_image->setAttribute("href", 'file:///' . $image_location);
			$product->appendChild($product_image);

			foreach ($val as $k => $v) {


				//special parsing for HTML descriptions
				if ($k == 'item_notes' && $v != '') {
					//$product->documentElement->appendChild($node);

					$orgdoc = new DOMDocument;
					//load html string
					$orgdoc->loadHTML($v);

					// The node we want to import to a new document
					//get the entire <body> tag, which the loadHTML() adds by default
					$node = $orgdoc->getElementsByTagName("body")->item(0);

					$rp = $product->getAttributeNode($val['dwin_display_item_number']);

					// Import the node, and all its children, to the document
					if ($node != '') {
						$rp = $newdoc->importNode($node, true);
						// And then append it to the "<product>" node
						$product->appendChild($rp);  //this works to put the body node into product node
					}

					/* Start Lists Move from inside body to outside body*/
					//identify the <ul> lists in the <body>
					//get all the <ul> elements

					//***   JEREMY COMMENTED OUT

					//***   */$lists = $node->getElementsByTagName('ul');

					//iterate through the <ul> lists and add them to the <product>
					//*** if ($lists->length > 0) {

//					print'lists::';
//					print_R($lists);
//					print ':end lists';

					//*** 	foreach ($lists as $list) {
					/* add <ul> items to <product> */
					//*** 	$nl = $newdoc->importNode($list, true);
					//append adds node to the <product>
					//*** 	$product->appendChild($nl);
					//*** }

					//now actually remove the lists
					//	$lists = $node->getElementsByTagName('ul');
					//*** if ($lists->length > 0) {
					//set default
					//*** 	$lists_to_remove = array();

					//*** 	foreach ($lists as $list) {
					//make a list of lists to remove (must do it this way)
					//*** 		$lists_to_remove[] = $list;
					//*** 	}
					//*** 	foreach ($lists_to_remove as $key => $lr) {
					//print "<br/>list key:".$key;
					//print_R($lr);
					//*** 	$lr->parentNode->removeChild($lr);

					//*** }

					//*** }
					//*** }
					/* End get all lists */

					// /*Get all tables in body and move them outside of body node*/
					// $tables = $node->getElementsByTagName('table');

					// //iterate through the <ul> lists and add them to the <product>
					// if ($tables->length > 0) {
					// 	foreach ($tables as $table) {

					//		if ($table->hasAttribute('type')) {

					// 			$tabletype = $table->getAttribute('type');

					// 			/* add <table> items to <product> */
					// 			//filtering by type=outer in the table wrapper
					// 			if ($tabletype == 'outer') {
					// 				$nt = $newdoc->importNode($table, true);
					// 				//append adds node to the <product>
					// 				$product->appendChild($nt);

					// 			}

					// 		}

					// 	}

					// 	//now actually remove the tables
					// 	if ($tables->length > 0) {
					// 		$tables_to_remove = array();

					// 		foreach ($tables as $table) {
					// 			//make a list of tables to remove (must do it this way)
					//			$tables_to_remove[] = $table;
					//		}
					// 		foreach ($tables_to_remove as $tr) {
					// 			$tr->parentNode->removeChild($tr);
					// 		}

					// 	}
					// }
					/* End get all tables from body node */


					/* Strip all div nodes from body */
					// $divs = $node->getElementsByTagName('div');
					// if ($divs->length > 0) {

					// 	//set default
					// 	$divs_to_remove = array();

					// 	foreach ($divs as $div) {
					// 		$divs_to_remove[] = $div;
					// 	}
					// 	foreach ($divs_to_remove as $dr) {
					// 		$dr->parentNode->removeChild($dr);
					// 	}

					// }
					/* end div node removal from body */



					//need to replace old body node with new body node
					//get the new version of the body after we've stripped out <ul> and <table> and <div> nodes
					$newnode = $orgdoc->getElementsByTagName("body")->item(0);
					$rpnew = $newdoc->importNode($newnode, true);
					//replace old body node($rp) with new body node($rpnew)
					$product->replaceChild($rpnew, $rp);


				} else {
					//normal parsing for product array
					//add DomDocument Nodes
					if ($k != "dwin_display_item_number") {
						//take all values from array and put into XML DomDocument nodes
						if (in_array($k, $show)) {
							$node = $newdoc->createElement($k);
							//add node Value
							$node->nodeValue = htmlspecialchars($v);

							//append child to Product node
							$product->appendChild($node);

						}

					}
//				$node = $newdoc->createElement($k);
//				//add node Value
//				$node->nodeValue = $v;
//
//				//append child to Product node
//				$product->appendChild($node);


				}


			}
			//	}
			//put subproducts (aka: children) here
			$children = $newdoc->createElement('children');
			//add node Value

			//append child to Product node
			$product->appendChild($children);

			foreach($product_families[$val['dwin_display_item_number']]['children'] as $pk=>$pv){

				$subprod = $product_families[$val['dwin_display_item_number']][$pv];


				$childprod = $newdoc->createElement('subproduct');
				//shoudl be attribute
				$childprod->setAttribute("id", $pv);

				//$childprod->nodeValue = $pv;
				$children->appendChild($childprod);


				//get query for subproducts
				$childnode = $newdoc->createElement('dwin_display_item_number');
				$childnode->nodeValue = $subprod['dwin_display_item_number'];
				$childprod->appendChild($childnode);

				$description = $newdoc->createElement('description');
				$description->nodeValue = $subprod['dwin_item_description'];
				$childprod->appendChild($description);

				$uom = $newdoc->createElement('uom');
				$uom->nodeValue = $subprod['in_purchase_unit'];
				$childprod->appendChild($uom);

				//format list_price to two decimal places
				$list_price = $newdoc->createElement('list_price');
				$list_price->nodeValue = number_format($subprod['in_list_price'],2);
				$childprod->appendChild($list_price);



			}

		}



	}


}

//Save does work
//$filename = 'englund.xml';

if(isset($clean['type'])){
	$filename = '../catalog_xml/englund_'.$clean["type"].'.xml';

}

//save the file
if($newdoc->save($filename)){

	//now open the file back up to do some search and replace
	//function is at the bottom of this page
	post_process_search_replace($filename);



	print '<br/><br/><strong>Generated file saved as '. $filename .'</strong>';

}






function get_string_between($string, $start, $end){
	$string = ' ' . $string;
	$ini = strpos($string, $start);
	if ($ini == 0) return '';
	$ini += strlen($start);
	$len = strpos($string, $end, $ini) - $ini;
	return substr($string, $ini, $len);
}


/*
 * Post file-save search and replace functionality
 * TODO: JEREMY - THIS IS YOUR FUNCTION TO WORK IN
 */
function post_process_search_replace($filename){

	//open the file and read the contents into a string
	$product_text = file_get_contents($filename);
	//close the file
	fclose($fp);


	////// JEREMY START THE MAGIC HERE   ////////

	//proof of concept test to find and replace
	//$product_text = str_ireplace('product_name','Change1',$product_text);
	//$product_text = str_ireplace('class_section','Change2',$product_text);

//$product_text = preg_replace('</th>\\s*\\n*\\s*</tr>','</th></tr></thead><tbody>',$product_text);



//  Generic Fixes - Find and Replace


//"Remove trailing and leading whitespace"
	$product_text = preg_replace("/^\\s*(.*)\\s*$/", "$1", $product_text);

//"Remove blank lines"
	$product_text = preg_replace("/^\\n/", "", $product_text);


//"Remove spaces between tags"
	$product_text = preg_replace("/> +</", "><", $product_text);

//"Add line between products"
	$product_text = preg_replace("/<\/product>/", "</product>\n", $product_text);

//"remove comments"
	$product_text = preg_replace("/<!--.*-->/", "", $product_text);

//"Remove accordion1"
	$product_text = preg_replace("/<div class=\"accordionContent\">/", "", $product_text);

//"Remove accordion2"
	$product_text = preg_replace("/<div class=\"accordionButton\">/", "", $product_text);


//"Remove <div>"
	$product_text = preg_replace("/<div>/", "", $product_text);


//"Remove Click to Expand>"
	$product_text = preg_replace("/\s*\(Click to expand\)/", "", $product_text);

//"Remove </div>"
	$product_text = preg_replace("/<\/div>/", "", $product_text);

//"Remove <div/>"
	$product_text = preg_replace("/<div\/>/", "", $product_text);


//"Replace h6> with strong>""
	$product_text = preg_replace("/h6>/", "strong>", $product_text);

//"remove <body>"
	$product_text = preg_replace("/<body>/", "", $product_text);

//"remove </body>"
	$product_text = preg_replace("/<\/body>/", "", $product_text);

//"remove <a links"
	$product_text = preg_replace("/<a.*<\/a>/", "", $product_text);

//"remove empty <p>"
	$product_text = preg_replace("/<p>\\t*\\s*\\n*\\s*\\t*<\/p>/", "", $product_text);


	//"replace <p>"
	$product_text = preg_replace("/<p>/", "<paragraph>", $product_text);

//"replace </p>"
	$product_text = preg_replace("/<\/p>/", "</paragraph>", $product_text);


//"remove </paragraph></li>"
	$product_text = preg_replace("/<\/paragraph><\/li>/", "</paragraph>", $product_text);

//"remove empty </paragraph>"
	$product_text = preg_replace("/<\/paragraph><\/li>/", "</paragraph>", $product_text);

//"image path to mapped drives"
	$product_text = preg_replace("/ems-fs01\/public share\/catalog/", "L:", $product_text);

//"tech image tag path"
	$product_text = preg_replace("/<img src=\"https:\/\/img2.activant-inet.com\/custom\/022170\/image\//", "<tech href=\"file:///L:/images/", $product_text);

//"image tag backslash path>"
	$product_text = preg_replace("/<image href=\"file:\\\\+ems-fs01\\\\public share\\\\Web\\\\CONTENT\\\\_04 IMAGES LOADED\\\\_01 IMAGES STD\\\\/", "", $product_text);

//open the same filename, but it will be emptied of content
	$fp1 = fopen($filename, "w") or die("Unable to open file!");

//write the text string to file
	fwrite($fp1,$product_text);
//close the file
	fclose($fp1);
//file should continue to exist in its spot, so no action needed

//open the file and read the contents into a string
	$product_text = file_get_contents($filename);
//close the file
	fclose($fp);



	//  Fix LISTS - Find and Replace


//"Isolate end0"
	$product_text = preg_replace("/<\/li>\\t*\\s*\\n*\\s*\\t*<\/ul>/", "<end>\n\t\t", $product_text);

//"Isolate end1""
	$product_text = preg_replace("/<\/ul>\\t*\\s*\\n*\\s*\\t*<table/", "<end>\n\t\t<table", $product_text);

//"Isolate end2"
	$product_text = preg_replace("/<\/ul>\\t*\\s*\\n*\\s*\\t*<children>/", "<end>\n\t\t<children>", $product_text);

//"Isolate end3""
	$product_text = preg_replace("/<strong>/", "<end>\n\t\t<strong>", $product_text);

//"Isolate end4""
	$product_text = preg_replace("/<tech/", "<end>\n\t\t<tech", $product_text);


//"Remove <ul>"
	$product_text = preg_replace("/<ul>/", "", $product_text);

//"Remove </ul>"
	$product_text = preg_replace("/<\/ul>/", "", $product_text);

//"Remove </li>"
	$product_text = preg_replace("/<\/li>/", "", $product_text);

//"Close <li> with </li>"
	$product_text = preg_replace("/<li>/", "</li>\n\t\t<li>", $product_text);

//"Remove </li> from begin1"
	$product_text = preg_replace("/<\/paragraph>\\t*\\s*\\n*\\s*\\t*<\/li>/", "</body>", $product_text);

//"Remove </li> from begin2"
	$product_text = preg_replace("/<\/strong>\\t*\\s*\\n*\\s*\\t*<\/li>/", "</strong>", $product_text);

//"Remove </li> from begin3"
	$product_text = preg_replace("/<\/table>\\t*\\s*\\n*\\s*\\t*<\/li>/", "</table>", $product_text);


//"replace <end> with </li>"
	$product_text = preg_replace("/\\t*\\s*\\n*\\s*\\t*<end>/", "</li>", $product_text);


//"replace </p>"
	$product_text = preg_replace("/<paragraph>(.*)<\/li><strong>/", "<paragraph>$1<strong>", $product_text);

	//"Remove double </li>"
	$product_text = preg_replace("/<\/li>\\t*\\s*\\n*\\s*\\t*<\/li>/", "</li>", $product_text);

//"Remove blanks before close </li>"
	$product_text = preg_replace("/\\t*\\s*\\n*\\s*\\t*<\/li>/", "</li>", $product_text);

//"Remove empty <li> tags"
	$product_text = preg_replace("/<li>\\t*\\s*\\n*\\s*\\t*<\/li>/", "", $product_text);

//"Add blank line &#13;"
	$product_text = preg_replace("/<li>/", "<li>&#13;", $product_text);

//"remove leading &#13; behind <strong>"
	$product_text = preg_replace("/<\/strong>\\t*\\s*\\n*\\s*\\t*<li>&#13;/", "</strong>\n\t\t<li>", $product_text);

//Remove <end> when after <paragraph>
//---$product_text = preg_replace("/<\/paragraph>\\t*\\s*\\n*\\s*\\t*<\/li>/", "</paragraph>", $product_text);

//Remove <end> when after <table>
//---$product_text = preg_replace("/<\/table>\\t*\\s*\\n*\\s*\\t*<\/li>/", "</table>", $product_text);

//Remove <end> when after greater than sign
	$product_text = preg_replace("/>\\t*\\s*\\n*\\s*\\t*<\/li>/", ">", $product_text);



//open the same filename, but it will be emptied of content
	$fp1 = fopen($filename, "w") or die("Unable to open file!");

//write the text string to file
	fwrite($fp1,$product_text);
//close the file
	fclose($fp1);
//file should continue to exist in its spot, so no action needed

//open the file and read the contents into a string
	$product_text = file_get_contents($filename);
//close the file
	fclose($fp);

	//  Fix TABLES - Find and Replace

	$product_text = preg_replace("/<tbody>/", "<thead>", $product_text);
	$product_text = preg_replace("/<\/th>\\t*\\s*\\n*\\s*\\t*<\/tr>/", "</th></tr></thead><tbody>", $product_text);
	$product_text = preg_replace("/<tr>/", "<row>", $product_text);
	$product_text = preg_replace("/<\/tr>/", "</row>", $product_text);
	$product_text = preg_replace("/<th>/", "<entry>", $product_text);
	$product_text = preg_replace("/<\/th>/", "</entry>", $product_text);
	$product_text = preg_replace("/<td>/", "<entry>", $product_text);
	$product_text = preg_replace("/<\/td>/", "</entry>", $product_text);
	$product_text = preg_replace("/<table type=\"outer\">\n<table>/", "<table type=\"outer\"><table>", $product_text);
	$product_text = preg_replace("/<\/table>\n<\/table>/", "<table type=\"outer\"><table", $product_text);

//  Alternate Format to Fix TABLES - Find and Replace

//$patterns = array();
//$patterns[0] = "/<tbody>/";
//$patterns[5] = "/<\/th>\\s*\\n*\\s*<\/tr>/";
//$patterns[10] = "/<tr>/";
//$patterns[15] = "/<\/tr>/";
//$patterns[20] = "/<th>/";
//$patterns[25] = "/<\/th>/";
//$patterns[30] = "/<td>/";
//$patterns[35] = "/<\/td>/";
//$replacements = array();
//$replacements[0] = "<thead>";
//$replacements[5] = "</th></tr></thead><tbody>";
//$replacements[10] = "<row>";
//$replacements[15] = "</row>";
//$replacements[20] = "</entry>";
//$replacements[25] = "<entry>";
//$replacements[30] = "</entry>";
//$replacements[35] = "<entry>";
//ksort($patterns);
//ksort($replacements);
//$product_text = preg_replace($patterns, $replacements, $product_text);


	//////  JEREMY END THE MAGIC HERE   ////////





	//open the same filename, but it will be emptied of content
	$fp1 = fopen($filename, "w") or die("Unable to open file!");

	//write the text string to file
	fwrite($fp1,$product_text);
	//close the file
	fclose($fp1);
	//file should continue to exist in its spot, so no action needed
}

