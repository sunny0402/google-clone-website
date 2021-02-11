<?php
// cause in ajax folder go up one folder to find config
include "../config.php";

if (isset($_POST["imageUrl"])) {
    //query will update the clicks count in the sites table of the id specified by  $_POST["linkId"]
    $query = $connection->prepare("UPDATE images SET clicks = clicks + 1 WHERE imageUrl=:imageUrl");
    $query->bindParam(":imageUrl", $_POST["imageUrl"]);
    $query->execute();
} else {
    echo "no image URL passed to page";
}
