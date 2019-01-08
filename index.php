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

// Create a new DOM document  (XML)
$newdoc = new DOMDocument;
$newdoc->formatOutput = true;

//create outer wrapper for all products
$products = $newdoc->createElement('products');
$newdoc->appendChild($products);

/*General Select */
//$sku = 'RUL10';

//build a list of skus
//TODO: Build this list from another query or a some form input
$skus = array('BLU11001','RUL10');

//$results = DB::query("SELECT * FROM dw_item WHERE dwin_item_number = %s",$sku);
////print_r($results);

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

	}


//<xmp> tags will show raw html in the browser
//	print 'Raw note:';
//	print '<xmp>';
//print_R($note);
//	print '</xmp>';

//print_R($note);
	//TODO: get all child products under the product
	$results = DB::query(
			"SELECT * FROM `dw_item` INNER JOIN `in` ON `dw_item`.`dwin_item_number`=`in`.`in_item_number` AND `dw_item`.`dwin_store`=`in`.`in_store` INNER JOIN `view_dw_department` ON `dw_item`.`dwin_store`=`view_dw_department`.`dwde_store_number` AND `dw_item`.`dwin_department`=`view_dw_department`.`dwde_department` INNER JOIN `view_dw_class` ON `dw_item`.`dwin_store`=`view_dw_class`.`dwcl_store` AND `dw_item`.`dwin_class`=`view_dw_class`.`dwcl_class` INNER JOIN `view_dw_fineline` ON `dw_item`.`dwin_store`=`view_dw_fineline`.`dwfi_store` AND `dw_item`.`dwin_fineline`=`view_dw_fineline`.`dwfi_fineline_code`INNER JOIN `view_dw_vendor` ON `dw_item`.`dwin_store`=`view_dw_vendor`.`dwvm_store` AND `dw_item`.`dwin_primary_vendor`=`view_dw_vendor`.`dwvm_vendor_code`INNER JOIN `view_dw_manufacturer` ON `dw_item`.`dwin_store`=`view_dw_manufacturer`.`dwvm_store` AND `dw_item`.`dwin_manufacturer`=`view_dw_manufacturer`.`dwvm_vendor_code` WHERE dwin_item_number = %s",$sku);

	foreach ($results as $key=>$row1){

		//$notes = DB::query("SELECT mx_text FROM view_item_notes WHERE mg_group_name = %s ORDER BY mx_line_nbr",$sku);  //wasn't doing anything.  //will be important for subproducts


		foreach ($row1 as $key1=>$val1){
			//concatenate each row's note value
			//add space to end of each line to ensure a space exists between words

			if($key1 ==''){  //need an empty key for just the HTML notes
				$note .= $val1." ";

			}

			//	print "xx".$key1.":"  . $val1 . "<br/>";

		}

		//todo: we are still getting all the non-note Values shoved into the $note var
//		print 'line95';
//		print_r($note);
//		print 'endline95';
		//	$results[$key]['item_notes_concat'] = '<xmp>'.$note.'</xmp>';
		$results[$key]['item_notes'] = $note;

	}



//	print'blah<pre>';
//	print_R($results[$key]['item_notes']);
//	print'</pre>endblah';


	foreach($results as $key=>$val){
//print_r($val);
		//add parent element
		$product = $newdoc->createElement('product');

		$products->appendChild($product);
		//set the item number as an attribute
		$product->setAttribute("id", $val['dwin_item_number']);
		$dwin_item = $val['dwin_item_number'];

		foreach ($val as $k=>$v){



			//special parsing for HTML descriptions
			if($k=='item_notes'){
				//$product->documentElement->appendChild($node);

				$orgdoc = new DOMDocument;
				//load html string
				$orgdoc->loadHTML($v);

				// The node we want to import to a new document
				//get the entire <body> tag, which the loadHTML() adds by default
				$node = $orgdoc->getElementsByTagName("body")->item(0);

				$rp = $product->getAttributeNode($val['dwin_item_number']);
				// Import the node, and all its children, to the document
				$rp = $newdoc->importNode($node, true);
				// And then append it to the "<product>" node
				$element = $newdoc->createElement('item_notes');

				$product->appendChild($rp);  //this works!! to put the body
				//todo: would like a child node instead of just <body>.  Like <item_notes>





			}else{
				//normal parsing for product array
				//add DomDocument Nodes
				$node = $newdoc->createElement($k);

				//add node Value
				$node->nodeValue = $v;

				//append child to Product node
				$product->appendChild($node);


			}

		}


	}

	//Save does work
	$newdoc->save('englund1.xml');

}





