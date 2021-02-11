<?php
ob_start(); //save output of any data till the end. Prevents errors with headers already sent.
//PDO is php data objects
try {
    //GRANT ALL ON google_duck_test.* TO 'alex'@'localhost' IDENTIFIED BY 'my_pass';
    //GRANT ALL ON google_duck_test.* TO 'alex'@'127.0.0.1' IDENTIFIED BY 'my_pass';
    $connection = new PDO('mysql:host=localhost;port=3307;dbname=google_duck_test', 'alex', 'my_pass');
    //will show error message but continue execution
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
} catch (PDOException $e) { //exception will be of type PDOException
    echo "Connection failed: " . $e->getMessage();
}
