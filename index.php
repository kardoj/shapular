<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Shapular</title>
	<script src="jquery-2.1.4.min.js"></script>
	<script src="draggabilly.pkgd.min.js"></script>
	<link rel="stylesheet" href="style.css">
	<script>
		var draggedPiece = null;
		// Mouse relation to the dragged object while dragging (to calculate landing)
		var mouseToDragged = {
			x: 0,
			y: 0
		};
		var levelData = null;
		var selectedLevel = null;
		// Allowed misplacement in all four directions
		var allowedError = 10;
		$(document).ready(function(){
			
			// Trigger level change when clicking reset
			$("#resetBtn").on("click", function(){
				$("#levels").trigger("change");
			});
			$("#levels").on("change", function(){
				selectedLevel = $("#levels option:selected").text();
				$("#content").empty();
				if(selectedLevel != "Vali tase"){
					// Retrieve level resources
					$.ajax({
						url: "getLevelData.php",
						data: {level: selectedLevel}
					}).done(function(data){
						levelData = JSON.parse(data);
						loadLevel();
					});	
				}
			});
		});
		
		function loadLevel(){
			$("<div draggable='true' id='border'><img src='"+levelData.border.image+"' alt='game border image'></div>").appendTo("#content");
			$("#border").css("margin", "auto");
			$("#border").css("left", "0");
			$("#border").css("right", "0");
			$("#border").css("top", "0");
			$("#border").css("bottom", "0");
			$("#border").css("width", levelData.border.width);
			$("#border").css("height", levelData.border.height);
			
			var borderCoordinates = getBorderCoordinates();
			// Randomly distribute pieces
			var containerWidth = $("#content").width();
			var containerHeight = $("#content").height();
			for(var i=0; i<levelData.pieces.length; i++){
				var x = Math.ceil(Math.random()*(containerWidth-levelData.pieces[i].width));
				var y = Math.ceil(Math.random()*(containerHeight-levelData.pieces[i].height));
				createPiece(i, levelData.pieces[i].image, x, y);
			}
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
			
			$(hash_id).draggabilly({}).on("dragStart", function(event){
				draggedPiece = "#" + $(this).attr("id");
				var piecePosition = $(draggedPiece).position();
				mouseToDragged = {
					x: event.pageX - piecePosition.left,
					y: event.pageY - piecePosition.top
				};
			});
			
			$(hash_id).draggabilly({}).on("dragMove", function(event){
				if(event.pageX != 0){ // For some reason, last drag event gives 0 for event.pageX
					var x = event.pageX - mouseToDragged.x;
					var y = event.pageY - mouseToDragged.y;
					$(draggedPiece).css("left", x);
					$(draggedPiece).css("top", y);
				}
			});
			
			$(hash_id).draggabilly({}).on("dragEnd", function(event){
				// CHECK IF ALL THE COORDINATES MATCH
				var pieces = $("#content").children("div");
				var win = true;
				for(var i=1; i<pieces.length; i++){
					var hash_id = "#" + $(pieces[i]).attr("id");
					var relativePieceCoordinates = getPieceRelativeCoordinates(hash_id);
					var filename = $(hash_id).children("img").attr("src").split("/")[2];
					if(!(coordinatesMatch(levelData.coordinates[i-1], relativePieceCoordinates))){
						win = false;
						break;
					}
				}
				// If all coordinates matched, player wins!
				if(win){
					alert("Sinu võit!");
					// Remove all the piece listeners so pieces can not be moved until choosing another level
					$(".piece").draggabilly("disable");
				}
			});
		}
		
		// A function for defining and checking allowed coordinate error
		function coordinatesMatch(levelDataCoordinates, pieceCoordinates){
			if(levelDataCoordinates.x >= pieceCoordinates.x - allowedError && levelDataCoordinates.x <= allowedError + pieceCoordinates.x &&
				levelDataCoordinates.y >= pieceCoordinates.y - allowedError && levelDataCoordinates.y <= allowedError + pieceCoordinates.y){
				return true;
			}
			return false;
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
				$level_files = scandir("levels/" . $level);
				$coordinates_available = false;
				foreach($level_files as $file){
					if($file == "coordinates.txt"){
						$coordinates_available = true;
					}
				}
				if($coordinates_available){
					echo "<option value='$level'>$level</option>";
				}
			}
		?>
	</select>
	<input type="button" value="Reset" id="resetBtn">
	
	<div id="content">
	</div>
</body>
</html>