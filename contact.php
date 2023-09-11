<?php include("includes/db_connection.php");?>
<!DOCTYPE html>
<html lang="en">
<?php include("includes/head.html") ?>
<body>
<?php include("includes/header.html") ?>
<div class="container-fluid text-center">
    <?php include("includes/leftnav.html") ?>
    <div class="col-sm-8 text-left">
        <div class="form-container">
            <h4>For online contact, you can send us an email</h4>
            <?php if (!isset($_POST["submit"])) { ?>
                <form name="frmContact" id="frmContact" method="post" action="<?php echo $_SERVER["PHP_SELF"];?>" enctype="multipart/form-data" onsubmit="return validateContactForm()">
                    <div class="input-row">
                        <label style="padding-top: 20px;">Name</label> <span id="userName-info" class="info"></span><br /> <input typpe="text" class="input-field" name="userName" id="userName" />
                    </div>
                    <div class="input-row">
                        <label>Email</label> <span id="userEmail-info" class="info"></span><br /> <input type="text" class="input-field" name="from_email" id="userEmail" />
                    </div>
                    <div class="input-row">
                        <label>Subject</label> <span id="subject-info" class="info"></span><br /> <input type="text" class="input-field" name="subject" id="subject" />
                    </div>
                    <div class="input-row">
                        <label>Message</label> <span id="userMessage-info" class="info"></span><br /> <textarea name="content" id="content" class="input-field" cols="60" rows="6"></textarea>
                    </div>
                    <div>
                        <input type="submit" name="submit" class="btn-submit" value="Send" />
                        <div id="statusMessage">
                            <?php
                            if (!empty($message))
                            {
                                echo "<p class='<?php echo($type); ?>Message'><?php echo($message); ?></p>";
                            }
                            ?>
                        </div>
                    </div>
                </form>
                <h4>Also you can call us : <b>+389 71 919 333</b></h4>
                <?php
            } else {
                $to_email = "ms26214@seeu.edu.mk";
                $from_email = $_POST["from_email"];
                $subject = $_POST["subject"];
                $body = $_POST["content"];

                if (mail($to_email, $subject, $body)) {
                    echo("Email successfully sent!");
                }
                else {
                    echo("Email sending failed!");
                }
            }
            ?>
            <div>

            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript"></script>
    <script type="text/javascript">
        function validateContactForm() {
            var valid = true;

            $(".info").html("");
            $(".input-field").css('border', '#e0dfdf 1px solid');
            var userName = $("#userName").val();
            var userEmail = $("#userEmail").val();
            var subject = $("#subject").val();
            var content = $("#content").val();

            if (userName == "") {
                $("#userName-info").html("Required.");
                $("#userName").css('border', '#e66262 1px solid');
                valid = false;
            }
            if (userEmail == "") {
                $("#userEmail-info").html("Required.");
                $("#userEmail").css('border', '#e66262 1px solid');
                valid = false;
            }
            if (!userEmail.match(/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/))
            {
                $("#userEmail-info").html("Invalid Email Address.");
                $("#userEmail").css('border', '#e66262 1px solid');
                valid = false;
            }

            if (subject == "") {
                $("#subject-info").html("Required.");
                $("#subject").css('border', '#e66262 1px solid');
                valid = false;
            }
            if (content == "") {
                $("#userMessage-info").html("Required.");
                $("#content").css('border', '#e66262 1px solid');
                valid = false;
            }
            return valid;
        }
    </script>
    <?php include("includes/rightnav.html") ?>
</div>
<?php include("includes/footer.html") ?>
</body>
</html>
