<?php
$site_title = "Start";
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
                    <h1 class="page-header">Managing</h1>
                </div>
            </div>
            
            <div class="panel panel-default" style="max-width: 500px; display: block; margin-left: auto; margin-right: auto">
                <div class="panel-heading"><b>Manage</b> </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-6">
                            <a href="images.php"><button class="btn btn-default btn-block" type="button">Images</button></a>
                        </div>
                        <div class="col-xs-6"> 
                            <a href="devices.php"><button class="btn btn-default btn-block" type="button">Devices</button></a>
                            
                        </div>
                    </div>
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

  

</body>
</html>
