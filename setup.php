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
						loadLevel(JSON.parse(data));
					});	
				}
			});
		});
		
		function loadLevel(levelData){
			$("<div id='border'><img src='"+levelData.border.image+"' alt='game border image'></div>").appendTo("#content");
			$("#border").css("margin", "auto");
			$("#border").css("left", "0");
			$("#border").css("right", "0");
			$("#border").css("top", "0");
			$("#border").css("bottom", "0");
			$("#border").css("width", levelData.border.width);
			$("#border").css("height", levelData.border.height);
			
			var borderPosition = $("#border").offset();
			var borderX = borderPosition.left;
			var borderY = borderPosition.top;
			if(levelData.coordinates === null){
				// Randomly distribute pieces
				var containerWidth = $("#content").width();
				var containerHeight = $("#content").height();
				for(var i=0; i<levelData.pieces.length; i++){
					var x = Math.ceil(Math.random()*(containerWidth-levelData.pieces[i].width));
					var y = Math.ceil(Math.random()*(containerHeight-levelData.pieces[i].height));
					console.log(containerWidth);
					createPiece(i, levelData.pieces[i].image, x, y);
				}				
			} else {
				// Set pieces to their coordinates
				for(var i=0; i<levelData.pieces.length; i++){
					var x = parseInt(borderX) + parseInt(levelData.coordinates[i].x);
					var y = parseInt(borderY) + parseInt(levelData.coordinates[i].y);					
					createPiece(i, levelData.pieces[i].image, x, y);
				}
			}
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