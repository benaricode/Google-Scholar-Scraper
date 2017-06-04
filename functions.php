<?php

	$chartData;

	function get_or_default($arr, $key, $default) {
		if(isset($arr[$key])) {
			return $arr[$key];
		} else {
			return $default;
		}
	}

	function searchScholarMinYear($ThingToSearchFor, $year){
		$chartArray = array();
	
	for(;$year <= date("Y"); $year++){
		$temp = searchScholar($ThingToSearchFor, $year);

		$chartArray[$year] = $temp.",";
		//echo $year." had ".$temp." number of papers<br>";
	}
	buildChart($chartArray);

	}

	function buildChart($data){
		global $chartData ;
		$chartDataTemp = json_encode($data);
		$chartDataTemp = str_replace('{', '[', $chartDataTemp);
		$chartDataTemp = str_replace('}', '],', $chartDataTemp);
		$chartDataTemp = str_replace(':', ',', $chartDataTemp);
		$chartDataTemp = str_replace('"', '', $chartDataTemp);
		$chartDataTemp = str_replace(',,', ',],[', $chartDataTemp);
		$chartDataTemp = substr($chartDataTemp, 0 ,-1);
		$chartData = $chartDataTemp;
	}

	function searchScholar($ThingToSearchFor, $year){

		$formattedThingToSearchFor = str_replace(' ', '+', $ThingToSearchFor);

		$baseURL = 'https://scholar.google.com.au/scholar?q="';
		$yearlower = '"&hl=en&as_sdt=0%2C5&as_ylo=';
		$yearHigh = '&as_yhi=';

		$requestToMake = $baseURL.$formattedThingToSearchFor.$yearlower.$year.$yearHigh.$year;
echo $requestToMake;
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
		<?php if(isset($_POST['query'])){
		
		$query = get_or_default($_POST, 'query', '');
		$startYear = get_or_default($_POST, 'year', '');
		
		searchScholarMinYear($query , $startYear);
	
		}?>

		<title>Google Scholar Scaper</title>
		<?php if(isset($_POST['query'])){?>
			 <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		    <script type="text/javascript">
		      google.charts.load('current', {'packages':['corechart']});
		      google.charts.setOnLoadCallback(drawChart);

		      function drawChart() {
		        var data = google.visualization.arrayToDataTable([
		          ['Year', 'Sales'],
		       	<?php global $chartData; echo $chartData ?>
		        ]);

		        var options = {
		          title: 'Company Performance',
		          hAxis: {title: 'Year',  titleTextStyle: {color: '#333'}},
		          vAxis: {minValue: 0}
		        };

		        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
		        chart.draw(data, options);
		      }
    </script>
		<?php }?>
	</head>

	<body>
	<section>
		<h1>Google Scholar Scaper and graph</h1>
		<p>
			This page exists as a way to quickly graph a year over year view of the use of a phrase in papers, this is useful to get a sense of when a field boomed

			note, it may take awhile for the page to reload after clicking submit <br><br>

			Note if you send off too many requests too quickly google will think you are a robot and get really mad....

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

	    <div id="chart_div" style="width: 100%; height: 500px;"></div>	
	    
	    <?php 
		    if(isset($_POST['query'])){
		    	global $chartData; 
		    	echo $chartData;
		    	
				foreach ($chartData as $key => $value) {

					echo $key." had ".$value." number of papers<br>";
				    
				}
			}
	    ?>	
	</body>
</html>