<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'Save')
{
	if($_REQUEST['payment_ID'] == 0)
	{
		$keys = $app_data->payment;
		$post = array(
				'payment_ID'  => getNext_users_Sequence('payment_ID'),
				'payment_user_id'  => $_REQUEST['payment_user_id'],
				'invoice_number' => 'INVOICE-'.getNext_users_Sequence('invoice_number'),
				'amount'  => $_REQUEST['amount'],
				'date_from'  => $_REQUEST['date_from'],
				'date_to'  => $_REQUEST['date_to'],
				'payment_status'  => $_REQUEST['payment_status'],
			);
		$keys->insert($post);
	}
	else
	{
		$collection = new MongoCollection($app_data, 'payment');
		$criteria = array('payment_ID'=>(int) $_REQUEST['payment_ID']);
		$collection->update( $criteria ,array('$set' => array(
				'payment_user_id'  => $_REQUEST['payment_user_id'],
				'amount'  => $_REQUEST['amount'],
				'date_from'  => $_REQUEST['date_from'],
				'date_to'  => $_REQUEST['date_to'],
				'payment_status'  => $_REQUEST['payment_status']
		)));
	}
	echo "<script>window.location='payment.php?sucess=true'</script>"; 
}
include("header.php");?>
<body>
    <div id="wrapper">
       <?php include("menu.php");?>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Payment</h1>
                </div>
                <div class="col-lg-6">
                                    <form role="form" action="" method="post">
									<input type="hidden" name="payment_ID" value="<?php echo isset($_REQUEST['payment_ID']) ? $_REQUEST['payment_ID'] : 0;  ?>" />
									<?php
									
									if(isset($_REQUEST['payment_ID']))
									{
										$payment_details = $app_data->payment;
										$cursor = $payment_details->find(array('payment_ID' =>(int) $_REQUEST['payment_ID']));
										if($cursor->count() > 0)
										{
											foreach($cursor as $payment_detail)
											{
												$payment_user_id = $payment_detail['payment_user_id'];
												$amount = $payment_detail['amount'];
												$date_from = $payment_detail['date_from'];
												$date_to = $payment_detail['date_to'];
												$payment_status = $payment_detail['payment_status'];
											}
										}
									}
									?>
										<div class="form-group">
                                            <label> User ID (Linked to user) </label>
											<select name="payment_user_id" class="form-control">
											<?php
												$collection = new MongoCollection($app_data, 'users');
												$users = $collection->find();
												if($users->count() > 0)
												{?>
														<?php foreach($users as $user) { ?>
															<option <?php echo $payment_user_id == $user['user_id'] ? 'selected="selected"' : ''; ?> value="<?php echo $user['user_id']; ?>"> <?php echo $user['username']; ?> </option>
														<?php }
												}
											?>
											</select>
                                        </div>
										<div class="form-group">
                                            <label>Amount</label>
                                            <input class="form-control" name="amount" value="<?php echo $amount; ?>">
                                        </div>
                                        <script>
										  $( function() {
											$( "#date_from,#date_to" ).datepicker({
											  dateFormat: "dd-mm-yy"
											});
										  } );
										</script>
										<div class="form-group">
                                            <label>Date from (in DD/MM/YYYY format)</label>
											<input class="form-control" type="text" name="date_from" id="date_from" value="<?php echo $date_from; ?>">
                                        </div>
										
										<div class="form-group">
                                            <label>Date from (in DD/MM/YYYY format)</label>
											<input class="form-control" type="text" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                                        </div>
										<div class="form-group">
                                            <label> Payment Status </label>
											<select name="payment_status" class="form-control">
												<option <?php echo $payment_status == 3 ? 'selected="selected"' : ''; ?> value="3"> Processing </option>
												<option <?php echo $payment_status == 1 ? 'selected="selected"' : ''; ?> value="1"> Ok </option>
												<option <?php echo $payment_status == 2 ? 'selected="selected"' : ''; ?> value="2"> Not Paid </option>
											</select>
                                        </div>
                                        <input type="submit" class="btn btn-default" value="Save" name="process">
                                    </form>
                                </div>
              </div>
        </div>
    </div>
<?php include("footer.php");?>
