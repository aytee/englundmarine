<?php

class EnglundProducts
{

	public $product_type_list;
	public $product_type_list_solo;


	function getProductTypeList()
	{

		//build array of product and product types
		//This will identify a particular product as a parent or child

		$list = DB::query('SELECT dw_item.dwin_display_item_number AS item_number, view_representative_items.dwin_display_item_number AS cluster,
      if(sgir_item_number IS NULL,"Regular", if(dw_item.dwin_display_item_number=view_representative_items.dwin_display_item_number,"parent","child")) AS item_type
      FROM ((dw_item LEFT JOIN SGIR ON dw_item.dwin_item_number = SGIR.sgir_item_number)
      LEFT JOIN view_representative_items ON SGIR.sgir_representative_item = view_representative_items.dwin_item_number)
      LEFT JOIN dw_item AS dw_item_1 ON view_representative_items.dwin_item_number = dw_item_1.dwin_item_number');

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




