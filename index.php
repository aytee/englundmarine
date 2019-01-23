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

	//holds the product type list array of parent / child products
	//$eproducts->product_type_list;

  //holds the product list of "regular" products
	//$eproducts->product_type_list_solo;

	//print_r($eproducts->product_type_list);



/*General Select */
//$sku = 'RUL10';

//build a list of skus
//TODO: Build this list from another query or a some form input
//$skus = array('BLU11001','RUL10');
$skus = array('RUL37A','RUL-BP12V','BLU5063','BLU-ESBS');

//$results = DB::query("SELECT * FROM dw_item WHERE dwin_item_number = %s",$sku);
////print_r($results);
//$temp_items = array();
//cycle through all the skus
foreach ($skus as $sku){

	//$temp_item[$sku] = $eproducts->product_type_list[$sku];

	//cycle through product array and build array

	//is product sku a "regular" item
	if(array_key_exists($sku, $eproducts->product_type_list_solo)){
		//print "hey, im a regular:".$sku."<br/>";
		$product_families[$sku]['type'] = 'regular';
		$product_families[$sku]['children'][]= $sku;  //should only ever be a single child for 'regular' item
	//	print_r( $eproducts->product_type_list_solo[$sku]);

	}else{
		//product is a child or parent

		//$child_products = array();
		$product_families = array();
		//$temp_prod = $eproducts->product_type_list;

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



//	if($temp_item[$sku]['item_type']=='parent'){
//		print "hey, im a parent";
//		print_r($temp_item[$sku]);
//
//		//if its a parent, loop through and find all the child skus
//		$child_products = array();
//		foreach ($eproducts->product_type_list as $item_sku=>$info){
//
//			if($info['item_type']=='child' && $info['cluster']==$sku){
//				//it's a child, so grab it
//				$child_products[$sku][] = $info['item_number'];
//
//			}
//
//		}
//
//	}elseif($temp_item[$sku]['item_type']=='Regular'){
//		//it's a "regular"
//		print "hey, im a regular";
//		//todo: this doesn't appear correct for regular
//		//$child_products[$sku][] = $info['item_number'];
//		//$child_products[$info['item_number']][] = $info['item_number'];
//
//	}else{
//		//its a child, so ignore it
//	}

//	print_R($temp_item);
	print '<pre>';
	print_R($product_families);
	print '</pre>';

	foreach ($product_families as $pid => $pdata){
		foreach($pdata['children'] as $psid => $subid){




			$subproducts_results = DB::query(
					"SELECT * FROM `dw_item` INNER JOIN `in` ON `dw_item`.`dwin_item_number`=`in`.`in_item_number` AND `dw_item`.`dwin_store`=`in`.`in_store` INNER JOIN `view_dw_department` ON `dw_item`.`dwin_store`=`view_dw_department`.`dwde_store_number` AND `dw_item`.`dwin_department`=`view_dw_department`.`dwde_department` INNER JOIN `view_dw_class` ON `dw_item`.`dwin_store`=`view_dw_class`.`dwcl_store` AND `dw_item`.`dwin_class`=`view_dw_class`.`dwcl_class` INNER JOIN `view_dw_fineline` ON `dw_item`.`dwin_store`=`view_dw_fineline`.`dwfi_store` AND `dw_item`.`dwin_fineline`=`view_dw_fineline`.`dwfi_fineline_code`INNER JOIN `view_dw_vendor` ON `dw_item`.`dwin_store`=`view_dw_vendor`.`dwvm_store` AND `dw_item`.`dwin_primary_vendor`=`view_dw_vendor`.`dwvm_vendor_code`INNER JOIN `view_dw_manufacturer` ON `dw_item`.`dwin_store`=`view_dw_manufacturer`.`dwvm_store` AND `dw_item`.`dwin_manufacturer`=`view_dw_manufacturer`.`dwvm_vendor_code` WHERE dwin_display_item_number = %s",$subid);

			foreach ($subproducts_results as $subkey=>$subrow1) {

				foreach($subrow1 as $subk =>$subv){
				//	print "<br/>".$subk.":"  . $subv . "<br/>";
					//$product_families[$pid]['children'][$psid][$subk]= (string)$subv;
					$product_families[$pid][$subid][$subk]= $subv;

				}



			}



		}


	}
	//todo: take $product_families and run
//	print '<pre>';
//
//	print_R($product_families);
//	print '</pre>';


	//By the time we get here, we have built an entire nested array of products and subproducts

	///////
//

//initialize $note;
	$note = '';

//this will output a note (aka detailed description) for the product
//each db row is an individual row of the description.
	//This builds an array of all the Production descriptions that is referenced further below
	//must link with the foreign key view_item_notes.mg_group_name = dw_item.dwin_item_number
	//$notes = DB::query("SELECT mx_text FROM view_item_notes WHERE mg_group_name = %s ORDER BY mx_line_nbr",$sku);
	$notes = DB::query("SELECT dw_item.dwin_display_item_number, view_item_notes.mx_text
FROM view_item_notes INNER JOIN dw_item ON view_item_notes.mg_group_name = dw_item.dwin_item_number
WHERE  view_item_notes.mgdb_message_type=8 AND dw_item.dwin_store=1 AND dwin_display_item_number = %s
    ORDER BY view_item_notes.mg_group_name, view_item_notes.mx_line_nbr",$sku);

	foreach ($notes as $row) {

		foreach ($row as $key=>$val){
			//concatenate each row's note value
			//add space to end of each line to ensure a space exists between words
			$note .= $val." ";

		}

	}


	//TODO: get all child products under the product
	$results = DB::query(
			"SELECT * FROM `dw_item` INNER JOIN `in` ON `dw_item`.`dwin_item_number`=`in`.`in_item_number` AND `dw_item`.`dwin_store`=`in`.`in_store` INNER JOIN `view_dw_department` ON `dw_item`.`dwin_store`=`view_dw_department`.`dwde_store_number` AND `dw_item`.`dwin_department`=`view_dw_department`.`dwde_department` INNER JOIN `view_dw_class` ON `dw_item`.`dwin_store`=`view_dw_class`.`dwcl_store` AND `dw_item`.`dwin_class`=`view_dw_class`.`dwcl_class` INNER JOIN `view_dw_fineline` ON `dw_item`.`dwin_store`=`view_dw_fineline`.`dwfi_store` AND `dw_item`.`dwin_fineline`=`view_dw_fineline`.`dwfi_fineline_code`INNER JOIN `view_dw_vendor` ON `dw_item`.`dwin_store`=`view_dw_vendor`.`dwvm_store` AND `dw_item`.`dwin_primary_vendor`=`view_dw_vendor`.`dwvm_vendor_code`INNER JOIN `view_dw_manufacturer` ON `dw_item`.`dwin_store`=`view_dw_manufacturer`.`dwvm_store` AND `dw_item`.`dwin_manufacturer`=`view_dw_manufacturer`.`dwvm_vendor_code` WHERE dwin_display_item_number = %s",$sku);


	//array of items to show
	$show = array('dwin_display_item_number','dwvm_vendor_name');



	foreach ($results as $key=>$row1){

		foreach ($row1 as $key1=>$val1){
			//concatenate each row's note value
			//add space to end of each line to ensure a space exists between words

			if($key1 ==''){  //need an empty key for just the HTML notes
				$note .= $val1." ";

			}

				//print "xx".$key1.":"  . $val1 . "<br/>";

		}
		$results[$key]['item_notes'] = $note ;

	}

	foreach($results as $key=>$val){
	//	print '<br/>'.$key.'::';
	//	print_r($val);
		//add parent element
		$product = $newdoc->createElement('product');

		$products->appendChild($product);
		//set the item number as an attribute
		$product->setAttribute("id", $val['dwin_display_item_number']);
		$dwin_item = $val['dwin_display_item_number'];

		// item type element
		$product_item_type = $newdoc->createElement('item_type');
		$product_item_type->nodeValue = $product_families[$val['dwin_display_item_number']]['type'];

		//append child to Product node
		$product->appendChild($product_item_type);




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

				$rp = $product->getAttributeNode($val['dwin_display_item_number']);
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
				if(in_array($k,$show)){
					$node = $newdoc->createElement($k);
					//add node Value
					$node->nodeValue = $v;

					//append child to Product node
					$product->appendChild($node);

				}


//				$node = $newdoc->createElement($k);
//				//add node Value
//				$node->nodeValue = $v;
//
//				//append child to Product node
//				$product->appendChild($node);


			}



		}


		//todo: put subproducts (aka: children) here
		$children = $newdoc->createElement('children');
		//add node Value
	//	$children->nodeValue = "whoa";

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

			$list_price = $newdoc->createElement('list_price');
			$list_price->nodeValue = $subprod['in_list_price'];
			$childprod->appendChild($list_price);



		}




	}

	//Save does work
	$newdoc->save('englund1.xml');

}

print 'index1-success';




