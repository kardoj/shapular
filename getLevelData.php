<?php
	
	// An object containing border, pieces array and array of pieces' coordinates if available
	if(isSet($_REQUEST["level"])){
		$level = $_REQUEST["level"];
		$response = new StdClass();
		
		$level_resources_path = "levels/" . $level;
		
		// Every level must have border.png
		$border = new StdClass();
		$border->image = $level_resources_path . "/border.png";
		$border_info = getimagesize($level_resources_path . "/border.png");
		$border->width = $border_info[0];
		$border->height = $border_info[1];
		$response->border = $border;
		
		$pieces = array();
		foreach(scandir($level_resources_path) as $piece){
			if(strpos($piece, ".png") != false && $piece != "border.png"){
				$piece_info = new StdClass();
				$piece_info->image = $level_resources_path . "/" . $piece;
				$size_data = getimagesize($level_resources_path . "/" . $piece);
				$piece_info->width = $size_data[0];
				$piece_info->height = $size_data[1];
				array_push($pieces, $piece_info);
			}
		}
		$response->pieces = $pieces;
		$response->coordinates = null;
		
		// If coordinates file exists, get coordinates
		if(file_exists($level_resources_path . "/coordinates.txt")){
			$coordinates = file_get_contents($level_resources_path . "/coordinates.txt");
			$coordinates_array = json_decode($coordinates);
			$response->coordinates = $coordinates_array;
		}
		
		echo json_encode($response);
	}
	
?>