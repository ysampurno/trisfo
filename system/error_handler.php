<?php
/**
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2022
*/

// SYSTEM ERROR
// error handler function
function errorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        return false;
    }

    switch ($errno) {
    case E_USER_ERROR:
        echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
        echo "Aborting...<br />\n";
        exit(1);
        break;
	
	 case E_ERROR:
        echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
        echo "Aborting...<br />\n";
        exit(1);
        break;

    case E_USER_WARNING:
		$title = 'WARNING';
		$err_type = 'warning';
        break;
	
	case E_WARNING:
		$title = 'WARNING';
		$err_type = 'warning';
        break;

    case E_USER_NOTICE:
		$title = 'NOTICE';
		$err_type = 'warning';
        break;
	
	case E_NOTICE:
		$title = 'NOTICE';
		$err_type = 'warning';
        break;
		
    default:
        echo "Unknown error type: [$errno] $errstr<br /> $errfile $errline\n";
        break;
    }
	
	$content = "<p><strong>Message</strong> : [$errno] $errstr</p>
				<p><strong>File</strong> : $errfile line: $errline</p>";
	
	
	if (ENVIRONMENT == 'production') {
		include 'views/error_production.php';
	} else {
		include 'views/error.php';
	}
	
    return true;
}

set_error_handler("errorHandler");


function fatalError()
{
    $error = error_get_last();
	// echo '<pre>'; print_r($error); die;
	if (!$error)
		return;

	// http://php.net/manual/en/errorfunc.constants.php
    if ( $error["type"] == E_PARSE || $error["type"] == E_ERROR || $error["type"] == E_COMPILE_ERROR) {
		$content = '<p><strong>Message</strong> : ' . $error['message'] . '</p>
					<p><strong>File</strong> : '.$error['file'].' line: '.$error['line'].'</p>';
					
		if (ENVIRONMENT == 'production') {
			include 'views/error_production.php';
		} else {
			include 'views/error.php';
		}
	}
	return false;
}

register_shutdown_function( "fatalError" );


function appErrorHandler($content) {
	$err_type = 'danger';
	$title = 'Error';
	if (ENVIRONMENT == 'production') {
		include 'views/error_production.php';
	} else {
		include 'views/error.php';
	}
}