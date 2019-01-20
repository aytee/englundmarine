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
require_once __DIR__ . '/includes/englundproducts.class.php';
DB::$user = 'englund';
DB::$password = 'englund';
DB::$dbName = 'englund';


// Create a new DOM document  (XML)
$newdoc = new DOMDocument;
$newdoc->formatOutput = true;

//create outer wrapper for all products
$products = $newdoc->createElement('products');
$newdoc->appendChild($products);


	//Build products list
	$eproducts = new EnglundProducts();
	$eproducts->getProductTypeList();

	//holds the product type list array
	$eproducts->product_type_list;
	//print_r($eproducts->product_type_list);



/*General Select */
//$sku = 'RUL10';

//build a list of skus
//TODO: Build this list from another query or a some form input
//$skus = array('BLU11001','RUL10');
$skus = array('RUL-BP12V','RUL37A');

//$results = DB::query("SELECT * FROM dw_item WHERE dwin_item_number = %s",$sku);
////print_r($results);
//$temp_items = array();
//cycle through all the skus
foreach ($skus as $sku){

	$temp_item[$sku] = $eproducts->product_type_list[$sku];

	//cycle through product array and build array


	if($temp_item[$sku]['item_type']=='parent'){
		print "hey, im a parent";
		print_r($temp_item[$sku]);

		//if its a parent, loop through and find all the child skus
		$child_products = array();
		foreach ($eproducts->product_type_list as $item_sku=>$info){

			if($info['item_type']=='child' && $info['cluster']==$sku){
				//it's a child, so grab it
				$child_products[$sku][] = $info['item_number'];

			}

		}

	}elseif($temp_item[$sku]['item_type']=='Regular'){
		//it's a "regular"
		print "hey, im a regular";
		//todo: this doesn't appear correct for regular
		//$child_products[$sku][] = $info['item_number'];

	}else{
		//its a child, so ignore it
	}

//	print_R($temp_item);
	print '<pre>';
	print_R($child_products);
	print '</pre>';


	///////


//initialize $note;
	$note = '';

//this will output a note (aka detailed description) for the product
//each db row is an individual row of the description.
	//This builds an array of all the Production descriptions that is referenced further below
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
			"SELECT dwin_item_number FROM `dw_item` INNER JOIN `in` ON `dw_item`.`dwin_item_number`=`in`.`in_item_number` AND `dw_item`.`dwin_store`=`in`.`in_store` INNER JOIN `view_dw_department` ON `dw_item`.`dwin_store`=`view_dw_department`.`dwde_store_number` AND `dw_item`.`dwin_department`=`view_dw_department`.`dwde_department` INNER JOIN `view_dw_class` ON `dw_item`.`dwin_store`=`view_dw_class`.`dwcl_store` AND `dw_item`.`dwin_class`=`view_dw_class`.`dwcl_class` INNER JOIN `view_dw_fineline` ON `dw_item`.`dwin_store`=`view_dw_fineline`.`dwfi_store` AND `dw_item`.`dwin_fineline`=`view_dw_fineline`.`dwfi_fineline_code`INNER JOIN `view_dw_vendor` ON `dw_item`.`dwin_store`=`view_dw_vendor`.`dwvm_store` AND `dw_item`.`dwin_primary_vendor`=`view_dw_vendor`.`dwvm_vendor_code`INNER JOIN `view_dw_manufacturer` ON `dw_item`.`dwin_store`=`view_dw_manufacturer`.`dwvm_store` AND `dw_item`.`dwin_manufacturer`=`view_dw_manufacturer`.`dwvm_vendor_code` WHERE dwin_item_number = %s",$sku);

	foreach ($results as $key=>$row1){

		//	$notes = DB::query("SELECT mx_text FROM view_item_notes WHERE mg_group_name = %s ORDER BY mx_line_nbr",$sku);  //wasn't doing anything.  //will be important for subproducts


		foreach ($row1 as $key1=>$val1){
			//concatenate each row's note value
			//add space to end of each line to ensure a space exists between words

			if($key1 ==''){  //need an empty key for just the HTML notes
				$note .= $val1." ";

			}

			//	print "xx".$key1.":"  . $val1 . "<br/>";

		}

		$results[$key]['item_notes'] = $note ;

	}



	foreach($results as $key=>$val){
//print_r($val);
		//add parent element
		$product = $newdoc->createElement('product');

		$products->appendChild($product);
		//set the item number as an attribute
		$product->setAttribute("id", $val['dwin_item_number']);
		$dwin_item = $val['dwin_item_number'];

		//todo: Add subproducts here


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

//				$node = $newdoc->createElement($k);
//
//				//add node Value
//				$node->nodeValue = $note;
//				//append child to Product node
//				$product->appendChild($node);

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

print 'index1-success';




