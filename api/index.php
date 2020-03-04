<?php
 include(dirname(__FILE__).'/configurations/config.php');
 //checklogin();
 //include("header.php");?>
<head>
 <meta name="google-signin-client_id" content="437775881518-65u2sbp3r824qjsmpu03b43dok3tptm8.apps.googleusercontent.com">

<script src="https://www.gstatic.com/firebasejs/5.7.0/firebase.js"></script>
<script src="https://apis.google.com/js/platform.js" async defer></script>
</head>

<body>

    <div id="wrapper">

       <?php include("menu.php");?>

        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Welcome</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
                  
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

<?php include("footer.php");?>
