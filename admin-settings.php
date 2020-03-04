<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();

if($_SESSION['role'] != 1)
{
	header('Location:index.php');
}
$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'keys' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'keys');
	$collection->remove( array( 'key_ID' =>(int) $_REQUEST['id'] ) );
	$msg = "Deleted Sucessfully!!";
}
?>
<style>
    .navwrap li{
        list-style: none;
        display: inline;
    }
    .pag-selected { 
        font-weight: bold;
        text-decoration: underline;
    }
    .navwrap a{
        color:black;
    }
</style>

<?php include("header.php");?>

<body>

    <div id="wrapper">

       <?php include("menu.php");?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Settings</h1>
                </div>
                <div class="col-lg-12">
					<!--<div style="width:150px;clear:both;padding-left:30px;"><a class="btn btn-lg btn-primary btn-block" href="managekeys.php"> Add Keys </a></div>-->
                    <?php if(isset($_REQUEST['sucess'])){?>
                    <div style="color:green;text-align: center;">Keys Added or updated Sucessfully!!</div>
                    <?php } ?>
                    <?php if(isset($msg) && $msg != ''){?>
                    <div style="color:green;text-align: center;"><?php echo $msg; ?></div>
                    <?php } ?>
                     <div class="panel-body">
                            <div class="table-responsive">
							
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
											<th>Company ID</th>
											<th>Company Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									  <?php
										$settings = $app_data->settings;
										$cursor = $settings->find();
										if($cursor->count() > 0)
										{
										  foreach($cursor as $settings)
										  {
											  if($settings['company_id'] != 0)
											  { ?>
											 <tr>
												<th><?php echo $settings['company_id']; ?></th>
												<th><?php $company = $app_data->company;
												$cursor = $company->findOne(array('company_ID'=>(int)$settings['company_id']));
												if(!empty( $cursor['company_ID']))
												{
													echo $cursor['company_name'];
												}
												?></th>
												<th>
												<a href="manage-admin-settings.php?setting_id=<?php echo $settings['setting_id']; ?>">  Settings </a>
												<!--<a onclick="return confirm('Are you sure?')" href="keys.php?delete=keys&id=<?php echo $keys['key_ID']; ?>">  Delete </a>-->
												</th>
											</tr>
										<?php } } } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.panel-body -->
                </div>
                <!-- /.col-lg-12 -->
            </div>
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

<?php include("footer.php");?>
