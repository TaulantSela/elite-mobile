<?php
declare(strict_types=1);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['userName'] ?? '');
    $fromEmail = filter_var(trim($_POST['from_email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['content'] ?? '');

    if ($name === '' || $fromEmail === false || $subject === '' || $body === '') {
        $message = 'Please complete all required fields with valid information.';
        $messageType = 'error';
    } else {
        $toEmail = 'taulant1995@gmail.com';
        $headers = sprintf('From: %s <%s>', $name, $fromEmail);

        if (mail($toEmail, $subject, $body, $headers)) {
            $message = 'Email successfully sent!';
            $messageType = 'success';
        } else {
            $message = 'Email sending failed. Please try again later.';
            $messageType = 'error';
        }
    }
}

$formAction = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once __DIR__ . '/includes/head.html'; ?>
<body>
<?php require_once __DIR__ . '/includes/header.html'; ?>
<div class="container-fluid text-center">
    <?php require_once __DIR__ . '/includes/leftnav.html'; ?>
    <div class="col-sm-8 text-left">
        <div class="form-container">
            <h4>For online contact, you can send us an email</h4>
            <form name="frmContact" id="frmContact" method="post" action="<?php echo $formAction; ?>" enctype="multipart/form-data" onsubmit="return validateContactForm()">
                <div class="input-row">
                    <label style="padding-top: 20px;">Name</label> <span id="userName-info" class="info"></span><br /> <input type="text" class="input-field" name="userName" id="userName" value="<?php echo htmlspecialchars($_POST['userName'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="input-row">
                    <label>Email</label> <span id="userEmail-info" class="info"></span><br /> <input type="email" class="input-field" name="from_email" id="userEmail" value="<?php echo htmlspecialchars($_POST['from_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="input-row">
                    <label>Subject</label> <span id="subject-info" class="info"></span><br /> <input type="text" class="input-field" name="subject" id="subject" value="<?php echo htmlspecialchars($_POST['subject'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <div class="input-row">
                    <label>Message</label> <span id="userMessage-info" class="info"></span><br /> <textarea name="content" id="content" class="input-field" cols="60" rows="6"><?php echo htmlspecialchars($_POST['content'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div>
                    <input type="submit" name="submit" class="btn-submit" value="Send" />
                    <?php if ($message !== ''): ?>
                        <div id="statusMessage" class="<?php echo $messageType; ?>-message"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                </div>
            </form>
            <h4>Also you can call us : <b>+389 71 919 333</b></h4>
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
    <?php require_once __DIR__ . '/includes/rightnav.html'; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.html'; ?>
</body>
</html>
