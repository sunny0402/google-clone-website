<?php
class ImageResultsProvider
{
    private $connection;

    //connection variable will be stored as a class property
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function getNumResults($term)
    {

        $query = $this->connection->prepare("SELECT COUNT(*) as total
                                            FROM images WHERE (title LIKE :term
                                            OR alt LIKE :term)
                                            AND broken =0"); //broken column to mark broken link to image
        //%a_search_term% when using LIKE in mysql                                   
        $searchTerm = "%" . $term . "%";
        $query->bindParam(":term", $searchTerm);
        $query->execute();
        //store results in associative array (key/value)
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row["total"];
    }

    //current page, page size is num results, search term
    public function getResultsHtml($page, $pageSize, $term)
    {
        //start from $fromLimit
        //page 1: (1-1)*20 = 0
        //page 2: (2-1)*20 = 20
        $fromLimit = ($page - 1) * $pageSize;

        $query = $this->connection->prepare("SELECT *
                                            FROM images WHERE (title LIKE :term
                                            OR alt LIKE :term)
                                            AND broken =0
										    ORDER BY clicks DESC
										    LIMIT :fromLimit, :pageSize");
        //select the :fromLimit and display the next :pageSize number of results

        //% signifies characters before and afte search term
        $searchTerm = "%" . $term . "%";
        $query->bindParam(":term", $searchTerm);
        $query->bindParam(":fromLimit", $fromLimit, PDO::PARAM_INT); //PDO::PARAM_INT specify that this value is an int
        $query->bindParam(":pageSize", $pageSize, PDO::PARAM_INT);
        $query->execute();

        //apply masonry layout to this div
        //https://masonry.desandro.com/
        $resultsHtml = "<div class='imageResults'>";

        $count = 0; //for every image a different count value
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $count++;
            $id = $row["id"];
            $siteUrl = $row["siteUrl"];
            $imageUrl = $row["imageUrl"];
            $title = $row["title"];
            $alt = $row["alt"];

            if ($title) {
                $displayText = $title;
            } else if ($alt) {
                $displayText = $alt;
            } else {
                $displayText = $imageUrl;
            }
            //load image$count class  to div and javascript fn loadImage()
            //data-siteurl has to be lowercase to work with fancybox
            $resultsHtml .= "<div class='gridItem image$count'>
                <a href='$imageUrl' data-fancybox data-caption='$displayText'
                data-siteurl='$siteUrl'>
                <script>
                $(document).ready(function (){
                 loadImage(\"$imageUrl \",\"image$count \");       
                });
                </script>
 
                <span class='details'>$displayText</span>
                </a>

            </div>";
        }


        $resultsHtml .= "</div>";
        //.= is to append to a string
        return $resultsHtml;
    }
}
