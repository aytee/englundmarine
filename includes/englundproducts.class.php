<?php

class EnglundProducts
{

	public $product_type_list;
	public $product_type_list_solo;


	public $department;

	function getProductTypeList()
	{

		if(!isset($this->department)){
			$this->department = '';
		}
		print 'searching by Department code::::';
		print_r($this->department);
		print '<br/><br/>';
		//build array of product and product types
		//This will identify a particular product as a parent or child



		$query =

				'SELECT dw_item.dwin_display_item_number AS item_number, view_representative_items.dwin_display_item_number AS cluster,
       if(sgir_item_number IS NULL,"Regular", if(dw_item.dwin_display_item_number=view_representative_items.dwin_display_item_number,"parent","child")) AS item_type
       FROM dw_item 
	   INNER JOIN `IN` ON `dw_item`.`dwin_item_number`=`IN`.`in_item_number` AND `dw_item`.`dwin_store`=`IN`.`in_store` AND `dw_item`.`dwin_store`=1 AND `IN`.`in_store`=1 AND `dw_item`.`dwin_department`="CT" 
	   INNER JOIN `view_dw_department` ON `dw_item`.`dwin_store`=`view_dw_department`.`dwde_store_number` AND `dw_item`.`dwin_department`=`view_dw_department`.`dwde_department` AND dw_item.dwin_store=1 
	   LEFT JOIN SGIR ON dw_item.dwin_item_number = SGIR.sgir_item_number AND dw_item.dwin_store=1 
       LEFT JOIN view_representative_items ON SGIR.sgir_representative_item = view_representative_items.dwin_item_number
       LEFT JOIN dw_item AS dw_item_1 ON view_representative_items.dwin_item_number = dw_item_1.dwin_item_number AND dw_item_1.dwin_item_number=1
       WHERE `IN`.`in_catalogue_page` Between "0000" And "2000" Or `IN`.`in_catalogue_page`="ADD" ' ;


		if($this->department !=''){
			$query .= 'AND `view_dw_department`.`dwde_department` = %s  ';

		}
//print_r($query);
		$list = DB::query($query,$this->department);
//print_r($list);
		foreach ($list as $row) {
//			$this->product_type_list[$row['item_number']] = array(
//					'item_number'=> $row['item_number'],
//					'cluster'=> $row['cluster'],
//					'item_type'=> $row['item_type'],
//
//			);

			if (is_null($row['cluster'])) {
				//regular product (not a parent/child)
				//print '<br/>im null:.'.$row['item_number'];
				$this->product_type_list_solo[$row['item_number']] = array(
						'item_number' => $row['item_number'],
						'cluster' => $row['cluster'],
						'item_type' => $row['item_type'],

				);


			} else {
				//either a parent or child
				$this->product_type_list[$row['item_number']] = array(
						'item_number' => $row['item_number'],
						'cluster' => $row['cluster'],
						'item_type' => $row['item_type'],

				);


			}


		}

	}

}




