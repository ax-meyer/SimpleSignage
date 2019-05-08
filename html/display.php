<?php
// -- INIT
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "php-error.log");
// -- INIT
$configs = include('config/config.php');
date_default_timezone_set($configs['timezone']);
session_start();
$pdo = new PDO('sqlite:'.$configs['dbpath']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Refresh" content="<?php echo($configs['reloadtime']); ?>">
	<meta name="google" content="notranslate">
	<style type="text/css">
body
 {
     background-color: black;
     background-repeat: no-repeat;
	 background-size: cover;
	 position: fixed;
 }
  
 .verticalhorizontal {
    display: table-cell;
    height: 100vh;
    text-align: center;
    width: 100vw;
    vertical-align: middle;
}

.posters {
	object-fit: contain;
	height: 100vh;
	max-width: 100vw;
}
 </style>
</head>

<body>
<div class="verticalhorizontal">
    <!-- Page Content -->
	<img class="posters" id="display_image" align="middle"/>
</div>

<!-- jQuery - required here because generic_head is not loaded! Keeps the page small. -->
<script src="js/jquery.min.js"></script>

<script>
function onLoad()
{
	$.ajax({
        type: "POST",
        url: "ajax.php",
        dataType:'json',
        data: {
            method: "cleanupImages",
			session_id: "<?php echo(session_id()) ?>"
        },
        success: function(data) {
			console.log(data);
		},
		error: function(data) {
			console.log("error");
			console.log(data);
		}
    });
	
	<?php $device_id = intval($_GET['device']);
		$query = $pdo->prepare("SELECT FILENAME FROM IMAGES_TO_DEVICES INNER JOIN IMAGES ON IMAGES.ID = IMAGES_TO_DEVICES.IMAGE_ID WHERE IMAGES_TO_DEVICES.DEVICE_ID=? AND date(IMAGES.DATE_START) <= date('now') AND (date(IMAGES.DATE_END) > date('now') OR IMAGES.INFINITE = 1) AND IMAGES.MARKED_FOR_DELETE = 0");
		$query->bindValue(1,$device_id, PDO::PARAM_INT);
		$query->execute();
		$row_item = $query->fetch();
	 ?>
const images = [<?php 
			while($row_item)
			{
				echo("\"");
				echo($row_item[0]);
				echo("\",");
				$row_item = $query->fetch();
			}
			?>
];

const node = document.getElementById("display_image");

const cycleImages = (images, container, step) => 
	{
		images.forEach(
			(image, index) => (
				setTimeout(
					() => 
					{
						container.src = '<?php echo($configs['imagepath']); ?>' + "/" + image;
					}
					, step * (index)
				)
			)
		)
		setTimeout(() => cycleImages(images, container, step), step * images.length)
	}

cycleImages(images, node, <?php echo($configs['imagedisplaytime']); ?> * 1000);

}
window.onload = onLoad();
</script>
    

</body>
</html>
