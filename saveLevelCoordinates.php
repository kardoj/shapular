<?php
	if(isSet($_REQUEST["level"]) && isSet($_REQUEST["coordinates"])){
		$level_coordinates_file = "levels/" . $_REQUEST["level"] . "/coordinates.txt";
		file_put_contents($level_coordinates_file, $_REQUEST["coordinates"]);
	} else {
		die("Error.");
	}
?>
