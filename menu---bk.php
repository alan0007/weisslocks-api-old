 <!-- Navigation -->
	<div class="navigation">
 
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
               <h1> <a class="navbar-brand" href="index.php"><img src="images/weisslocks-logo.png" width="121"></a></h1>
            </div>
            <!-- /.navbar-header -->
            <ul class="nav navbar-top-links navbar-right">
                <!-- /.dropdown -->
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                       <!-- <li><a href="#"><i class="fa fa-user fa-fw"></i> User Profile</a>
                        </li>
                        <li><a href="#"><i class="fa fa-gear fa-fw"></i> Settings</a>
                        </li>
                        <li class="divider"></li>-->
						<li><a href="profile_update.php"><i class="fa fa-sign-out fa-fw"></i> Update Profile </a>
						
                        <li><a href="logout.php"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user --> 
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->
            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
					<?php 
					$actual_link = basename($_SERVER['PHP_SELF']);
					$file_name = basename($actual_link,'.php');
					
					
					
					
				//	echo '---'.$_SESSION['role'].'---';
					
					
					
					?>
					<li>
						<a class="<?php echo $file_name == 'index' ? 'active' : ''; ?>" href="index.php"><i class="fa fa-dashboard fa-fw"></i>Dashboard</a>
					</li>
					<li>
						<a class="<?php echo $file_name == 'users' || $file_name == 'manageusers' ? 'active' : ''; ?>" href="users.php"><i class="fa fa-user fa-fw"></i>Users</a>
					</li>
					<li>
						<a class="<?php echo $file_name == 'company' || $file_name == 'managecompany' ? 'active' : ''; ?>" href="company.php"><i class="fa fa-building fa-fw"></i>Company</a>
					</li>
					<li>
						<a class="<?php echo $file_name == 'locks' || $file_name == 'managelocks' ? 'active' : ''; ?>" href="locks.php"><i class="fa fa-lock fa-fw"></i>Locks</a>
					</li>
					<li>
						<a class="<?php echo $file_name == 'keys' || $file_name == 'managekeys' ? 'active' : ''; ?>" href="keys.php"><i class="fa fa-key fa-fw"></i>Keys</a>
					</li>
					<li>
						<a class="<?php echo $file_name == 'lockgroup' || $file_name == 'manage_lockgroup' ? 'active' : ''; ?>" href="lockgroup.php"><i class="fa fa-tag fa-fw"></i>Lock Group</a>
					</li>
					<li>
						<a class="<?php echo $file_name == 'keygroup' || $file_name == 'manage_keygroup' ? 'active' : ''; ?>" href="keygroup.php"><i class="fa fa-tag fa-fw"></i>Key Group</a>
					</li>
					<li>
						<a class="<?php echo $file_name == 'key_and_lock_group_pairing' || $file_name == 'manage_key_and_lock_group_pairing' ? 'active' : ''; ?>" href="key_and_lock_group_pairing.php"><i class="fa fa-tags fa-fw"></i><!--Key Group and Lock Group Pairing--> Access Control </a>
					</li>
					<?php if($_SESSION['role'] == 1) { ?>
						<li>
							<a class="<?php echo $file_name == 'payment' || $file_name == 'managepayment' ? 'active' : ''; ?>" href="payment.php"><i class="fa fa-dollar fa-fw"></i>Payment</a> 
						</li>
					<?php } ?>
					<?php if($_SESSION['role'] != 1) { ?>
						<li>
							<a class="<?php echo $file_name == 'settings' ? 'active' : ''; ?>" href="settings.php"><i class="fa fa-gear fa-fw"></i>Settings</a>
						</li>
					<?php } else { ?>
						<li>
								<a class="<?php echo $file_name == 'admin-settings' || $file_name == 'manage-admin-settings' ? 'active' : ''; ?>" href="admin-settings.php"><i class="fa fa-gear fa-fw"></i>Settings</a>
						</li>
					<?php } ?>
						<li>
							<a class="<?php echo $file_name == 'history' ? 'active' : ''; ?>" href="history.php"><i class="fa fa-history fa-fw"></i>History Logs</a>
						</li>
						<li>
							<a class="<?php echo $file_name == 'accesscode' ? 'active' : ''; ?>" href="accesscode.php"><i class="fa fa-unlock fa-fw"></i>Access Code</a>
						</li>
					
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>
		
	</div>