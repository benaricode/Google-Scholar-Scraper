<?php

function get_or_default($arr, $key, $default) {
	if(isset($arr[$key])) {
		return $arr[$key];
	} else {
		return $default;
	}
}
	if(isset($_POST['query'])){
		searchScholar('Big Data', '2013');
	}

	function searchScholar($ThingToSearchFor, $year){

		$formattedThingToSearchFor = str_replace(' ', '+', $ThingToSearchFor);

		$baseURL = 'https://scholar.google.com.au/scholar?q="';
		$yearlower = '"&hl=en&as_sdt=0%2C5&as_ylo=';
		$yearHigh = '&as_yhi=';

		$requestToMake = $baseURL.$formattedThingToSearchFor.$yearlower.$year.$yearHigh.$year;

		$html = file_get_contents($requestToMake);
		
		$pos = stripos ($html , 'About');

		$total = substr ($html , $pos+6, 50);

		$sPos = stripos($total, '<b>');

		$output = substr($total, 0 ,$sPos-10);

		return str_replace(',', '', $output);
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Google Scholar Scaper</title>
	</head>

	<body>
	<section>
		<h1>Google Scholar Scaper and graph</h1>
		<p>
			This page exists as a way to quickly graph a year over year view of the use of a phrase in papers, this is useful to get a sense of when a field boomed

			note, it may take awhile for the page to reload after clicking submit

		</p>
	</section>
		<form action="functions.php" method="POST">
			<ul>
			<li>
				<label for="Query">Query</label>
				<input type="text" size="50" name="query" id="query">
			</li>

			<li>
				<label for="year">Start Year</label>
				<input type="number" size="10" name="year" id="year">
			</li>
			</ul>
			<button>Search scholar</button>
		</form>
		<?php echo date("Y"); ?>

		<?php if(isset($_POST['query'])){
		
		$query = get_or_default($_POST, 'query', '');
		$startYear = get_or_default($_POST, 'year', '');
		
		echo searchScholar($query , $startYear);

	}?>
	</body>
</html>