<?php
	
	//Global veriable to hold the array of data used to create the chart
	$chartData;

	//Gets the required information out of the request
	function get_or_default($arr, $key, $default) {
		if(isset($arr[$key])) {
			return $arr[$key];
		} else {
			return $default;
		}
	}

	//function to take a serch query and a minimum year
	function searchScholarMinYear($ThingToSearchFor, $year){
		
		//Create an array to hold values
		$chartArray = array();
		
		//While the submitted year is less then the current year
		for(;$year <= date("Y"); $year++){

			//get the value for the year
			$temp = searchScholar($ThingToSearchFor, $year);

			//add the year to the associative array
			$chartArray[$year] = $temp.",";
		}
	
		//build the charts array 
		buildChart($chartArray);

	}

	//takes a php associatve array and converts it to work with google charts
	function buildChart($data){
		
		//Use the global charts veriable 
		global $chartData ;

		//transfrom the old php array into a JSON object
		$chartDataTemp = json_encode($data);

		//using string replacement convert the JSON to the exact array type for google charts
		$chartDataTemp = str_replace('{', '[', $chartDataTemp);
		$chartDataTemp = str_replace('}', '],', $chartDataTemp);
		$chartDataTemp = str_replace(':', ',', $chartDataTemp);
		$chartDataTemp = str_replace('"', '', $chartDataTemp);
		$chartDataTemp = str_replace(',,', ',],[', $chartDataTemp);
		$chartDataTemp = substr($chartDataTemp, 0 ,-1);

		//set the chartdata to be the converted JSON object
		$chartData = $chartDataTemp;
	}

	//Take a thing to search for and a year to search for 
	function searchScholar($ThingToSearchFor, $year){

		//Replace any spaces with + signs for the url
		$formattedThingToSearchFor = str_replace(' ', '+', $ThingToSearchFor);

		//URL component parts
		//I am going to assume that google won't change there URL any time soon....
		$baseURL = 'https://scholar.google.com.au/scholar?q="';
		$yearlower = '"&hl=en&as_sdt=0%2C5&as_ylo=';
		$yearHigh = '&as_yhi=';

		//Create the actual request
		$requestToMake = $baseURL.$formattedThingToSearchFor.$yearlower.$year.$yearHigh.$year;

		//sends off the request to get the page
		$html = file_get_contents($requestToMake);
		
		//gets the first instance of the phrase we want, we are using about to find the navbar 
		$pos = stripos ($html , 'About');

		//remove everything before about inclusive and only give us the next 50 charters
		$total = substr ($html , $pos+6, 50);

		//find the end of our number by searching for the next element
		$sPos = stripos($total, '<b>');

		//remove everything after the number
		$output = substr($total, 0 ,$sPos-10);

		//remove the comma in the middle of the number and return just the number
		return str_replace(',', '', $output);
	}
?>

<!-- HTML5 doctype declaration -->
<!DOCTYPE html>
<html>
	<head>
		<!-- if this is the first page load or after we have made a request -->
		<?php if(isset($_POST['query'])){
			
			//get the value sent or replace it with the default
			$query = get_or_default($_POST, 'query', '');
			$startYear = get_or_default($_POST, 'year', '');
			
			//invokes the search for the papers
			searchScholarMinYear($query , $startYear);
	
		}?>

		<!-- Page title -->
		<title>Google Scholar Scaper</title>
		
		<!-- if this is the first page load or after we have made a request -->
		<?php if(isset($_POST['query'])){?>

			<!--Google charts stuff, by having it inside an if statement like we have it only gets added to the page after the first load -->
			<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		    <script type="text/javascript">
		      google.charts.load('current', {'packages':['corechart']});
		      google.charts.setOnLoadCallback(drawChart);

		      function drawChart() {
		        var data = google.visualization.arrayToDataTable([
		          ['Year', 'papers'],
		          //Puts the data into the chart
		       	<?php global $chartData; echo $chartData ?>
		        ]);

		        var options = {
		          title: 'Number of papers per year',
		          hAxis: {title: 'Year',  titleTextStyle: {color: '#333'}},
		          vAxis: {minValue: 0}
		        };

		        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
		        chart.draw(data, options);
		      }
   			</script>
   		<!-- Ends the php if statement -->
		<?php }?>
	</head>

	<body>
		<section>
		<!-- Title heading and information -->
			<h1>Google Scholar Scraper and graph</h1>
			<p>
				This page exists as a way to quickly graph a year over year view of the use of a phrase in papers, this is useful to get a sense of when a field boomed

				note, it may take awhile for the page to reload after clicking submit <br><br>

				Note if you send off too many requests too quickly google will think you are a robot and get really mad and break everything....

			</p>
		</section>

		<!-- Form to take info from the user -->
		<section>
			<!-- Post the form back to this page -->
			<form action="functions.php" method="POST">
				<ul>
				<li>
					<!-- Fields for the thing to search for -->
					<label for="Query">Query</label>
					<input type="text" size="50" name="query" id="query">
				</li>

				<li>
					<!-- Fields for the year to search for -->
					<label for="year">Start Year</label>
					<input type="number" size="10" name="year" id="year">
				</li>
				</ul>

				<!-- submit button-->
				<button>Search scholar</button>
			</form>
		</section>

		<!-- Chart / raw data section -->
		<section>

			<!-- Chart canvas -->
		    <div id="chart_div" style="width: 100%; height: 500px;"></div>	
		    
		    <!-- Will print out the raw data -->
		    <?php 
			   //if this is the first page load or after we have made a request 
			    if(isset($_POST['query'])){
			    
			    	//Use the global veriable 
			    	global $chartData; 
			    	
			    	//Loop through all the values and plot them on a chart
					foreach ($chartData as $key => $value) {

						echo $key." had ".$value." number of papers<br>";
					    
					}
				}
		    ?>	
		</section>
	</body>
</html>
