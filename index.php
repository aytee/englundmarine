<?php


//include the settings file or throw a notice
if(file_exists('../permanent/settings.php')){
	include '../permanent/settings.php';
}else{
    print "<strong>You are missing the settings.php file in the /permanent directory</strong>";

}


/* Include the meekro DB class */
require_once __DIR__ . '/includes/meekrodb.2.3.class.php';
DB::$user = $db_name;
DB::$password = $db_user;
DB::$dbName = $db_password;

//get list of departments
$query = 'SELECT * FROM view_dw_department';

$list = DB::query($query);

print_r($list);



?>


<html>
    <head>
        <title>Englund Marine Catalog Data Generation Tool</title>

    </head>
	<body>
        <h1>Englund Marine Catalog Data Generation Tool</h1>

        <p><a href="search.php?type=short">Just the Four items</a> </p>
        <p><a href="search.php?type=blu">Items = "BLU"</a> </p>
        <p><a href="search.php?type=all">All items</a> </p>


        /////
        <p>Or Select your Department</p>
        <form>
            <select name = "department">
              <?php
              //output option list of departments
              foreach($list as $key=>$value){
                  $dept_name = $value['dwde_dept_name'];
                  $dept_code = $value['dwde_department'];

                  print '<option value="'.$dept_code.'" >'.$dept_name.' </option>';
              }
              ?>


            </select>


        </form>


	</body>




</html>