<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Tasemete ülesseadmine</title>
	<script src="http://code.jquery.com/jquery-2.1.4.min.js"></script>
	<link rel="stylesheet" href="style.css">
	<script>
		var draggedPiece = null;
		// Mouse relation to the dragged object while dragging (to calculate landing)
		var mouseToDragged = {
			x: 0,
			y: 0
		};
		var levelData = null;
		$(document).ready(function(){
			$("#levels").on("change", function(){
				var selected = $("#levels option:selected").text();
				// Remove all the event handlers before removing objects
				$(document).off();
				$("#content").empty();
				if(selected != "Vali tase"){
					// Retrieve level resources
					$.ajax({
						url: "getLevelData.php",
						data: {level: selected}
					}).done(function(data){
						levelData = JSON.parse(data);
						loadLevel();
					});	
				}
			});
		});
		
		function loadLevel(){
			$("<div id='border'><img src='"+levelData.border.image+"' alt='game border image'></div>").appendTo("#content");
			$("#border").css("margin", "auto");
			$("#border").css("left", "0");
			$("#border").css("right", "0");
			$("#border").css("top", "0");
			$("#border").css("bottom", "0");
			$("#border").css("width", levelData.border.width);
			$("#border").css("height", levelData.border.height);
			
			var borderCoordinates = getBorderCoordinates();
			if(levelData.coordinates === null){
				// Randomly distribute pieces
				var containerWidth = $("#content").width();
				var containerHeight = $("#content").height();
				var pieceCoordinates = [];
				for(var i=0; i<levelData.pieces.length; i++){
					var x = Math.ceil(Math.random()*(containerWidth-levelData.pieces[i].width));
					var y = Math.ceil(Math.random()*(containerHeight-levelData.pieces[i].height));
					createPiece(i, levelData.pieces[i].image, x, y);
					var relativeCoordinates = getPieceRelativeCoordinates("#piece" + (i+1));
					var coordinates = {
						filename: levelData.pieces[i].image.split("/")[2],
						x: relativeCoordinates.x,
						y: relativeCoordinates.y
					};
					pieceCoordinates.push(coordinates);
				}
				// Add coordinates to levelData.coordinates
				levelData.coordinates = pieceCoordinates;
				// Write all the coordinates to coordinates.txt in JSON
				updateCoordinatesFile();
			} else {
				// Set pieces to their coordinates
				for(var i=0; i<levelData.pieces.length; i++){
					var x = parseInt(borderCoordinates.x) + parseInt(levelData.coordinates[i].x);
					var y = parseInt(borderCoordinates.y) + parseInt(levelData.coordinates[i].y);					
					createPiece(i, levelData.pieces[i].image, x, y);
				}
			}
		}
		
		// Update all the coordinates from levelData.coordinates
		function updateCoordinatesFile(){
			var selected = $("#levels option:selected").text();
			$.ajax({
				url: "saveLevelCoordinates.php",
				data: {
					level: selected,
					coordinates: JSON.stringify(levelData.coordinates)
					}
			});			
		}
		
		function getBorderCoordinates(){
			var borderOffset = $("#border").offset();
			var borderCoordinates = {
				x: borderOffset.left,
				y: borderOffset.top
			};
			return borderCoordinates;
		}
		
		// Counter is from the loop and is used to make ids for the pieces (piece1, piece2, etc)
		function createPiece(counter, imgSrc, x, y){
			var id = "piece" + (counter+1);
			$("<div class='piece' id='"+id+"'><img src='"+imgSrc+"' alt='game piece image'></div>").appendTo("#content");
			var hash_id = "#" + id;
			$(hash_id).css("left", x);
			$(hash_id).css("top", y);
			
			$(hash_id).on("dragstart", function(){
				draggedPiece = "#" + $(this).attr("id");
				var piecePosition = $(draggedPiece).position();
				mouseToDragged = {
					x: event.pageX - piecePosition.left,
					y: event.pageY - piecePosition.top
				};
			});
			
			$(hash_id).on("drag", function(){
				if(event.pageX != 0){ // For some reason, last drag event gives 0 for event.pageX
					var x = event.pageX - mouseToDragged.x;
					var y = event.pageY - mouseToDragged.y;
					$(draggedPiece).css("left", x);
					$(draggedPiece).css("top", y);
				}
			});
			
			$(hash_id).on("dragend", function(){
				var imgSrc = $(draggedPiece).children("img").attr("src");
				var filename = imgSrc.split("/")[2];
				var relativeCoordinates = getPieceRelativeCoordinates(draggedPiece);
				savePieceCoordinates(filename, relativeCoordinates.x, relativeCoordinates.y);
			});
		}
		
		// All piece coordinates are saved relative to the border coordinates to allow different screen sizes
		function getPieceRelativeCoordinates(pieceId){
			var piecePosition = $(pieceId).position();
			var borderPosition = getBorderCoordinates();
			var relativeCoordinates = {
				x: piecePosition.left - borderPosition.x,
				y: piecePosition.top - borderPosition.y
			};
			return relativeCoordinates;
		}
		
		function savePieceCoordinates(filename, x, y){
			// Update coordinates in levelData.coordinates and write JSON into the coordinates.txt file
			for(var i=0; i<levelData.coordinates.length; i++){
				if(levelData.coordinates[i].filename == filename){
					levelData.coordinates[i].x = x;
					levelData.coordinates[i].y = y;
				}
			}
			
			updateCoordinatesFile(levelData.coordinates);
		}
	</script>
</head>
<body>
	<?php
		$levels = scandir("levels");
	?>
	<select name="levels" id="levels">
		<option value="none" selected="selected">Vali tase</option>
		<?php 
			foreach($levels as $level){
				if($level != "." && $level != ".."){
					echo "<option value='$level'>$level</option>";
				}
			}
		?>
	</select>
	
	<div id="content">
	</div>
</body>
</html>