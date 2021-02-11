<?php
include("classes/DomDocumentParser.php");
include("config.php");

$alreadyCrawled = array();
$crawling = array();
$alreadyFoundImages = array();

function linkExists($url)
{
	global $connection;
	//do not insert into id and clicks
	$query = $connection->prepare("SELECT * from sites WHERE url = :url");
	$query->bindParam(":url", $url);
	$query->execute();

	return $query->rowCount() != 0; //number of rows that query returned
}

function insertLink($url, $title, $description, $keywords)
{
	global $connection;
	//do not insert into id and clicks
	$query = $connection->prepare("INSERT INTO sites (url,title,description,keywords)
	VALUES (:url,:title,:desc,:keyw)");
	//bind the placeholders to the values
	$query->bindParam(":url", $url);
	$query->bindParam(":title", $title);
	$query->bindParam(":desc", $description);
	$query->bindParam(":keyw", $keywords);

	return $query->execute();
}

//$src is the url of the image. $url so that can go to site that image was found on
function insertImage($url, $src, $alt, $title)
{
	global $connection;
	//do not insert into id and clicks
	$query = $connection->prepare("INSERT INTO images (siteUrl,imageUrl,alt,title)
	VALUES (:siteUrl, :imageUrl, :alt,:title )");
	//bind the placeholders to the values
	$query->bindParam(":siteUrl", $url);
	$query->bindParam(":imageUrl", $src);
	$query->bindParam(":alt", $alt);
	$query->bindParam(":title", $title);

	return $query->execute();
}


//convert relative links to actual links
//$src is the relative link found on the page we are crawling
function createLink($src, $url)
{
	// echo "SRC: $src<br>";
	// echo "URL: $url<br>";

	//scheme ( http or https) | host (www.google.com)
	$scheme = parse_url($url)["scheme"];
	$host = parse_url($url)["host"];
	if (substr($src, 0, 2) == "//") {
		//$src is the relative link
		//so concatenate the schemre( http or https) with colon and the relative link
		$src = $scheme . ":" . $src;
	} else if (substr($src, 0, 1) == "/") {
		$src = $scheme . "://" . $host;
	}
	// chek if ./somefolder/somefile (./ means start in current directory)
	//substr($src, 1) to ignore the dot
	else if (substr($src, 0, 2) == "./") {
		$src = $scheme . "://" . $host . dirname(parse_url($url)["path"]) . substr($src, 1);
	}
	// chek if ../somefolder/somefile (../ means go to previous directory)
	// will now have /../
	else if (substr($src, 0, 3) == "../") {
		$src = $scheme . "://" . $host . $src;
	}
	//is the relative url something like about/aboutUS.php
	//make sure that no full url already
	else if (substr($src, 0, 5) != "https" && substr($src, 0, 4) != "http") {
		$src = $scheme . "://" . $host . $src;
	}
	return $src;
}

function getDetails($url)
{
	global $alreadyFoundImages;
	//will call the DomDocumentParser
	$my_crawl = new DomDocumentParser($url);
	//every website should have title and meta tags of description and keyword

	//should only be one title but possiblity that more
	$title_array = $my_crawl->getTitleTags();

	//Don't crawl websites without title.
	if (sizeof($title_array) == 0 || $title_array->item(0) == NULL) {
		return;
	}

	$title = $title_array->item(0)->nodeValue;
	$title = str_replace("\n", "", $title);
	if ($title == "") {
		return;
	}
	$description = "";
	$keywords = "";

	$metaArray = $my_crawl->getMetaTags();
	foreach ($metaArray as $meta) {
		//Example: Get the value of the class attribute of an <h1> element
		//var x = document.getElementsByTagName("H1")[0].getAttribute("class");
		if ($meta->getAttribute("name") == "description") {
			$description = $meta->getAttribute("content");
		}

		if ($meta->getAttribute("name") == "keywords") {
			$keywords = $meta->getAttribute("content");
		}
	}

	$description = str_replace("\n", "", $description);
	$keywords = str_replace("\n", "", $keywords);

	// echo "URL: $url, Title: $title, Description: $description, Keywords: $keywords <br>";

	//insert into database
	if (linkExists($url)) { //returns True if linkExists
		echo "$url already exists: " . "<br>";
		// next run function insertLink()
		// insertLink returns true if query executed
	} else if (insertLink($url, $title, $description, $keywords)) { //if succesfully insert echo the below
		echo "SUCCESS: $url" . "<br>";
	} else { //if get here insertLink failed
		echo "ERROR: Failed to insert $url" . "<br>";
	}

	$imagesArray = $my_crawl->getImages();
	foreach ($imagesArray as $image) {
		$src = $image->getAttribute("src");
		$alt = $image->getAttribute("alt");
		$img_title = $image->getAttribute("title");

		//require at least one of these. If doesn't have both do nothing and continue with next iteration of loop.
		if (!$img_title && !$alt) {
			continue;
		}
		//take relative link of image and with createLink create full path.
		$src = createLink($src, $url);

		//if first time come across this image insert it into the array
		if (!in_array($src, $alreadyFoundImages)) {
			$alreadyFoundImages[] = $src;

			//insert into images table
			//echo "INSERT IMAGE: " . insertImage($url, $src, $alt, $img_title) . "<br>";
			insertImage($url, $src, $alt, $img_title);
		}
	}
}


function followLinks($url)
{
	//any references to these arrays inside the fn refers to the global vrbls
	global $alreadyCrawled;
	global $crawling;
	//will call the DomDocumentParser
	$my_crawl = new DomDocumentParser($url);
	$link_list = $my_crawl->getlinks();
	// $link contains the items in the list
	foreach ($link_list as $link) {
		// anchor tags have attribute href which contains the url
		$href = $link->getAttribute("href");
		//check value and type with ===
		//strpos returns the number of where char exists or -1
		//continue go around loop
		if (strpos($href, "#") !== false) {
			continue;
		}
		// link can be used to execute javascript
		else if (substr($href, 0, 11) == "javascript:") {
			continue;
		}

		$href = createLink($href, $url);

		if (!in_array($href, $alreadyCrawled)) {
			$alreadyCrawled[] = $href;
			$crawling[] = $href;

			//insert href to db
			getDetails($href);
		} //else return; //if found duplicate stop. so that we can see output, othwerise wait till end

		// echo $href, "<br>";
	}
	//Remove the first element from an array
	//so remove item already crawled
	array_shift($crawling);
	//$site will be the item in the $crawling array

	//RECURSIVE
	foreach ($crawling as $site) {
		followLinks($site);
	}
}

//TODO: crawl more than one site
//https://github.com/BruceDone/awesome-crawler
//https://github.com/spatie/crawler
//maybe add a loop to crawl through several sites
//https://github.com/BruceDone/awesome-crawler


// $urls_to_crawl = array("http://www.bbc.com", "http://www.cnn.com", "http://www.wsj.com", "https://pixabay.com");
// //$startUrl = "http://www.bbc.com";
// foreach ($urls_to_crawl as $a_url) {
// 	echo "Next URL : $a_url";
// 	followLinks($a_url);
// }
$startUrl = "https://www.rd.com/list/funny-dog-photos/";
followLinks($startUrl);
