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
$query = 'SELECT IN_EXT.inext_ext_35 FROM IN_EXT WHERE IN_EXT.in_store=1 AND IN_EXT.inext_ext_35<>"" GROUP BY IN_EXT.inext_ext_35';

$dept_list = DB::query($query);

?>

<html>
<head>
    <title>Englund Marine Catalog Data Generation Tool</title>

</head>
<body>
<h1>Englund Marine Catalog Data Generation Tool</h1>

<div style="background-color:lightgrey; padding:10px;">
    <h2> Select Catalog Section</h2>
    <form action="search.php" method="get">
        Catalog Section:
        <select name = "department">
            <option value="" selected>-- Please select one --</option>
					<?php
					//output option list of departments
					foreach($dept_list as $key=>$value){
						$dept_name = $value['inext_ext_35'];
						$dept_code = $value['inext_ext_35'];

						print '<option value="'.$dept_code.'" >'.$dept_name.' </option>';
					}
					?>


        </select><br/><br/>
        <input type="hidden" name="type" value="all"/>

        <input type="submit" value="Submit"/>

    </form>

</div>
</body>


</html>