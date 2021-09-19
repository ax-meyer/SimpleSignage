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
$site_title = "Devices";
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
                    <h1 class="page-header">Device management</h1>
                </div>
            </div>
            
            <div class="panel panel-default" style="max-width: 500px; margin-left: auto; margin-right: auto; display: none" id="addDeviceDiv">
                <div class="panel-heading"><b>Add device</b> </div>
                <div class="panel-body">
                    <form role="form">
                        <fieldset>
                           <div class="form-group">
                                <input class="form-control" placeholder="Description" name="item_description" autofocus="" type="text" id="input_description">
                            </div>
							<div class="form-group">
							<label>Copy images to display from:
							<select name="copy_from_device" id="copy_from_device">
							<option value="-1">Don't copy</option>
							<?php
							$query = $pdo->prepare("SELECT DEVICES.ID as Column1, DEVICES.NAME as Column2 FROM DEVICES");
                            $query->execute();
                            $row_item = $query->fetch();
                        
                        // iterate and insert all items into table
                            while($row_item) {
                                $device_id = $row_item[0];
                                $name = $row_item[1];
                                ?>
								<option value=<?php echo($device_id); ?>><?php echo($name); ?></option>
                                <?php
                                $row_item = $query->fetch();
                            }
                        ?>
							</select>
							</div>
                            <input class="btn btn-lg btn-success btn-block" onclick="addDevice();" value="Add device" />
                        </fieldset>
                    </form>
                </div>
            </div>
            
            <div class="panel panel-default" style="display: block; margin-left: auto; margin-right: auto">
                <div class="panel-heading"><b>All images</b> </div>
                <div class="panel-body">
                    <table style="width:100%" id="image_table">
                        <tr>
                            <th onclick="sortTable('NAME')" style="cursor:pointer"> Description </th>
                            <th onclick="sortTable('NUM_IMAGES')" style="cursor:pointer"> # Images </th>
                            <th> Link </th>
                        </tr>
                        <?php
                            // get all items from database
                            if (isset ($_GET['sortby']) && isset ($_GET['order']))
                            {
                                $sort_by = $_GET['sortby'];
                                $order = $_GET['order'];
                                switch($sort_by) {
                                    case 'NAME':
                                        $sort_by = "DEVICES.NAME";
                                        break;
                                    case 'NUM_IMAGES':
                                        $sort_by = "Column3";
                                        break;
                                    default:
                                        $sort_by = "DEVICES.NAME";
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
                                $sort_by = "NAME";
                                $order = "ASC";
                            }
							
                            $query = $pdo->prepare("SELECT DEVICES.ID as Column1, DEVICES.NAME as Column2, Count(*) as Column3 FROM DEVICES INNER JOIN IMAGES_TO_DEVICES WHERE DEVICE_ID = DEVICES.ID group by IMAGES_TO_DEVICES.DEVICE_ID UNION SELECT DEVICES.ID as Column1, DEVICES.NAME as Column2, 0 as Column3 FROM DEVICES WHERE NOT EXISTS (SELECT * FROM IMAGES_TO_DEVICES WHERE IMAGES_TO_DEVICES.DEVICE_ID = DEVICES.ID) ORDER BY $sort_by $order");
                            $query->execute();
                            $row_item = $query->fetch();
                        ?>
                            <div style="display:none" id="pass_order_to_js"><?php echo($order); ?></div>
                        <?php
                        // iterate and insert all items into table
                            while($row_item) {
                                $device_id = $row_item[0];
                                $name = $row_item[1];
                                $num_images = $row_item[2];
                                ?>

                                <!-- create div and insert item & transaction details -->
                                <tr>
                                <td><?php echo($name); ?></td>
                                <td><?php echo($num_images); ?></td>
                                <td><a href="display.php?device=<?php echo($device_id); ?>">Display Link</a></td>
								<td>                                
                                    <input id="btn_del_item" type="button" class="btn btn-xs btn-danger" value="Delete" onclick="delDevice('<?php echo($device_id); ?>', '<?php echo($name); ?>')" />
                                </td>
                                </tr>
                                <?php
                                $row_item = $query->fetch();
                            }
                        ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><input id="btn_add_item" type="button" class="btn btn-xs btn-success" value="Add new device" onclick="showaddDevice()" /></td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div id="footerwrap"></div>
            <!-- ... Your content goes here ... -->

        </div>
    </div>

</div>

<script>
    function sortTable(sort_var) {
        var order = document.getElementById("pass_order_to_js").textContent;
        console.log("order " + order);
        if (order.toLowerCase() == "asc") { order = "DESC"; }
        else { order = "ASC"; }
        window.location = "devices.php?sortby=" + sort_var + "&order=" + order;
    }
</script>

<script>
// delete item on button click
function delDevice(deviceid, devicename){
	// verify
	var ret = confirm("Deleting device \"" + devicename + "\" (device " + deviceid + "). Are you sure?");
	if (ret == false) { return };
	
	// execute delete
    $.ajax({
        type: "POST",
        url: "ajax.php",
        dataType:'json',
        data: {
            method: "delDevice",
            deviceid: deviceid,
			session_id: "<?php echo(session_id()) ?>"
        },
        
        success: function(data) {
            console.log("SUCCESS");
            console.log(data);
			console.log("deleted device" + deviceid);
            // handle return
            if(data['status'] == 'okay') {
              alert("Device \"" + devicename + "\" deleted!")
            } else {
                alert("An error occured. Device not deleted.")
            }
            // reload page. deleted item vanishes from table
            location.reload();
        }
    });
}

// add new item to database
function addDevice() {    
    var description = $("#input_description").val()
    // verify that all input fields are filled
    if (description == "" | description == null) {
        alert("Please Fill All Fields");
        return false;
    }
	var copy_id = $("#copy_from_device").val()
	
    $.ajax({
        type: "POST",
        url: "ajax.php",
        dataType:'json',
        data: {
            method: "addDevice",
            description: description,
			copy_id : copy_id,
			session_id: "<?php echo(session_id()) ?>"
        },
        
        success: function(data) {
            console.log("SUCCESS");
            console.log(data);
            // handle return codes
            if(data['status'] == 'deviceExist') {
              alert("Device name already used!")
              $("#input_description").val("")
            } else if (data['status'] == 'error'){
                alert("An error occured. Device not added.")
            } else {
                //alert("Device added successfully. Name is " + data['name']);
            }
            // reload page. new item appears in table
            location.reload();
        }
    });
}
</script>

<script>
    function showaddDevice() {
        document.getElementById('addDeviceDiv').style.display = "block";
    }
</script>
    

</body>
</html>
