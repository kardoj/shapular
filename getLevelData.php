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
		
		// If image positions file exists, get its contents
		if(file_exists($level_resources_path . "/coordinates.txt")){
			$coordinates = file_get_contents($level_resources_path . "/coordinates.txt");
			$file_rows = explode("\n", $coordinates);
			$coordinates_array = array();
			foreach($file_rows as $row){
				$row_pieces = explode(" ", $row);
				$one = new StdClass();
				$one->image = trim($row_pieces[0]);
				$one->x = trim($row_pieces[1]);
				$one->y = trim($row_pieces[2]);
				array_push($coordinates_array, $one);
			}
			$response->coordinates = $coordinates_array;
		}
		
		echo json_encode($response);
	}
	
?>