<?php
/**
 * Created by PhpStorm.
 * User: arthurturlak
 * Date: 12/19/18
 * Time: 11:30 AM
 */
print 'hellow';

/*Composer autoload*/
require __DIR__ . '/vendor/autoload.php';

/* Include the meekro DB class */
require_once __DIR__ . '/includes/meekrodb.2.3.class.php';
DB::$user = 'englund';
DB::$password = 'englund';
DB::$dbName = 'englund';

$results = DB::query("SELECT * FROM view_dw_class");
//print_r($results);

foreach ($results as $row) {
//	echo "Name: " . $row['name'] . "\n";
//	echo "Age: " . $row['age'] . "\n";
//	echo "Height: " . $row['height'] . "\n";
//	echo "-------------\n";

print_R($row);
print "<br/>";

}