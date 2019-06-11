<?php



//include the settings file or throw a notice
if(file_exists('../permanent/settings.php')){
	include '../permanent/settings.php';
}else{
    print "<strong>You are missing the settings.php file in the /permanent directory</strong>";

}




/* Include the meekro DB class */
require_once __DIR__ . '/includes/meekrodb.2.3.class.php';
DB::$dbName = $db_name;   //database name
DB::$user = $db_user;     //user name
DB::$password = $db_password;   //user password
DB::$port = $db_port;   //usually 3306
DB::$host = $db_host;  //IP or localhost

//get list of departments

$query = 'SELECT * FROM view_dw_department WHERE view_dw_department.dwde_store_number="1"  ';

$dept_list = DB::query($query);




?>


<html>
    <head>
        <title>Englund Marine Catalog Data Generation Tool</title>

    </head>
	<body>
        <h1>Englund Marine Catalog Data Generation Tool</h1>
<!---->
<!--        <p><a href="search.php?type=short">Just the Four items</a> </p>-->
<!--        <p><a href="search.php?type=blu">Items = "BLU"</a> </p>-->
<!--        <p><a href="search.php?type=all">All items</a> </p>-->

        <div style="background-color:lightgrey; padding:10px;">
        <h2> Select the Department</h2>
        <form action="search.php" method="get">
            Department:
            <select name = "department">
              <option value="" selected>-- Please select one --</option>
              <?php
              //output option list of departments
              foreach($dept_list as $key=>$value){
                  $dept_name = $value['dwde_dept_name'];
                  $dept_code = $value['dwde_department'];

                  print '<option value="'.$dept_code.'" >'.$dept_name.' </option>';
              }
              ?>


            </select><br/><br/>
            <input type="hidden" name="type" value="all"/>
<!--            <p>Search Type:-->
<!--            <select name="type">-->
<!--                <option value="all" selected>All Products</option>-->
<!--                <option value="short" >Just the Four Items</option>-->
<!--                <option value="blue" >Items = 'BLU'</option>-->
<!---->
<!--            </select>-->
<!---->
<!--            </p>-->





            <input type="submit" value="Submit"/>

        </form>

        </div>
	</body>




</html>