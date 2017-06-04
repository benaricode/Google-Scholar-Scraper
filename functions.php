<?php

	searchScholar('Big Data', '2013');

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