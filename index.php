<?php



//include the settings file or throw a notice
if(file_exists('../permanent/settings.php')){
	include '../permanent/settings.php';
}else{
	print "<strong>You are missing the settings.php file in the /permanent directory</strong>";

}

/* Include the meekro DB class */
require_once __DIR__ . '/includes/meekrodb.2.3.class.php';
//$host=null, $user=null, $password=null, $dbName=null, $port=null,

//primary DB
$db = new MeekroDB($db_host, $db_user, $db_password,$db_name,$db_port);

//secondary datasource
$db2 = new MeekroDB($db2_host, $db2_user, $db2_password,$db2_name,$db2_port);


require_once __DIR__ . '/includes/englundproducts.class.php';

//build section temp table
$eproducts = new EnglundProducts();

$eproducts->db = $db;
$eproducts->db2 = $db2;
$eproducts->buildSectionTable();

$section_list = $db2->query('SELECT * FROM catalog_section');



//get list of departments
//not using departments any longer
//$query = 'SELECT * FROM view_dw_department WHERE view_dw_department.dwde_store_number="1" AND view_dw_department.dwde_non_merchandise_flag="N" AND view_dw_department.dwde_department BETWEEN "A" AND "Z" ';
//$dept_list = DB::query($query);


?>

<html>
<head>
    <title>Englund Marine Catalog Data Generation Tool</title>

</head>
<body>
<h1>Englund Marine Catalog Data Generation Tool</h1>

<div style="background-color:lightgrey; padding:10px;">
    <h2> Select the Section</h2>
    <form action="search.php" method="get">
<!--        Department:-->
<!--        <select name = "department">-->
<!--            <option value="" selected>-- Please select one --</option>-->
<!--					--><?php
//					//output option list of departments
//					foreach($dept_list as $key=>$value){
//						$dept_name = $value['dwde_dept_name'];
//						$dept_code = $value['dwde_department'];
//
//						print '<option value="'.$dept_code.'" >'.$dept_name.' </option>';
//					}
//					?>
<!---->
<!---->
<!--        </select><br/><br/>-->


        Section:
        <select name = "section">
            <option value="" selected>-- Please select one --</option>
					<?php
					//output option list of departments
					foreach($section_list as $key=>$value){
						$section_id = $value['id'];
						$section_name = $value['section_name'];

						print '<option value="'.$section_id.'" >'.$section_name.' </option>';
					}
					?>


        </select><br/><br/>




        <input type="hidden" name="type" value="all"/>

        <input type="submit" value="Submit"/>

    </form>

</div>
</body>


</html>