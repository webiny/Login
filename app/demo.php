<?php
require_once './bootstrap.php';

// check if we have the auth cookie and device cookie
$authCookie = \Webiny\Component\Http\Cookie::getInstance()->get('auth-token');
$deviceToken = \Webiny\Component\Http\Cookie::getInstance()->get('device-token');

$msg = '';
if ($authCookie) {
    try {
        $user = $login->getUser($authCookie, $deviceToken);
        if($user){
            die('You are logged in');
        }
    } catch (\Webiny\Login\LoginException $le) {
        $msg = $le->getMessage();
        die(print_r($le));
    } catch (\Exception $e) {
        die(print_r($e));
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login page</title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <style type="text/css">
        body {
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #eee;
        }

        .form-signin {
            max-width: 330px;
            padding: 15px;
            margin: 0 auto;
        }

        .form-signin .form-signin-heading,
        .form-signin .checkbox {
            margin-bottom: 10px;
        }

        .form-signin .checkbox {
            font-weight: normal;
        }

        .form-signin .form-control {
            position: relative;
            height: auto;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            padding: 10px;
            font-size: 16px;
        }

        .form-signin .form-control:focus {
            z-index: 2;
        }

        .form-signin input[type="email"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }

        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    </style>

</head>
<body>


<div class="container">

    <form class="form-signin">
        <h2 class="form-signin-heading">Please sign in</h2>

        <label for="inputEmail" class="sr-only">Email address</label>
        <input type="email" id="username" value="admin" class="form-control" placeholder="Email address" required
               autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="password" value="login123" class="form-control" placeholder="Password" required>

        <div class="checkbox">
            <label>
                <input type="checkbox" value="remember-me" id="remember-me"> Remember me
            </label>
        </div>
        <input type="hidden" name="device-token" id="device-token" value="<?php echo $deviceToken; ?>"/>
        <button class="btn btn-lg btn-primary btn-block" type="button" onclick="submitLogin();">Sign in</button>
        <br/>

        <div class="alert alert-danger" role="alert" id="msg" style="display: none;"></div>
        <div class="alert alert-info" role="alert" id="info" style="display: none;"></div>
    </form>

</div>


<script type="text/javascript">

    domain = 'http://api.test.app:'+location.port;

    function submitLogin() {

        $('#msg').hide();
        $('#info').hide();

        $.ajax({
            type: 'POST',
            url: domain + '/login/process-login',
            data: {
                'username': $('#username').val(),
                'password': $('#password').val(),
                'rememberMe': $('#remember-me').val(),
                'deviceToken': $('#device-token').val()
            },
            success: function (result) {
                $('#msg').html('Login successful, refresh the page.');
                $('#msg').fadeIn();

                var token = result.data.authToken;

                // save auth token for later
                document.cookie = encodeURIComponent('auth-token') + "=" + encodeURIComponent(token) + "; path=/";

            },
            error: function (result) {
                var error = result.responseJSON.errorReport;
                // display the error
                $('#msg').html('<strong>' + error.message + '</strong>: ' + error.description);
                $('#msg').fadeIn();

                // special error cases
                switch (error.code) {
                    case 4: // account not confirmed
                        setTimeout(function () {
                            $('#info').html('Requesting account activation code.');
                            $('#info').fadeIn();

                            $.post(domain + '/login/get-account-activation-token', {'username': $('#username').val()}, function (result) {
                                setTimeout(function () {
                                    var msg = 'Your activation code is: <strong>' + result.data.accountActivationToken + '</strong>';
                                    msg += '<br/><a href="javascript://" onclick="activateAccount(\'' + result.data.accountActivationToken + '\')">Click here to activate your account</a>';
                                    $('#info').html(msg);
                                }, 500);
                            });

                        }, 500);

                        break;

                    case 5: // device not allowed

                        setTimeout(function () {
                            $('#info').html('Requesting device activation code.');
                            $('#info').fadeIn();

                            $.post(domain + '/login/get-device-validation-token', {'username': $('#username').val()}, function (result) {
                                setTimeout(function () {
                                    var msg = 'Your activation code is: <strong>' + result.data.deviceValidationToken + '</strong>';
                                    msg += '<br/><a href="javascript://" onclick="validateDevice(\'' + result.data.deviceValidationToken + '\')">Click here to validate this device</a>';
                                    $('#info').html(msg);
                                }, 500);
                            });

                        }, 500);

                        break;
                }
            }
        });

        return false;
    }

    function activateAccount(token) {
        $.post(domain + '/login/validate-account-activation-token', {
            'username': $('#username').val(),
            'accountActivationToken': token
        }, function (result) {
            if (typeof result.data != "undefined") {
                if (result.data.result == "success") {
                    $('#info').html('Account successfully activated, you can now login.');

                    return;
                }
            }

            $('#info').html('Invalid account activation token');
        });
    }

    function validateDevice(token) {
        $.post(domain + '/login/validate-device-validation-token', {
            'username': $('#username').val(),
            'deviceValidationToken': token
        }, function (result) {
            if (typeof result.data != "undefined") {
                $('#info').html('Device successfully activated.');

                var token = result.data.deviceToken;

                // save device token for later
                document.cookie = encodeURIComponent('device-token') + "=" + encodeURIComponent(token) + "; path=/";

                // resubmit login
                $('#device-token').val(token);
                submitLogin();

                return;
            }

            $('#info').html('Invalid account activation token');
        });
    }
</script>

</body>
</html>