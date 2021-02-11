<?php
// cause in ajax folder go up one folder to find config
include "../config.php";

if (isset($_POST["linkId"])) {
    //query will update the clicks count in the sites table of the id specified by  $_POST["linkId"]
    $query = $connection->prepare("UPDATE sites SET clicks = clicks + 1 WHERE id=:id");
    $query->bindParam(":id", $_POST["linkId"]);
    $query->execute();
} else {
    echo "no link passed to page";
}
