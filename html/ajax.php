<?php
error_reporting(-1); // reports all errors
ini_set("display_errors", "1"); // shows all errors
ini_set("log_errors", 1);
ini_set("error_log", "php-error.log");

$configs = include('config/config.php');
date_default_timezone_set($configs['timezone']);
// -- INIT
session_start();
$pdo = new PDO('sqlite:'.$configs['dbpath']);

// -- return data
$data = array('action' => "");

$dest_dir=__DIR__.'/images/';

if(!isset($_POST['session_id']) || $_POST['session_id'] != session_id())
{
	$data["error"] = "remote access not allowed";
	
	echo json_encode($data);
	trigger_error("remote access not allowed");
	exit;
}

$query = $pdo->prepare("SELECT date(MAX(DATE)) < date('now') FROM CLEANUPS");
$query->execute();
$do_cleanup = $query->fetch();

if ($do_cleanup[0] == null || $do_cleanup[0] == 1)
{
	// mark expired images for deletion
	$query = $pdo->prepare("UPDATE IMAGES SET MARKED_FOR_DELETE = 1 WHERE date(IMAGES.DATE_END) < date('now') AND IMAGES.MARKED_FOR_DELETE = 0");
	$query->execute();
	
	// delete images marked for delete for 7 days
	$query = $pdo->prepare("SELECT FILENAME, ID FROM IMAGES WHERE IMAGES.MARKED_FOR_DELETE = 1 AND date(IMAGES.DELETE_DATE, '+7 day') < date('now')");
	$query->execute();
	$row = $query->fetch();
	$ids_to_delete = array();
	while ($row)
	{
		$image_filename = $dest_dir.$row[0];
		unlink($image_filename);
		array_push($ids_to_delete, $row[1]);
		$row = $query->fetch();
	}
	// delete database entries
	$query = $pdo->prepare("DELETE FROM IMAGES WHERE id IN (?)");
	$query->bindValue(1, implode($ids_to_delete, ","));
	$query->execute();
	
	// log cleanup to database
	$query = $pdo->prepare("INSERT INTO CLEANUPS (DATE, DELETED_IMAGES) VALUES (date('now'), ?)");
	$query->bindValue(1,sizeof($ids_to_delete));
	$query->execute();
	$data['cleanup'] = "done";
}

if ($_POST['method'] == "addDevice") {
    // insert user into database
    $query2 = $pdo->prepare("INSERT INTO DEVICES (NAME) VALUES(?)");
    $query2->bindValue(1, $_POST['description'], PDO::PARAM_STR);
	$query2->execute();
    //trigger_error($pdo->errorCode());
	if ($_POST['copy_id'] != "-1")
	{
		
		$query = $pdo->prepare("SELECT IMAGE_ID FROM IMAGES_TO_DEVICES WHERE DEVICE_ID=?");
		$query->bindValue(1, intval($_POST['copy_id']), PDO::PARAM_INT);
		$query->execute();
		$row = $query->fetch();
	
		$device_id = -1;
		if ($row)
		{
			$query_2 = $pdo->prepare("SELECT max(ID) FROM DEVICES");
			$query_2->execute();
			$row_2 = $query_2->fetch();
			$device_id = intval($row_2[0]);
		}
		$data['prog'] = true;
		
		while($row) 
		{
			$query_3 = $pdo->prepare("INSERT INTO IMAGES_TO_DEVICES (IMAGE_ID, DEVICE_ID) VALUES (?,?)");
			$query_3->bindValue(1, intval($row[0]), PDO::PARAM_INT);
			$query_3->bindValue(2, $device_id, PDO::PARAM_INT);
			$query_3->execute();
			$row = $query->fetch();
		}
	}		
    $data['status'] = "okay";
    $data['name'] = $_POST['description'];
	$data['post'] = $_POST;
	$data['compare'] = $_POST['copy_id'] != "-1";
}
elseif ($_POST['method'] == 'delDevice'){
	//trigger_error("deviceid $_POST['deviceid']");        
    $query = $pdo->prepare("DELETE FROM DEVICES WHERE ID=?");
    $query->bindValue(1,$_POST['deviceid'], PDO::PARAM_STR);
    $result = $query->execute();
    $data['id_to_delete'] = $_POST['deviceid'];
    $data['status'] = "okay";
}

elseif ($_POST['method'] == 'markImageForDelete'){
    $query = $pdo->prepare("UPDATE IMAGES SET MARKED_FOR_DELETE = 1 WHERE ID=?");
    $query->bindValue(1,$_POST['imageid']);
    $query->execute();
    $data['id_to_delete'] = $_POST['imageid'];
    $data['status'] = "okay";
}

elseif ($_POST['method'] == 'cleanupImages'){
	$data["clean"] = "called";
}

