<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();

if($_SESSION['role'] != 1)
{
	header('Location:company.php');
}

$current_user = $_SESSION['user_id'];
if(isset($_REQUEST['process']) && $_REQUEST['process'] == 'Save')
{
	$com_user_id = !isset($_REQUEST['com_user_id']) ? array() : $_REQUEST['com_user_id'];
	$contracted_company = !isset($_REQUEST['contracted_company']) ? array() : $_REQUEST['contracted_company'];
	$contracted_company_ref_id = !isset($_REQUEST['contracted_company_ref_id']) ? array() : $_REQUEST['contracted_company_ref_id'];
	
	if($_REQUEST['company_ID'] == 0)
	{
		$user_company = $app_data->company;
		$post = array(
				'company_ID'  => getNext_users_Sequence('company'),
				'company_ref'  => $_REQUEST['company_ref'],
				'company_name'  => $_REQUEST['company_name'],
				'company_address'  => $_REQUEST['company_add'],
				'company_contact' => $_REQUEST['company_contact'],
				'user_id' => json_encode( $com_user_id ),
				'contracted_name' => $contracted_company,
				'contracted_ref_id' => $contracted_company_ref_id
			);
		$user_company->insert($post);
		
		$collection = new MongoCollection($app_data, 'company');
		$Reg_Query = array('_id' => $post['_id'] ) ;
		$cursor = $collection->findOne( $Reg_Query ); 
		$company_ID_ = $cursor['company_ID'];
		
		$ids = $_REQUEST['com_user_id'];
		for($i=0;$i<=count($ids);$i++)
		{
			$collection1 = new MongoCollection($app_data, 'users');
			$criteria = array('user_id'=>(int) $ids[$i]);
			$collection1->update( $criteria ,array('$set' => array(
				'company_id'  =>(int) $company_ID_,
			)));
		}
	}
	else
	{
		$collection = new MongoCollection($app_data, 'company');
		$criteria = array('company_ID'=>(int) $_REQUEST['company_ID']);
		$collection->update( $criteria ,array('$set' => array(
		'company_name' => $_REQUEST['company_name'],
		'company_ref' => $_REQUEST['company_ref'],
		'company_address'  => $_REQUEST['company_add'],
		'company_contact' => $_REQUEST['company_contact'],
		'user_id' => json_encode( $com_user_id ),
		'contracted_name' => $contracted_company,
		'contracted_ref_id' => $contracted_company_ref_id
		)));
		
		$ids = $_REQUEST['com_user_id'];
		for($i=0;$i<=count($ids);$i++)
		{
			// $company_details1 = $app_data->company;
			// $cursor11 = $company_details1->find();
			// foreach($cursor11 as $commm)
			// {
				// $user_ids = $commm['user_id'];
				// $idsss = json_decode($user_ids);
					// if (false !== $key = array_search($ids[$i], $idsss )) {
						// unset($idsss[$key]);
					// }
					// $collection12 = new MongoCollection($app_data, 'company');
					// $criteria = array('company_ID'=>(int) $commm['company_ID']);
					// $collection12->update( $criteria ,array('$set' => array(
						// 'user_id'  => json_encode($idsss),
					// )));
			// }
			$collection1 = new MongoCollection($app_data, 'users');
			 $criteria = array('user_id'=>(int) $ids[$i]);
			 $collection1->update( $criteria ,array('$set' => array(
				'company_id'  =>(int) $_REQUEST['company_ID'],
			)));
		}
	}
	echo "<script>window.location='company.php?sucess=true'</script>";
}
include("header.php");?>
<body>
    <div id="wrapper">
       <?php include("menu.php");?>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Company</h1>
                </div>
                <div class="col-lg-6">
                                    <form role="form" action="" method="post">
									<input type="hidden" name="company_ID" value="<?php echo isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : 0;  ?>" />
									<?php
									$com_user_id = array();
									$company_name = '';
									$company_ref = '';
									$company_add = '';
									$company_contact = '';
									if(isset($_REQUEST['company_id']))
									{
										$company_details = $app_data->company;
										$cursor = $company_details->find(array('company_ID' =>(int) $_REQUEST['company_id']));
										if($cursor->count() > 0)
										{
											foreach($cursor as $company_detail)
											{
												$com_user_id = json_decode($company_detail['user_id']);
												$company_name = $company_detail['company_name'];
												$company_ref = $company_detail['company_ref'];
												$company_add = $company_detail['company_address'];
												$company_contact = $company_detail['company_contact'];
												$contracted_name = $company_detail['contracted_name'];
												$contracted_ref_id = $company_detail['contracted_ref_id'];
											}
										}
									}
									?>
										<div class="form-group">
                                            <label>Company Name</label>
                                            <input class="form-control" name="company_name" value="<?php echo $company_name; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Company Reference ID</label>
                                            <input class="form-control" name="company_ref" value="<?php echo $company_ref; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Company Address</label>
                                            <input class="form-control" name="company_add" value="<?php echo $company_add; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Company Contact</label>
                                            <input class="form-control" name="company_contact" value="<?php echo $company_contact; ?>">
                                        </div>
										<!-- Changed Select User location -->
										<div class="form-group">
                                            <label>Select User</label><br/>
											<?php
												$collection = new MongoCollection($app_data, 'users');
												$users = $collection->find();
												if($users->count() > 0) 
												{
												foreach($users as $user) { ?>	
													<input <?php echo in_array($user['user_id'],$com_user_id) ? 'checked' : ''; ?> type="checkbox" name="com_user_id[]" value="<?php echo $user['user_id']; ?>" />
													<?php echo $user['username']; ?><br/>
											<?php } } ?>
                                        </div>
										
										<h1 class="page-header">Contract Company Details : </h1>
										<?php
										if(count($contracted_name) > 0) {
										for($i=0; $i < count($contracted_name);$i++)
										{ ?>
										<div class="form-group" id="main<?php echo $i+1; ?>">
                                            <label>Contract Company <?php //echo $i+1 ?> </label>
                                            <input class="form-control" name="contracted_company[]" placeholder="Contract Company Name" value="<?php echo $contracted_name[$i]; ?>">
											<input class="form-control" name="contracted_company_ref_id[]" placeholder="Contract Company Ref. ID" value="<?php echo $contracted_ref_id[$i]; ?>">
                                        </div>
										
										
										<?php }  
										echo '<div class="form-group" id="main"> </div>' ; ?>
										<script> var tot = <?php echo count($contracted_name)+1; ?>; var edit = true; </script>
										<?php } else { ?>
										
										<div class="form-group" id="main">
                                            <label>Contract Company  </label>
                                            <input class="form-control" name="contracted_company[]" placeholder="Contract Company Name">
											<input class="form-control" name="contracted_company_ref_id[]" placeholder="Contract Company Ref. ID">
                                        </div>
										<script> var tot = 2; </script>
										<?php }  ?>
										
										<input type='button' value='Add Button' id='addButton'>
										<input type='button' value='Remove Button' id='removeButton'>
										
										<script type="text/javascript">
											$(document).ready(function()
											{
											var counter = tot;
											$("#addButton").click(function () 
												{
													 if(counter < 2) {
														 counter = 2;
													 }
													if(counter>100)
													{
															alert("Only 10 textboxes allow");
															return false;
													}
													$('#main').append('<div class="form-group" id="main'+counter+'"><label>Contract Company </label><input class="form-control" name="contracted_company[]" placeholder="Contract Company Name" value=""><input class="form-control" name="contracted_company_ref_id[]" placeholder="Contract Company Ref. ID" value=""></div>');
													counter++;
												});
												$("#removeButton").click(function () 
												{
													var delete_node = counter-1;
													//alert(delete_node);
													if(delete_node > 0 || delete_node != 1) {
													$('#main'+delete_node).remove();
													counter--;
													}
													if(delete_node < 1) {
														counter = 1;
													}
												});
											});
										</script>
										<br/><br/>
                                        <input type="submit" class="btn btn-default" value="Save" name="process">
                                    </form>
                                </div>
              </div>
        </div>
    </div>
<?php include("footer.php");?>
