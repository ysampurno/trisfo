<?php
/**
*	Developed by: Agus Prawoto Hadi
*	Website		: https://jagowebdev.com
*	Year		: 2021-2022
*/

function generate_token($n) {
	// PHP 7
	if (function_exists('random_bytes')) {
		return bin2hex(random_bytes($n));
	
	// Fallback to PHP 5
	} else {
		require_once BASEPATH . "system/libraries/vendors/paragonie/random-compat/lib/random.php";
		try {
			$string = random_bytes($n);
		} catch (TypeError $e) {
			// Well, it's an integer, so this IS unexpected.
			// die("An unexpected error has occurred"); 
			$string = null;
		} catch (Error $e) {
			// This is also unexpected because 32 is a reasonable integer.
			// die("An unexpected error has occurred");
			$string = null;
		} catch (Exception $e) {
			// If you get this message, the CSPRNG failed hard.
			// die("Could not generate a random string. Is our OS secure?");
			$string = null;
		}
		return bin2hex($string);
	}
}

function csrf_settoken() 
{
	global $csrf_token;
	setcookie($csrf_token['name'], '', time() - 360000, '/');
	$token = generate_token(32);
	setcookie($csrf_token['name'], $token, time() + $csrf_token['expire'], '/');
	$csrf_token['token'] = $token;
}

function csrf_gettoken() 
{
	global $csrf_token;
	return $csrf_token['token'];
}

function csrf_field() 
{
	global $csrf_token;
	if (!empty($csrf_token['token'])) {
		$field = '<input type="hidden" name="' . $csrf_token['name'] . '" value="' . $csrf_token['token'] . '"/>';
	} else {
		$field = '<!-- CSRF Token disabled -->';
	}
	echo $field;
}

function csrf_verify() 
{
	global $csrf_token;
	
	$csrf_token['validation']['status'] = 'ok';
			
	if (!empty($_POST))
	{
		$error = false;
		
		if (empty($_COOKIE[$csrf_token['name']]) || empty($_POST[$csrf_token['name']])) {
			$csrf_token['validation']['status'] = 'error';
			$csrf_token['validation']['error_type'] = 'token_notfound';
			$csrf_token['validation']['message'] = 'Token tidak ditemukan';
			$error = true;
		}

		if ( !empty($_POST[ $csrf_token['name'] ]) && !empty($_COOKIE[ $csrf_token['name']] ) ) {
			if ($_COOKIE[$csrf_token['name']] != @$_POST[$csrf_token['name']]) {
			
				$csrf_token['validation']['status'] = 'error';
				$csrf_token['validation']['error_type'] = 'token_missmatch';
				$csrf_token['validation']['message'] = 'Token tidak sesuai';
				$error = true;
			}
		}
		
		if ($error && $csrf_token['exit_error'] == true) {
			exit_error($csrf_token['validation']['message']);
		}
	}
}

function csrf_validation() 
{
	global $csrf_token;
	$csrf_token['exit_error'] = false;
	csrf_verify();
	return $csrf_token['validation'];
}
?>