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
$site_title = "Add Image";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include ("generic_head.php"); ?>
</head>

<body>
<div id="wrapper">

    <!-- Navigation -->
    <?php include ("generic_navigation.php"); ?>

    <!-- Page Content -->
    <div id="page-wrapper">
        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Image management</h1>
                </div>
            </div>
            
            <div class="panel panel-default" style="max-width: 500px; margin-left: auto; margin-right: auto; display: none" id="addImageDiv">
                <div class="panel-heading"><b>Add image</b> </div>
                <div class="panel-body">
                    <form action="" enctype="multipart/form-data" method="POST" name="addImageForm" id="addImageForm" role="form">
                        <fieldset>
                            <div class="form-group">
                                <input class="form-control" placeholder="Description" name="image_description" autofocus="" type="text" id="input_description">
                            </div>
							
							<div>
							<label>Start date:</label>
							<input type="date" id="input_date_start" name="image_date_start" onchange="date_order_start_changed()">
							</div>
							
							<div>
							<label>End date:</label>
							<input type="date" id="input_date_end" name="image_date_end" onchange="date_order_end_changed()">
							</div>
							
							<div>
							<input type="checkbox" id="input_infinite" name="image_infinite" onchange="document.getElementById('input_date_end').disabled = this.checked;" >
							<label>Run infinitely?</label>
							</div>
							
							<div><label>Devices to show on:</label></div>
							<div id="device_checkboxes">
							<?php
							$query = $pdo->prepare("SELECT ID as Column1, NAME as Column2 FROM DEVICES");
                            $query->execute();
                            $row_item = $query->fetch();
							while($row_item) { 
							$device_id = $row_item[0];
							$device_name = $row_item[1];
							?>
								<input type="checkbox" id="input_device_<?php echo($device_id); ?>" name="image_device_<?php echo($device_id); ?>" checked="checked" >
								<label id="label_device_<?php echo($device_id); ?>"><?php echo($device_name); ?></label></br>
							<?php	
								$row_item = $query->fetch();
							}
							?>
							<label>Select image:</label><br/>
							<input name="image_path" id="input_image_path" type="file" class="inputFile" />
                            <input class="btn btn-lg btn-success btn-block" type="submit" value="Add image" />
                        </fieldset>
                    </form>
                </div>
            </div>
            
            <div class="panel panel-default" style="display: block; margin-left: auto; margin-right: auto">
                <div class="panel-heading"><b>All images</b> </div>
                <div class="panel-body">
                    <table style="width:100%" id="image_table">
                        <tr>
                            <th > Icon </th>
                            <th onclick="sortTable('DESCRIPTION')" style="cursor:pointer"> Description </th>
                            <th onclick="sortTable('DATE_START')" style="cursor:pointer"> Start date </th>
                            <th onclick="sortTable('DATE_END')" style="cursor:pointer"> End date </th>
                            <th onclick="sortTable('INFINITE')" style="cursor:pointer"> Run infinetly? </th>
                            <th> Devices </th>
                        </tr>
                        <?php
                            // get all items from database
                            if (isset ($_GET['sortby']) && isset ($_GET['order']))
                            {
                                $sort_by = $_GET['sortby'];
                                $order = $_GET['order'];
                                switch($sort_by) {
                                    case 'DESCRIPTION':
                                        $sort_by = "IMAGES.DESCRIPTION";
                                        break;
                                    case 'ENABLED':
                                        $sort_by = "IMAGES.ENABLED";
                                        break;
                                    case 'DATE_START':
                                        $sort_by = "IMAGES.DATE_START";
                                        break;
                                    case 'DATE_END':
                                        $sort_by = "IMAGES.DATE_END";
                                        break;
                                    case 'INFINITE':
                                        $sort_by = "IMAGES.INFINITE";
                                    case 'KEEP_AFTER_DATE_END':
                                        $sort_by = "IMAGES.KEEP_AFTER_DATE_END";
                                        break;
									case 'FILENAME':
                                        $sort_by = "IMAGES.FILENAME";
                                        break;
                                    default:
                                        $sort_by = "IMAGES.DATE_START";
                                }

                                switch ($order) {
                                    case 'ASC':
                                    case 'DESC':
                                        break;
                                    default:
                                        $order = 'ASC';
                                }
                            } else
                            {
                                $sort_by = "DATE_START";
                                $order = "ASC";
                            }
							
                            $query = $pdo->prepare("SELECT IMAGES.ID as Column1, IMAGES.DESCRIPTION as Column2, IMAGES.ENABLED as Column3, IMAGES.DATE_START as Column4, IMAGES.DATE_END as Column5, IMAGES.INFINITE as Column6, IMAGES.FILENAME as Column7, group_concat(DEVICES.NAME, ', ') as Column8 FROM IMAGES INNER JOIN IMAGES_TO_DEVICES ON IMAGES.ID = IMAGES_TO_DEVICES.IMAGE_ID INNER JOIN DEVICES ON IMAGES_TO_DEVICES.DEVICE_ID = DEVICES.ID group by IMAGES.ID UNION SELECT IMAGES.ID as Column1, IMAGES.DESCRIPTION as Column2, IMAGES.ENABLED as Column3, IMAGES.DATE_START as Column4, IMAGES.DATE_END as Column5, IMAGES.INFINITE as Column6, IMAGES.FILENAME as Column7, null as Column8 FROM IMAGES WHERE IMAGES.MARKED_FOR_DELETE = 0 AND NOT EXISTS (SELECT * FROM IMAGES_TO_DEVICES WHERE IMAGES.ID = IMAGES_TO_DEVICES.IMAGE_ID) ORDER BY $sort_by $order");
                            $query->execute();
                            $row_item = $query->fetch();
                        ?>
                            <div style="display:none" id="pass_order_to_js"><?php echo($order); ?></div>
                        <?php
                        // iterate and insert all items into table
                            while($row_item) {
                                $image_id = $row_item[0];
                                $description = $row_item[1];
                                $enabled = $row_item[2];
                                $date_start = $row_item[3];
                                $date_end = $row_item[4];
                                $infinite = $row_item[5];
                                $filename = $row_item[6];
                                $devices = $row_item[7];
                                ?>
								
                                <tr>
                                <td><img src='<?php echo($configs['imagepath'].'/'.$filename); ?>' alt='Image' height=60px style="margin-bottom:10px"></td>
                                <td><?php echo($description); ?></td>
                                <td><?php echo($date_start); ?></td>
                                
                                <?php
                                if ($infinite) { ?>
                                    <td></td>
                                    <td>Yes</td>
                                    

                                <?php } else { ?>
                                    <td><?php echo($date_end); ?></td>
									<td>No</td>
                                <?php } ?>
								<td> <?php echo($devices); ?></td>
								<td>                                
                                    <input id="btn_del_item" type="button" class="btn btn-xs btn-danger" value="Delete" onclick="delImage('<?php echo($image_id) ?>')" />
                                </td></tr>
                                <?php
                                $row_item = $query->fetch();
                            }
                        ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><input id="btn_add_item" type="button" class="btn btn-xs btn-success" value="Add new image" onclick="showaddImage()" /></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div id="footerwrap"></div>
            <!-- ... Your content goes here ... -->

        </div>
    </div>

