<?php
function check_user($username) 
{
	global $db;
	$query = $db->query('SELECT * FROM user WHERE username = ?', $username)->row();

	return $query;		
}

function check_login() 
{
	global $db;
	
	$error = false;
	$user = check_user($_POST['username']);
	
	if ($user) {
		if (!password_verify($_POST['password'],$user['password'])) {
			$error = 'Username dan password tidak cocok';
		} else {
			if ($user['verified'] == 0) {
				$error = 'Akun Anda belum aktif';
			}
		}
	} else {
		$error = 'Username dan password tidak cocok';
	}
	
	if ($error) {
		return $error;
	} else {
		delete_auth_cookie($user['id_user']);
		
		if (!empty($_POST['remember']))
		{
			global $app_auth;
			$token = $app_auth->generateDbToken();
			$expired_time = time() + (7*24*3600); // 7 h
			setcookie('remember', $token['selector'] . ':' . $token['external'], $expired_time, '/');
			
			$data = array ( 'id_user' => $user['id_user']
							, 'selector' => $token['selector']
							, 'token' => $token['db']
							, 'action' => 'remember'
							, 'created' => date('Y-m-d H:i:s')
							, 'expires' => date('Y-m-d H:i:s', $expired_time)
						);

			$db->insert('user_token', $data);
		}
		
		$_SESSION ['user'] = set_user($user['id_user']);
		$_SESSION['logged_in'] = true;
		
		header('location:./');
	}
}

function set_user($id) {
	global $db;
	
	$user_detail = $db->query('SELECT * FROM user 
								WHERE id_user = ' . $id
							)->getRowArray();
	
	$query = $db->query('SELECT * FROM user_role LEFT JOIN role USING(id_role) WHERE id_user = ?', [$user_detail['id_user']]);
	$result = $query->getResultArray();
	foreach ($result as $val) {
		$user_detail['role'][$val['id_role']] = $val;
	}
	
	$query = $db->query('SELECT * FROM module WHERE id_module = ?', $user_detail['id_module']);
	$user_detail['default_module'] = $query->getRowArray();
	
	return $user_detail;
}

function get_user() 
{
	global $db;
	$sql = 'SELECT * FROM user';
	$result = $db->query($sql)->result();
	return $result;
}

function check_cookie($selector) 
{
	if (!empty($_COOKIE['remember'])) 
	{
		global $db;
		list($selector, $cookie_token) = explode(':', $_COOKIE['remember']);
		$sql = 'SELECT * FROM user_token WHERE selector = ?';
		$data = $db->query($sql, $selector);
		
		if ($app_auth->verifyToken($cookie_token, $data['token'])) {
		
			if ($data['expires'] > date('Y-m-d H:i:s')) 
			{
				$user_detail = $db->query('SELECT * FROM user 
										WHERE id_user = ?', $data['id_user']
									)->row();

				$_SESSION ['user'] = $user_detail;
				$_SESSION['logged_in'] = true;
			}
		}
	}
}

function delete_auth_cookie($id_user) 
{
	global $db;
	$db->delete('user_token', ['action' => 'remember', 'id_user' => $id_user]);
	setcookie('remember', '', time() - 360000, '/');	
}