<?php
include("config.php");
include("classes/SiteResultsProvider.php");
include("classes/ImageResultsProvider.php");

if (isset($_GET["term"])) {
	// what are we searching for
	$term = $_GET["term"];
} else {
	exit("You must enter a search term");
}
// image or regular search. default is sites
$type = isset($_GET["type"]) ? $_GET["type"] : "sites";
//if page passed to the URL set it to that value otherwise default to 1
$page = isset($_GET["page"]) ? $_GET["page"] : 1;



?>
<!DOCTYPE html>
<html>

<head>
	<title>Welcome to Doodle</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
	<!-- our css is after as do not want it overwridden -->
	<link rel="stylesheet" type="text/css" href="assets/css/test_style_v1.css">
	<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

</head>

<body>

	<div class="wrapper">

		<div class="header">


			<div class="headerContent">

				<div class="logoContainer">
					<a href="index.php">
						<img src="assets/images/doodleLogo.png">
					</a>
				</div>

				<div class="searchContainer">

					<form action="search.php" method="GET">

						<div class="searchBarContainer">
							<!-- input type="hidden" ... ensures stay on img or site search if type another term in -->
							<input type="hidden" name="type" value="<?php echo $type; ?>">
							<input class="searchBox" type="text" name="term" value="<?php echo $term; ?>">
							<button class="searchButton">
								<img src="assets/images/icons/search.png" title="Site Logo" alt="Logo">
							</button>
						</div>

					</form>

				</div>

			</div>


			<div class="tabsContainer">

				<ul class="tabList">
					<!-- add an activa class to the search type. active sites or active images -->
					<!-- so one of these list items will be active depending on the search type -->
					<li class="<?php echo $type == 'sites' ? 'active' : '' ?>">
						<a href='<?php echo "search.php?term=$term&type=sites"; ?>'>
							Sites
						</a>
					</li>

					<li class="<?php echo $type == 'images' ? 'active' : '' ?>">
						<a href='<?php echo "search.php?term=$term&type=images"; ?>'>
							Images
						</a>
					</li>

				</ul>


			</div>



		</div>

		<div class="mainResultsSection">
			<?php
			if ($type == "sites") {
				$resultsProvider = new SiteResultsProvider($connection);
				//echo $resultsProvider->getNumResults($term);
				$pageSize = 20; //results per page
			} else {
				$resultsProvider = new ImageResultsProvider($connection);
				$pageSize = 30;
			}

			//both classes have getNumResults() fn			
			$numResults = $resultsProvider->getNumResults($term);
			echo "<p class='resultsCount'>$numResults results found</p>";


			echo $resultsProvider->getResultsHtml($page, $pageSize, $term);
			?>

		</div>

		<div class="paginationContainer">
			<div class="pageButtons">

				<div class="pageNumberContainer">
					<img src="assets/images/pageStart.png">
				</div>

				<?php
				//PAGINATION (page numbers at bottom as in google search)
				$pagesToShow = 10; //how many pages shown at bottom, how many can click on
				$numPages = ceil($numResults / $pageSize);
				//never display more than 10 pages AND never display more pages than the search results justify
				$pagesLeft = min($pagesToShow, $numPages);
				//have pages before and after current page in the display at bottom 7 8 9(currentPage) 10 11
				//floor ensures that float converted to int and rownddown  
				$currentPage = $page  - floor($pagesToShow / 2);

				//edge case: negative pages
				if ($currentPage < 1) {
					$currentPage = 1;
				}
				//edge case: do not show pages that do not have search results in them
				if ($currentPage + $pagesLeft > $numPages + 1) {
					$currentPage = $numPages + 1 - $pagesLeft;
				}


				while ($pagesLeft != 0 && $currentPage <= $numPages) {
					//$page = isset($_GET["page"]) ? $_GET["page"] : 1;
					if ($currentPage == $page) {
						//image for page numbers
						//current page does not have link		
						echo "<div class='pageNumberContainer'>
						<img src='assets/images/pageSelected.png'>
						<span class='pageNumber'>$currentPage</span>
						</div>";
					} else {
						//this pagenumber will have a link to it
						//$type is either website or iamge
						echo 	"<div class='pageNumberContainer'>
									<a href='search.php?term=$term&type=$type&page=$currentPage'>
										<img src='assets/images/page.png'>
										<span class='pageNumber'>$currentPage</span>
									</a>
								</div>";
					}




					$currentPage++;
					$pagesLeft--;
				}

				?>



				<div class="pageNumberContainer">
					<img src="assets/images/pageEnd.png">
				</div>
			</div>
		</div>
		<!-- for image preview: https://fancyapps.com/fancybox/3/ -->

		<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
		<!-- for masonry/grid layout: https://masonry.desandro.com/ -->
		<script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
		<script type="text/javascript" src="assets/JS/script.js"></script>
</body>

</html>