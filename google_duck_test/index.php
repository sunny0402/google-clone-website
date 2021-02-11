<!DOCTYPE html>
<html>

<head>
	<title>My Search</title>

	<meta charset="UTF-8">
	<meta name="description" content="It is a google search clone to search websites and images.">
	<meta name="keywords" content="HTML, CSS, JavaScript, search engine, PHP, Udemy">
	<meta name="author" content="John Doe">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" type="text/css" href="assets/css/test_style_v1.css">

</head>

<body>

	<div class="wrapper indexPage">


		<div class="mainSection">

			<div class="logoContainer">
				<img src="assets/images/doodleLogo.png">
			</div>


			<div class="searchContainer">

				<form action="search.php" method="GET">

					<input class="searchBox" type="text" name="term">
					<input class="searchButton" type="submit" value="Search">


				</form>

			</div>


		</div>


	</div>

</body>

</html>