elseif ($_POST['method'] == "addImage") {
	if (is_uploaded_file($_FILES['image_path']['tmp_name'])) 
  { 
	
  	//First, Validate the file name
  	if(empty($_FILES['image_path']['name']))
  	{
  		$data['error'] = " File name is empty! ";
		echo json_encode($data);
  		exit;
  	}

	$upload_file_name = "img_" . substr(md5(microtime()),rand(0,26),10) . "." . pathinfo($_FILES['image_path']['name'])['extension'];
	
  	//set a limit to the file upload size
  	if ($_FILES['image_path']['size'] > 1000000) 
  	{
		$data['error'] =  " too big file ";
		echo json_encode($data);
		exit;
    }
 
    //Save the file
    $dest=$dest_dir.$upload_file_name;
    if (move_uploaded_file($_FILES['image_path']['tmp_name'], $dest)) 
    {
    	$data['status'] =  'File Has Been Uploaded !';
    }
	else
	{
		$data['error'] =  'Unknown upload error!';
		echo json_encode($data);
		exit;
	}
  }    
	$data['post'] = $_POST;
	
	$query = $pdo->prepare("INSERT INTO IMAGES (DESCRIPTION, DATE_START, DATE_END, INFINITE, FILENAME) VALUES(:description,:dstart,:dend,:infinite,:filename)");
    $query->bindValue(':description',$_POST['image_description'], PDO::PARAM_STR);
    $query->bindValue(':dstart',$_POST['image_date_start'], PDO::PARAM_STR);
    $query->bindValue(':dend',$_POST['image_date_end'], PDO::PARAM_STR);
    $query->bindValue(':infinite',$_POST['infinite'], PDO::PARAM_INT);
    $query->bindValue(':filename',$upload_file_name, PDO::PARAM_STR);
    $query->execute();
    
	$query = $pdo->prepare("SELECT ID FROM IMAGES WHERE FILENAME=?");
	$query->bindValue(1, $upload_file_name);
	$query->execute();
	$row = $query->fetch();
	$image_id = $row[0];
	
	$devices = array_map('intval', explode(',', $_POST['devices']));
	$data['row'] = $image_id;
	for ($x = 0; $x < sizeof($devices); $x++) 
	{
		$query = $pdo->prepare("INSERT INTO IMAGES_TO_DEVICES (IMAGE_ID, DEVICE_ID) VALUES(:image_id,:device_id)");
		$query->bindValue(':image_id',$image_id, PDO::PARAM_INT);
		$query->bindValue(':device_id',$devices[$x], PDO::PARAM_INT);
		$query->execute();
	} 
}
// borrowing / returning of an item
elseif ($_POST['method'] == "itemTransaction") {
    // get item from database based on barcode
    $query = $pdo->prepare("SELECT id, type, description FROM items where barcode=?");
    $query->bindValue(1,$_POST['barcode']);
    $query->execute();
        
    // - get data
    $row = $query->fetch();
    
    if($row) {
        $itemid = $row[0];
        $type = $row[1];
        $description = $row[2];
        
        // check if active transaction exists
        $query = $pdo->prepare("SELECT stampin, userid FROM transactions where itemid=? and active=1");
        $query->bindValue(1,$itemid);
        $query->execute();
        $row2 = $query->fetch();
        
        // preparation for returning item & directly borrowing by other user
        $returnFromOtherUserThanLoggedIn = 0;
        
        if($row2){
            // active = 1 -> now returning item
            $stampin = $row2[0];
            $userid_borrower = $row2[1];
            $stampout = date('Y-m-d H:i:s');
            
            // return item, set transaction to inactive in database
            $query = $pdo->prepare("UPDATE transactions SET active=0, stampout=? WHERE itemid=? and active=1");
            $query->bindValue(1,$stampout);
            $query->bindValue(2,$itemid);
            $query->execute();
            
            $data['id'] = $itemid;
            $data['type'] = $type;
            $data['description'] = $description;
            $data['stampin'] = $stampin;
            $data['stampout'] = $stampout;
            $data['active'] = 0;
            $data['status'] = "okay";
            $data['borrower'] = $userid_borrower;
            $data['session userid'] = $_SESSION['userid'];
            // check if logged in user also borrowed item. if not -> transmit item to account of logged in user
            if (($_SESSION['userid'] != $userid_borrower) && $_SESSION['userid'] != null) {
                $returnFromOtherUserThanLoggedIn = 1;
            }
        }
        // borrowing block. can be triggered if there is now active transaction for item or if the logged-in user is not the same as the borrowing user when returning an item
        if ($returnFromOtherUserThanLoggedIn == 1 || !$row2) {
            // active = 0 -> now borrowing item           
            // can only borrow if user is logged in
            if(isset($_SESSION['userid'])) {
                // insert data into database
                $userid = $_SESSION['userid'];
                $stampin = date('Y-m-d H:i:s');
                $query = $pdo->prepare("INSERT INTO transactions ('userid','itemid','active','stampin') VALUES (?,?,1,?)");
                $query->bindValue(1,$userid);
                $query->bindValue(2,$itemid);
                $query->bindValue(3,$stampin);
                $query->execute();
                
                // return data to ajax
                $data['status'] = "okay";
                $data['id'] = $itemid;
                $data['type'] = $type;
                $data['description'] = $description;
                $data['stampin'] = $stampin;
                $data['stampout'] = null;
                $data['active'] = 1;

            }
            else {
                $data['status'] = "not_logged_in";
            }
        }
    }
}
else { // item not in database
    $data['status'] = 'item_not_known';
}    
   

// -- return json data
echo json_encode($data);
exit;

?>