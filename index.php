<?php
include(dirname(__FILE__).'/configurations/config.php');
checklogin();
$current_user = $_SESSION['user_id'];
$in = 0;
$out = 0;

//Finf User Details
if($_SESSION['role'] != 1)
{
	$collection_users = new MongoCollection($app_data, 'users');
	$cursor_user = $collection_users->find(array('user_id'=>$current_user));
	if($cursor_user->count() > 0)
	{
		foreach($cursor_user as $user)
		{
			$username = $user['username'];
			$role = $user['role'];	
			$company_id = $user['company_id'];
		}
	}

	//Find Company Details
	$collection_company = new MongoCollection($app_data, 'company');												
	$query_company = array( 'company_ID' => (int)$company_id ); 
	$cursor_company = $collection_company->find( $query_company );
	if($cursor_company->count() > 0) { 
		foreach($cursor_company as $company)
		{
			$company_name = $company['company_name'];
			$company_ref_id = $company['company_ref'];
		}										
	}
	else {
		$company_name = "No Company";
	}
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
<?php
include("header.php");
?>

<body>

    <div id="wrapper">

       <?php //include("nav-header.php");?>
       <?php include("menu.php");?>

        <div id="page-wrapper1">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Welcome, <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?></h1>					
                </div>
                <!-- /.col-lg-12 -->
            </div>
             
            <?php /*
			<div class="row" style="display: none;">
                <div class="">
					<div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-clock-o fa-fw"></i> Responsive Timeline
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <ul class="timeline">
                                <li>
                                    <div class="timeline-badge"><i class="fa fa-check"></i>
                                    </div>
                                    <div class="timeline-panel">
                                        <div class="timeline-heading">
                                            <h4 class="timeline-title">Lorem ipsum dolor</h4>
                                            <p><small class="text-muted"><i class="fa fa-clock-o"></i> 11 hours ago via Twitter</small>
                                            </p>
                                        </div>
                                        <div class="timeline-body">
                                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Libero laboriosam dolor perspiciatis omnis exercitationem. Beatae, officia pariatur? Est cum veniam excepturi. Maiores praesentium, porro voluptas suscipit facere rem dicta, debitis.</p>
                                        </div>
                                    </div>
                                </li>
                                
                            </ul>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
                  
			</div>
			<!-- /#page-wrapper -->
			*/ ?>
		</div>
		<!-- /#wrapper -->
    
    <!-- Page -->
	
    <div id="page-wrapper" class="page">
		<div class="page-content container-fluid">			
			<div class="row" data-plugin="matchHeight" data-by-row="true">
				<div class="col-xxl-8 col-lg-12">
					<!-- General Statistic -->
					<div class="card card-shadow" id="widgetStatistic">
						<div class="card-block p-30">
							<div class="row no-space" data-plugin="matchHeight">
								<div class="col-7">
									<p class="font-size-20 blue-grey-700">Company: <?php echo $company_name; ?></p>
									<p>Permit To Enter Management & Report</p>
									<p class="font-size-20 blue-grey-700">Statistic</p>
									<p class="blue-grey-400">Status: Demo</p>
									<p>
										<i class="icon wb-map blue-grey-400 mr-10" aria-hidden="true"></i>
										<span>Location: SP HQ</span>
									</p>
									
									<ul class="list-unstyled mt-20">
										<li>
											<p>
												Permit: <span>
												<?php 
												$collection_permit_to_enter = $app_data->permit_to_enter;
												$cursor_permit = $collection_permit_to_enter->find(array('company_id' => (string)$company_id ));
												$count = $cursor_permit->count();
												echo $count;
												?>
												</span>
											</p>
											<div class="progress progress-xs mb-25">
												<div class="progress-bar progress-bar-info bg-blue-600" style="width: 70.3%" aria-valuemax="100"
													aria-valuemin="0" aria-valuenow="70.3" role="progressbar">
													<span class="sr-only">70.3%</span>
												</div>
											</div>
										</li>
										<li>
											<p>
												QR Code Gantry Access: <span>
												<?php 
												$collection_qr_code = $app_data->qrcode;
												$cursor_qr_code = $collection_qr_code->find(array('company_ref_id' => $company_ref_id ));
												$count = $cursor_qr_code->count();
												echo $count;
												?>
												</span>
											</p>
											<div class="progress progress-xs mb-25">
												<div class="progress-bar progress-bar-info bg-green-600" style="width: 70.3%" aria-valuemax="100"
													aria-valuemin="0" aria-valuenow="70.3" role="progressbar">
													<span class="sr-only">70.3%</span>
												</div>
											</div>
										</li>
										<li>
											<p>
												Beacon: <span>
												<?php 
												$collection_beacon = $app_data->beacon;
												$cursor_beacon = $collection_beacon->find(array('company_id' => $company_id ));
												$count = $cursor_beacon->count();
												echo $count;
												?>
												</span>
											</p>
											<div class="progress progress-xs mb-25">
												<div class="progress-bar progress-bar-info bg-green-600" style="width: 70.3%" aria-valuemax="100"
													aria-valuemin="0" aria-valuenow="70.3" role="progressbar">
													<span class="sr-only">70.3%</span>
												</div>
											</div>
										</li>
										<li>
											<p>
												User Location Tracking: <span>
												<?php 
												$collection_user_location = $app_data->user_location;
												$cursor_user_location = $collection_user_location->find(array('company_id' => $company_id ));
												$count = $cursor_user_location->count();
												echo $count;
												?>
												</span>
											</p>
											<div class="progress progress-xs mb-0">
												<div class="progress-bar progress-bar-info bg-purple-600" style="width: 70.3%"
													aria-valuemax="100" aria-valuemin="0" aria-valuenow="70.3"
													role="progressbar">
													<span class="sr-only">70.3%</span>
												</div>
											</div>
										</li>										
									</ul>
								</div>
							</div>
							<br/>
						</div>
					</div>
					<!-- End General Statistic -->
				</div>

				<div class="col-xxl-8 col-lg-12">
					<!-- Fire Alarm Statistic -->
					<div class="card card-shadow" id="widgetStatistic">
						<div class="card-block p-30">
							<h3 class="card-title">
								<span class="text-truncate">Fire Alarm</span>							
								<div class="card-header-actions">
									<span class="green-600 font-size-24">									
										<?php 
										$collection_fire_alarm = $app_data->fire_alarm;
										$cursor_fire_alarm = $collection_fire_alarm->find(array('company_id' => (int)$company_id ));
										$count = $cursor_fire_alarm->count();
										$cursor_fire_alarm = $cursor_fire_alarm->sort(array('fire_alarm_id' => -1))->limit(3);
										echo $count;
										?>									
									</span>
								</div>
							</h3>
									
							<?php 
							if ($cursor_fire_alarm->count()>0){
							?>
							<div class="h-300" data-plugin="scrollable">
								<div data-role="container">
									<div data-role="content">
										<table class="table mb-0">
											<thead>
												<th>Date</th>
												<th>Purpose</th>
												<th>Attendance</th>
											</thead>
											<tbody>
											
											<?php
											foreach($cursor_fire_alarm as $fire_alarm)
												{													
  													$fire_alarm_id = $fire_alarm['fire_alarm_id'];
													$company_id = $fire_alarm['company_id'];
													//Find Company name
													$collection_company = new MongoCollection($app_data, 'company');												
													$query_company = array( 'company_ID' => (int)$company_id ); 
													$cursor_company = $collection_company->find( $query_company );
													if($cursor_company->count() > 0) { 
														foreach($cursor_company as $company)
														{
															$company_name = $company['company_name'];
															$company_ref_id = $company['company_ref'];
														}										
													}
														
													$user_id = $fire_alarm['trigger_user_id'];
													//Find Username
													$collection_users = new MongoCollection($app_data, 'users');
													$cursor_user = $collection_users->find(array('user_id'=>$user_id));
													if($cursor_user->count() > 0)
													{
														foreach($cursor_user as $user)
														{
															$username = $user['username'];
															$role = $user['role'];														
														}
													}
													
													$location_id = $fire_alarm['location_id'];
													$location_name = $fire_alarm['location_name'];
													$time = $fire_alarm['time'];
													$purpose = $fire_alarm['purpose'];
													$message = $fire_alarm['message'];	
												?>
												<tr>
													<td><?php echo $time;?></td>
													<td><?php echo $purpose;?></td>
													<td>
														Total: + 1458 <br/>
														<span  class="green-600">Present: +1000</span><br/>
														<span class="red-600">Missing: + 458</span><br/>
													</td>
												</tr>
												<?php 
												} ?>									
											</tbody>
										</table>
									</div>
								</div>
							</div>
							
							<?php 
							} ?>
						</div>
					</div>
					<!-- End Fire Alarm Statistic -->
				</div>	
			</div>
		</div>		
		  
		<?php
	  /*
        <div class="row" data-plugin="matchHeight" data-by-row="true">
          <div class="col-xxl-7 col-lg-7">
            <!-- Widget Linearea Color -->
            <div class="card card-shadow card-responsive" id="widgetLineareaColor">
              <div class="card-block p-0">
                <div class="pt-30 p-30" style="height:calc(100% - 250px);">
                  <div class="row">
                    <div class="col-7">
                      <p class="font-size-20 blue-grey-700">Eneergy Predictions</p>
                      <p>Quisque volutpat condimentum velit. Class aptent taciti</p>
                      <div class="counter counter-md text-left">
                        <div class="counter-number-group">
                          <span class="counter-icon red-600"><i class="icon wb-triangle-up" aria-hidden="true"></i></span>
                          <span class="counter-number red-600">2,250</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-5">
                      <div class="float-right clearfix">
                        <ul class="list-unstyled">
                          <li class="mb-5 text-truncate">
                            <i class="icon wb-medium-point red-600 mr-5" aria-hidden="true"></i>                            Diretary intake
                          </li>
                          <li class="mb-5 text-truncate">
                            <i class="icon wb-medium-point orange-600 mr-5" aria-hidden="true"></i>                            Motion
                          </li>
                          <li class="mb-5 text-truncate">
                            <i class="icon wb-medium-point green-600 mr-5" aria-hidden="true"></i>                            Other
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="ct-chart h-250"></div>
              </div>
            </div>
            <!-- End Widget Linearea Color -->
          </div>

          <div class="col-xxl-5 col-lg-5">
            <!-- Widget Stacked Bar -->
            <div class="card card-shadow" id="widgetStackedBar">
              <div class="card-block p-0">
                <div class="p-30 h-150">
                  <p>MARKET DOW</p>
                  <div class="red-600">
                    <i class="wb-triangle-up font-size-20 mr-5"></i>
                    <span class="font-size-30">26,580.62</span>
                  </div>
                </div>
                <div class="counters pb-20 px-30" style="height:calc(100% - 350px);">
                  <div class="row no-space">
                    <div class="col-4">
                      <div class="counter counter-sm">
                        <div class="counter-label text-uppercase">APPL</div>
                        <div class="counter-number-group text-truncate">
                          <span class="counter-number-related green-600">+</span>
                          <span class="counter-number green-600">82.24</span>
                          <span class="counter-number-related green-600">%</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="counter counter-sm">
                        <div class="counter-label text-uppercase">FB</div>
                        <div class="counter-number-group text-truncate">
                          <span class="counter-number-related red-600">-</span>
                          <span class="counter-number red-600">12.06</span>
                          <span class="counter-number-related red-600">%</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="counter counter-sm">
                        <div class="counter-label text-uppercase">GOOG</div>
                        <div class="counter-number-group text-truncate">
                          <span class="counter-number-related green-600">+</span>
                          <span class="counter-number green-600">24.86</span>
                          <span class="counter-number-related green-600">%</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="ct-chart h-200"></div>
              </div>
            </div>
            <!-- End Widget Stacked Bar -->
          </div>

          <div class="col-xxl-8 col-lg-12">
            <!-- Widget Statistic -->
            <div class="card card-shadow" id="widgetStatistic">
              <div class="card-block p-0">
                <div class="row no-space h-full" data-plugin="matchHeight">
                  <div class="col-md-8 col-sm-12">
                    <div id="widgetJvmap" class="h-full"></div>
                  </div>
                  <div class="col-md-4 col-sm-12 p-30">
                    <div class="form-group">
                      <div class="input-search input-search-dark">
                        <i class="input-search-icon wb-search" aria-hidden="true"></i>
                        <input type="text" class="form-control" name="" placeholder="Search...">
                      </div>
                    </div>
                    <p class="font-size-20 blue-grey-700">Statistic</p>
                    <p class="blue-grey-400">Status: live</p>
                    <p>
                      <i class="icon wb-map blue-grey-400 mr-10" aria-hidden="true"></i>
                      <span>258 Countries, 4835 Cities</span>
                    </p>
                    <ul class="list-unstyled mt-20">
                      <li>
                        <p>VISITS</p>
                        <div class="progress progress-xs mb-25">
                          <div class="progress-bar progress-bar-info bg-blue-600" style="width: 70.3%" aria-valuemax="100"
                            aria-valuemin="0" aria-valuenow="70.3" role="progressbar">
                            <span class="sr-only">70.3%</span>
                          </div>
                        </div>
                      </li>
                      <li>
                        <p>TODAY</p>
                        <div class="progress progress-xs mb-25">
                          <div class="progress-bar progress-bar-info bg-green-600" style="width: 70.3%" aria-valuemax="100"
                            aria-valuemin="0" aria-valuenow="70.3" role="progressbar">
                            <span class="sr-only">70.3%</span>
                          </div>
                        </div>
                      </li>
                      <li>
                        <p>WEEK</p>
                        <div class="progress progress-xs mb-0">
                          <div class="progress-bar progress-bar-info bg-purple-600" style="width: 70.3%"
                            aria-valuemax="100" aria-valuemin="0" aria-valuenow="70.3"
                            role="progressbar">
                            <span class="sr-only">70.3%</span>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Widget Statistic -->
          </div>

          <div class="col-xxl-4 col-lg-12">
            <div class="row h-full">
              <div class="col-xxl-12 col-lg-6 h-p50 h-only-lg-p100 h-only-xl-p100">
                <!-- Widget Linepoint -->
                <div class="card card-inverse card-shadow bg-blue-600 white" id="widgetLinepoint">
                  <div class="card-block p-0">
                    <div class="pt-25 px-30">
                      <div class="row no-space">
                        <div class="col-6">
                          <p>Today Sale's</p>
                          <p class="blue-200">Last Sale 23.45 USD</p>
                        </div>
                        <div class="col-6 text-right">
                          <p class="font-size-30 text-nowrap">450 USD</p>
                        </div>
                      </div>
                    </div>
                    <div class="ct-chart h-120"></div>
                  </div>
                </div>
                <!-- End Widget Linepoint -->
              </div>
              <div class="col-xxl-12 col-lg-6 h-p50 h-only-lg-p100 h-only-xl-p100">
                <!-- Widget Sale Bar -->
                <div class="card card-inverse card-shadow bg-purple-600 white" id="widgetSaleBar">
                  <div class="card-block p-0">
                    <div class="pt-25 px-30">
                      <div class="row no-space">
                        <div class="col-6">
                          <p>Month Sale's</p>
                          <p class="purple-200">2% higher than last month</p>
                        </div>
                        <div class="col-6 text-right">
                          <p class="font-size-30 text-nowrap">$ 14,500</p>
                        </div>
                      </div>
                    </div>
                    <div class="ct-chart h-120"></div>
                  </div>
                </div>
                <!-- End Widget Sale Bar -->
              </div>
            </div>
          </div>

          <div class="col-xxl-6 col-lg-12">
            <!-- Widget OVERALL VIEWS -->
            <div class="card card-shadow card-responsive" id="widgetOverallViews">
              <div class="card-block p-30">
                <div class="row pb-30" style="height:calc(100% - 250px);">
                  <div class="col-sm-4">
                    <div class="counter counter-md text-left">
                      <div class="counter-label">OVERALL VIEWS</div>
                      <div class="counter-number-group text-truncate">
                        <span class="counter-number-related red-600">$</span>
                        <span class="counter-number red-600">432,852</span>
                      </div>
                      <div class="counter-label">2% higher than last month</div>
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <div class="counter counter-sm text-left inline-block">
                      <div class="counter-label">MY BALANCE</div>
                      <div class="counter-number-group">
                        <span class="counter-number-related">$</span>
                        <span class="counter-number">12,346</span>
                      </div>
                    </div>
                    <div class="ct-chart inline-block small-bar-one"></div>
                  </div>
                  <div class="col-sm-4">
                    <div class="counter counter-sm text-left inline-block">
                      <div class="counter-label">NEW ORDERS</div>
                      <div class="counter-number-group">
                        <span class="counter-number-related">$</span>
                        <span class="counter-number">17,555</span>
                      </div>
                    </div>
                    <div class="ct-chart inline-block small-bar-two"></div>
                  </div>
                </div>
                <div class="ct-chart line-chart h-250"></div>
              </div>
            </div>
            <!-- End Widget OVERALL VIEWS -->
          </div>

          <div class="col-xxl-6 col-lg-12">
            <!-- Widget Timeline -->
            <div class="card card-shadow card-responsive" id="widgetTimeline">
              <div class="card-block p-0">
                <div class="p-30" style="height:120px;">
                  <div class="row">
                    <div class="col-4">
                      <div class="counter text-left">
                        <div class="counter-label blue-grey-700">Total usage</div>
                        <div class="counter-number-group">
                          <span class="counter-number red-600">21,451</span>
                          <span class="counter-number-related red-600">MB</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="counter text-left">
                        <div class="counter-label">Currently</div>
                        <div class="counter-number-group">
                          <span class="counter-number">227.34</span>
                          <span class="counter-number-related">KB</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-4">
                      <div class="counter text-left">
                        <div class="counter-label">Average</div>
                        <div class="counter-number-group">
                          <span class="counter-number">117.65</span>
                          <span class="counter-number-related">MB</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <ul class="list-unstyled pb-50 mb-0" style="height:calc(100% - 270px);">
                  <li class="px-30 py-15 container-fluid">
                    <div class="row">
                      <div class="col-3">Mail App</div>
                      <div class="col-6">210,685 users are using</div>
                      <div class="col-3 green-600">227.34KB</div>
                    </div>
                  </li>
                  <li class="px-30 py-15 container-fluid">
                    <div class="row">
                      <div class="col-3">Calendar</div>
                      <div class="col-6">10,685 users are using</div>
                      <div class="col-3 green-600">128.62KB</div>
                    </div>
                  </li>
                </ul>
                <div class="ct-chart h-150"></div>
              </div>
            </div>
            <!-- End Widget Timeline -->
          </div>

          <div class="col-xxl-6 col-lg-12">
            <!-- Panel California -->
            <div class="card card-shadow" id="widgetWeather">
              <div class="row no-space">
                <div class="col-md-7">
                  <div class="p-35 text-center">
                    <h4>California, Usa</h4>
                    <p class="blue-grey-400 mb-35">MONDAY MAY 11, 2017</p>
                    <canvas id="widgetSunny" height="60" width="60"></canvas>
                    <div class="font-size-40 red-600">
                      26°
                      <span class="font-size-30">C</span>
                    </div>
                    <div>Sunday</div>
                  </div>
                  <div class="weather-times p-30">
                    <div class="row no-space text-center">
                      <div class="col-3">
                        <div class="weather-day vertical-align">
                          <div class="vertical-align-middle">
                            <div class="mb-5">12:00</div>
                            <i class="wi-day-cloudy font-size-24 mb-5"></i>
                            <div class="red-600">24°
                              <span class="font-size-12">C</span>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="weather-day vertical-align">
                          <div class="vertical-align-middle">
                            <div class="mb-5">12:30</div>
                            <i class="wi-day-sunny font-size-24 mb-5"></i>
                            <div class="red-600">26°
                              <span class="font-size-12">C</span>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="weather-day vertical-align">
                          <div class="vertical-align-middle">
                            <div class="mb-5">13:00</div>
                            <i class="wi-day-sunny font-size-24 mb-5"></i>
                            <div class="red-600">28°
                              <span class="font-size-12">C</span>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="col-3">
                        <div class="weather-day vertical-align">
                          <div class="vertical-align-middle">
                            <div class="mb-5">13:30</div>
                            <i class="wi-day-sunny font-size-24 mb-5"></i>
                            <div class="red-600">30°
                              <span class="font-size-12">C</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-5 bg-blue-grey-100">
                  <div class="weather-list">
                    <ul class="list-unstyled m-0">
                      <li class="container-fluid">
                        <div class="row no-space">
                          <div class="col-4">
                            SUN
                          </div>
                          <div class="col-4">
                            <i class="wi-day-cloudy font-size-24"></i>
                          </div>
                          <div class="col-4">
                            24 - 26
                          </div>
                        </div>
                      </li>
                      <li class="container-fluid">
                        <div class="row no-space">
                          <div class="col-4">
                            SUN
                          </div>
                          <div class="col-4">
                            <i class="wi-day-cloudy font-size-24"></i>
                          </div>
                          <div class="col-4">
                            24 - 26
                          </div>
                        </div>
                      </li>
                      <li class="container-fluid">
                        <div class="row no-space">
                          <div class="col-4">
                            SUN
                          </div>
                          <div class="col-4">
                            <i class="wi-day-cloudy font-size-24"></i>
                          </div>
                          <div class="col-4">
                            24 - 26
                          </div>
                        </div>
                      </li>
                      <li class="container-fluid">
                        <div class="row no-space">
                          <div class="col-4">
                            SUN
                          </div>
                          <div class="col-4">
                            <i class="wi-day-cloudy font-size-24"></i>
                          </div>
                          <div class="col-4">
                            24 - 26
                          </div>
                        </div>
                      </li>
                      <li class="container-fluid">
                        <div class="row no-space">
                          <div class="col-4">
                            SUN
                          </div>
                          <div class="col-4">
                            <i class="wi-day-cloudy font-size-24"></i>
                          </div>
                          <div class="col-4">
                            24 - 26
                          </div>
                        </div>
                      </li>
                      <li class="container-fluid">
                        <div class="row no-space">
                          <div class="col-4">
                            SUN
                          </div>
                          <div class="col-4">
                            <i class="wi-day-cloudy font-size-24"></i>
                          </div>
                          <div class="col-4">
                            24 - 26
                          </div>
                        </div>
                      </li>
                      <li class="container-fluid">
                        <div class="row no-space">
                          <div class="col-4">
                            SUN
                          </div>
                          <div class="col-4">
                            <i class="wi-day-cloudy font-size-24"></i>
                          </div>
                          <div class="col-4">
                            24 - 26
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Panel California -->
          </div>

          <div class="col-xxl-3 col-lg-6">
            <!-- Panel Watchlist -->
            <div class="card card-shadow" id="widgetTable">
              <div class="card-block p-30">
                <h3 class="card-title">
                  <span class="text-truncate">Watch List</span>
                  <div class="card-header-actions">
                    <span class="red-600 font-size-24">$ 102,967</span>
                  </div>
                </h3>
                <form class="mt-25" action="#" role="search">
                  <div class="input-search input-search-dark">
                    <i class="input-search-icon wb-search" aria-hidden="true"></i>
                    <input type="text" class="form-control" placeholder="Search.." />
                  </div>
                </form>
              </div>
              <div class="h-300" data-plugin="scrollable">
                <div data-role="container">
                  <div data-role="content">
                    <table class="table mb-0">
                      <tbody>
                        <tr>
                          <td>GMY</td>
                          <td>$ 9,500</td>
                          <td class="green-600">+ 458</td>
                        </tr>
                        <tr>
                          <td>KPM</td>
                          <td>$ 15,425</td>
                          <td class="red-600">- 1,632</td>
                        </tr>
                        <tr>
                          <td>PTR</td>
                          <td>$ 11,540</td>
                          <td class="green-600">+ 8,326</td>
                        </tr>
                        <tr>
                          <td>HGM</td>
                          <td>$ 15,855</td>
                          <td class="green-600">+ 11,326</td>
                        </tr>
                        <tr>
                          <td>MKR</td>
                          <td>$ 18,500</td>
                          <td class="red-600">- 6,586</td>
                        </tr>
                        <tr>
                          <td>GMY</td>
                          <td>$ 9,500</td>
                          <td class="green-600">+ 458</td>
                        </tr>
                        <tr>
                          <td>KPM</td>
                          <td>$ 15,425</td>
                          <td class="red-600">- 1,632</td>
                        </tr>
                        <tr>
                          <td>PTR</td>
                          <td>$ 11,540</td>
                          <td class="green-600">+ 8,326</td>
                        </tr>
                        <tr>
                          <td>HGM</td>
                          <td>$ 15,855</td>
                          <td class="green-600">+ 11,326</td>
                        </tr>
                        <tr>
                          <td>MKR</td>
                          <td>$ 18,500</td>
                          <td class="red-600">- 6,586</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <!-- End Panel Watchlist -->
          </div>

          <div class="col-xxl-3 col-lg-6">
            <!-- Widget Linepoint -->
            <div class="card card-shadow" id="widgetLinepointDate">
              <div class="card-block p-30">
                <h3 class="card-title">Sales Analysis
                  <div class="card-header-actions">
                    <span class="badge badge-dark badge-round">View</span>
                  </div>
                </h3>
                <div class="row text-center my-25">
                  <div class="col-4">
                    <div class="counter">
                      <div class="counter-label">TOTAL</div>
                      <div class="counter-number red-600">20,186</div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="counter">
                      <div class="counter-label">TODAY</div>
                      <div class="counter-number red-600">36</div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="counter">
                      <div class="counter-label">WEEK</div>
                      <div class="counter-number red-600">261</div>
                    </div>
                  </div>
                </div>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer
                  nec odio. Praesent libero.</p>
              </div>
              <div class="ct-chart h-150"></div>
            </div>
            <!-- End Widget Linepoint -->
          </div>
        </div>
		*/?>
      </div>
    </div>
    <!-- End Page -->


<?php include("footer.php");?>