</div>

<!-- jQuery -->
<script src="js/jquery.min.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="js/bootstrap.min.js"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="js/metisMenu.min.js"></script>

<!-- Custom Theme JavaScript -->
<script src="js/startmin.js"></script>

<script>
    function sortTable(sort_var) {
        var order = document.getElementById("pass_order_to_js").textContent;
        console.log("order " + order);
        if (order.toLowerCase() == "asc") { order = "DESC"; }
        else { order = "ASC"; }
        window.location = "images.php?sortby=" + sort_var + "&order=" + order;
    }

Date.prototype.toDateInputValue = (function(daysToOffset) {
    var local = new Date(this);
    local.setMinutes(this.getMinutes() - this.getTimezoneOffset() + daysToOffset * 1440);
    return local.toJSON().slice(0,10);
});
function onLoad()
{
document.getElementById('input_date_start').value = new Date().toDateInputValue(0);
document.getElementById('input_date_end').value = new Date().toDateInputValue(14);
}
window.onload = onLoad();


function date_order_start_changed()
{
	var start = document.getElementById('input_date_start').value;
	var end = document.getElementById('input_date_end').value;
	if (end < start)
	{
		document.getElementById('input_date_end').value = start;
	}
}

function date_order_end_changed()
{
	var start = document.getElementById('input_date_start').value;
	var end = document.getElementById('input_date_end').value;
	if (end < start)
	{
		document.getElementById('input_date_start').value = end;
	}
}

// delete item on button click
function delImage(imageid){
	// verify
	var ret = confirm("Deleting image. Are you sure?");
	if (ret == false) { return; }
	
	// execute delete
    $.ajax({
        type: "POST",
        url: "ajax.php",
        dataType:'json',
        data: {
            method: "markImageForDelete",
            imageid: imageid,
			session_id: "<?php echo(session_id()) ?>"
        },
        
        success: function(data) {
            console.log("SUCCESS");
            console.log(data);
            // handle return
            if(data['status'] == 'okay') {
              alert("Image deleted!")
            } else {
                alert("An error occured. Item not deleted.")
            }
            // reload page. deleted item vanishes from table
            location.reload();
        }
    });
}


// add new image to database
$(document).ready(function (e) {
	$("#addImageForm").on('submit',(function(e) {    
	e.preventDefault();
    var description = $("#input_description").val()
    var date_start = $("#input_date_start").val()
    var date_end = $("#input_date_end").val()
	var infinite = document.getElementById("input_infinite").checked;
	var image_path = $("#input_image_path").val();
    // verify that all input fields are filled
    
	var ancestor = document.getElementById('device_checkboxes'),
    descendents = ancestor.getElementsByTagName('*');
	var at_least_one_device = false;
	var devices = [];
	var i, desc;
	for (i = 0; i < descendents.length; ++i) {
		e = descendents[i];
		if (e.id.startsWith("input_device_") && e.checked == true) {
			var spl = e.id.split("_");
			devices.push(parseInt(spl[2]));
		}	
    }
	
	
	if (description == "" | description == null | date_start == "" | date_start == null | devices.Length == 0 | (infinite == false && (date_end == "" | date_end == null)) | image_path == "" | image_path == null) {
        alert("Please Fill All Fields");
        return false;
    }
	
	var data = new FormData(this);
	data.append("method", "addImage");
	if (infinite) {
	data.append("infinite", 1);
	}
	else{
			data.append("infinite", 0);

	}
	
	data.append("devices", devices);
	data.append("session_id", "<?php echo(session_id()) ?>");
	
    $.ajax({
        type: "POST",
        url: "ajax.php",
		data:  data,
		contentType: false,
		processData:false,
        
        success: function(data) {
            console.log("SUCCESS");
            console.log(data);
            // reload page. new item appears in table
            location.reload();
        }
    });
}
));
});

    function showaddImage() {
        document.getElementById('addImageDiv').style.display = "block";
    }
</script>
    

</body>
</html>
