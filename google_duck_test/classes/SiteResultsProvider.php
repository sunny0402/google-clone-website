<?php
class SiteResultsProvider
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
                                            FROM sites WHERE title LIKE :term
                                            OR url LIKE :term
                                            OR keywords LIKE :term
                                            OR description LIKE :term");
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
										 FROM sites WHERE title LIKE :term 
										 OR url LIKE :term 
										 OR keywords LIKE :term 
										 OR description LIKE :term
										 ORDER BY clicks DESC
										 LIMIT :fromLimit, :pageSize");
        //select the :fromLimit and display the next :pageSize number of results

        //% signifies characters before and afte search term
        $searchTerm = "%" . $term . "%";
        $query->bindParam(":term", $searchTerm);
        $query->bindParam(":fromLimit", $fromLimit, PDO::PARAM_INT); //PDO::PARAM_INT specify that this value is an int
        $query->bindParam(":pageSize", $pageSize, PDO::PARAM_INT);
        $query->execute();


        $resultsHtml = "<div class='siteResults'>";


        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $id = $row["id"];
            $url = $row["url"];
            $title = $row["title"];
            $description = $row["description"];

            $title = $this->trimField($title, 55); //max 55 chars to display
            $description = $this->trimField($description, 230);

            $resultsHtml .= "<div class='resultContainer'>

								<h3 class='title'>
									<a class='result' href='$url' data-linkId='$id'>
										$title
									</a>
								</h3>
								<span class='url'>$url</span>
								<span class='description'>$description</span>

							</div>";
        }


        $resultsHtml .= "</div>";
        //.= is to append to a string
        return $resultsHtml;
    }
    //only call this fn inside SiteResultsProvider class
    private function trimField($string, $characterLimit)
    {
        //if length of string greater than char limit
        $dots = strlen($string) > $characterLimit ? "....." : "";
        return substr($string, 0, $characterLimit) . $dots;
    }
}
