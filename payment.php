<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();

if(isset($_REQUEST['delete']) && $_REQUEST['delete'] == 'payment' && $_REQUEST['id'] != '')
{
	$collection = new MongoCollection($app_data, 'payment');
	$collection->remove( array( 'payment_ID' =>(int) $_REQUEST['id'] ) );
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
                    <h1 class="page-header">Payment</h1>
                </div>
                <div class="col-lg-12">
                    <div style="width:180px;clear:both;padding-left:30px;"><a class="btn btn-lg btn-primary btn-block" href="managepayment.php"> Add Payment </a></div>
                    <?php if(isset($_REQUEST['sucess'])){?>
                    <div style="color:green;text-align: center;">Payment Added or updated Sucessfully!!</div>
                    <?php } ?>
                    <?php if(isset($msg) && $msg != ''){?>
                    <div style="color:green;text-align: center;"><?php echo $msg; ?></div>
                    <?php } ?>
                     <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover" id="dataTables-example">
                                    <thead>
                                        <tr>
											<th>Payment ID</th>
                                            <th>User </th>
                                            <th>Amount</th>
                                            <th>Status</th>
											<th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
									  <?php
										$payment = $app_data->payment;
									  $cursor = $payment->find();
									  if($cursor->count() > 0)
									  {
										  foreach($cursor as $payment)
										  { ?>
											 <tr>
												<th><?php echo $payment['payment_ID']; ?></th>
												<th><?php
													$collection = new MongoCollection($app_data, 'users');
													 $check = $collection->find(array('user_id'=>(int) $payment['payment_user_id']));
													 foreach($check as $users)
													 {
														echo $users['username'];
													 }
												?></th>
												<th><?php echo $payment['amount']; ?></th>
												<th><?php echo $payment['payment_status'] == 3 ? 'Processing' : ($payment['payment_status'] == 2 ? 'Not Paid' : 'Ok');?></th>
												<th>
												<a href="managepayment.php?payment_ID=<?php echo $payment['payment_ID']; ?>">  Edit </a>
												<a onclick="return confirm('Are you sure?')" href="payment.php?delete=payment&id=<?php echo $payment['payment_ID']; ?>">  Delete </a>
												</th>
											</tr>
											  <?php } }
									  else
										{
											  echo '<tr class="odd gradeX"><td colspan="5">No Payment Founds </td></tr>';
										}
									  ?>
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
