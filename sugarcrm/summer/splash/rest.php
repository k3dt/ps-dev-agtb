<?php
session_start();
chdir('../..');
define('sugarEntry', true);
require_once('summer/splash/BoxOfficeClient.php');
require_once 'summer/splash/boxoffice/lib/Slim/Slim.php';
require_once 'summer/splash/boxoffice/lib/EasyRpService.php';
$app = new Slim();
$app->config(array('debug' => true));
error_reporting(E_ALL &~ E_STRICT);
restore_error_handler();
define("GOOGLE_API_KEY", '');
/**
 * function for authenticating users
 */
$app->get('/rest/users/callback', function() use (&$app, &$box)
{
    $app->response()->header('Content-Type', 'text/html;charset=utf-8');
    $api = new EasyRpService(GOOGLE_API_KEY);
    $result = $api->verify("http://localhost:8888/sugar66/summer/splash/rest/users/callback", $_SERVER['QUERY_STRING']);
    // TODO: check if result is OK
    $email = $result['verifiedEmail'];
    $boc = BoxOfficeClient::getInstance();
    session_destroy();
    session_start();
    if($boc->getUser($email, false)) {
        // existing user
        $data = $boc->authenticateUser($email, null, $result['identifier']);
    } else {
        // new user
        $boc->createRemoteUser($email, array("first_name" => $result['firstName'], "last_name" => $result['lastName'],
            "photo" => $result['photoUrl'], "remote_id" => $result['identifier']
            ));
        $data = $boc->authenticateUser($email, null, $result['identifier']);
    }
    if($data['user']['status'] == 'Active') {
        $data = json_encode($data);
    } else {
        $data = json_encode(array("error" => "Your account is not active. Please contact support."));
    }
    echo <<<END
<script language="javascript">
window.opener.login.response($data);
window.close();
</script>
END;
}
);

$app->post('/rest/users/authenticate', function() use (&$app, &$box)
{
    $app->response()->header('Content-Type', 'application/json;charset=utf-8');
    $password = $app->request()->params('password');
    $email = $app->request()->params('email');

    $api = new EasyRpService(GOOGLE_API_KEY);
    $res = $api->getUrl($email, "http://localhost:8888/sugar66/summer/splash/rest/users/callback");
    if($res) {
        echo json_encode(array("popup" => $res['authUri']));
        return;
    }

    $boc = BoxOfficeClient::getInstance();
    session_destroy();
    session_start();
    if ($data = $boc->authenticateUser($email, $password)) {
        switch ($data['user']['status']) {
            case 'Pending Confirmation':
                echo json_encode(array("error" => "Your account needs to be activated. Please check your email for the activation code. <div><a href='#' onclick='login.resendActivation(" . json_encode($email) . ")'>Resend Activation Code</a></div>"));
                break;
            case 'Active':
                echo json_encode($data);
                break;
            default:
                echo json_encode(array("error" => "Your account needs to be activated. Please contact support."));
        }

    } else {
        echo json_encode(array("error" => "Please check your email and password."));
    }
});

/**
 * sends a new authentication email to validate an email address
 */
$app->post('/rest/users/resendActivation', function() use (&$app, &$box)
{

    $app->response()->header('Content-Type', 'application/json;charset=utf-8');
    $boc = BoxOfficeClient::getInstance();
    try {
        $email = $app->request()->params('email');
        if ($boc->resendActivation($email)) {
            echo json_encode(array("info" => "An activation email was sent to " . $email));
        } else {
            echo json_encode(array("info" => "This account is already activated. Please try logging in again"));
        }
    } catch (Exception $e) {
        echo json_encode(array("error" => "Please register for an account"));
    }
});

/**
 * Selects a given instance and bootstraps it into the session
 */
$app->get('/rest/instances/:id', function($id) use (&$app, &$box)
{
    $app->response()->header('Content-Type', 'application/json;charset=utf-8');
    $boc = BoxOfficeClient::getInstance();
    try {
        $boc->selectInstance($id);
        $boc->bootstrapInstance();
        $config = $boc->getConfig();
        echo json_encode(array('url' => $config['site_url'] . 'summer/index.php'));
    } catch (Exception $e) {

        echo json_encode(array("error" => "An error occurred please try again." . $e->getMessage()));
    }
});

/**
 * Registers or updates a given user
 */
$app->post('/rest/users', function() use (&$app, &$box)
{
    $app->response()->header('Content-Type', 'application/json;charset=utf-8');
    $boc = BoxOfficeClient::getInstance();
    $password = $app->request()->params('password');
    $password2 = $app->request()->params('password2');
    $email = $app->request()->params('email');
    $first_name = $app->request()->params('first_name');
    $last_name = $app->request()->params('last_name');
    $company = $app->request()->params('company');
    $last_name = $app->request()->params('last_name');
    if (empty($first_name) || empty($last_name) || empty($password) || empty($password2) || empty($email)) {
        $error = 'You are missing the following required field(s): ';
        $missingFields = array();
        if (empty($first_name)) $missingFields[] = 'First Name';
        if (empty($last_name)) $missingFields[] = 'Last Name';
        if (empty($password) || empty($password2)) $missingFields[] = 'Password';
        if (empty($email)) $missingFields[] = 'Email Address';
        $error .= implode($missingFields, ', ');
        echo json_encode(array("error" => $error));
    } else if ($password != $password2) {
        echo json_encode(array("error" => "Password fields do not match"));
    }
    elseif (strlen($password) < 8) {
        echo json_encode(array("error" => "Your password must be at least 8 characters long"));
    }
    elseif ($boc->getUser($email, false)) {
        echo json_encode(array("error" => "This email address already exists. Please try resetting your password"));
    } else {
        $boc->registerUser($email, $password, array("first_name" => $first_name, "last_name" => $last_name, "company" => $company));
        echo json_encode(array("success" => "You are almost ready! You just need to activate your account! A welcome message with an activation code was just sent to " . $email));
    }

});

/**
 *
 */
$app->post('/rest/users/resetpassword', function() use (&$app, &$box){
    $app->response()->header('Content-Type', 'application/json;charset=utf-8');
    $boc = BoxOfficeClient::getInstance();
    $email = $app->request()->params('email');
    try{
        $boc->requestPasswordReset($email);
        echo json_encode(array("info" => "A reset password email was sent to " . $email));

    }catch(Exception $e){
        echo json_encode(array("error" => "Please register for an account"));
    }

});
$app->run();

