<?php
class User extends Controller {

	public function view($f3) {
		$userid = $f3->get('PARAMS.3');

		// SQL VULNERABILITY
		// Prevents search of database for a user with a non-numeric parameter
		// Redirects to 'page not found' error if user profile not found
		if(!is_numeric($userid)) {
			return $f3->reroute('/404.htm');
		}
		else {
			$u = $this->Model->Users->fetch($userid);

			$articles = $this->Model->Posts->fetchAll(array('user_id' => $userid));
			$comments = $this->Model->Comments->fetchAll(array('user_id' => $userid));

			$f3->set('u',$u);
			$f3->set('articles',$articles);
			$f3->set('comments',$comments);
		}

	}

	public function add($f3) {
		if($this->request->is('post')) {
			extract($this->request->data);

			$check = $this->Model->Users->fetch(array('username' => $username));

			if (!empty($check)) {
				StatusMessage::add('User already exists','danger');
			} else if ($username == "") {
				// check username not blank
				StatusMessage::add('Username cannot be blank','danger');
			} else if($password != $password2) {
				StatusMessage::add('Passwords must match','danger');
			} else if($password == "") {
				//check password not blank
				StatusMessage::add('Password cannot be blank','danger');
			} else if($email == "") {
				StatusMessage::add('Email cannot be blank', 'danger');
		  } else {

				//Check for debug mode
				$settings = $this->Model->Settings;
				$debug = $settings->getSetting('debug');

				if ($debug == true) {
					$user = $this->Model->Users;
					$user->copyfrom('POST');
					$user->created = mydate();
					$user->bio = '';
					$user->level = 1;

					//XSS VULERNERABILITY
					//Remove tags (<></>) from username, displayname and password
					$user->username = $f3->clean($username);
					$user->displayname = $f3->clean($displayname);
					$user->setPassword($f3->clean($password));
					// $user->setPassword($password);

					if(empty($displayname)) {
						$user->displayname = $user->username;
					}

					//Set the users password
					$user->setPassword($user->password);

					$user->save();
					StatusMessage::add('Registration complete','success');
					return $f3->reroute('/user/login');
				}
				else {

					// BRUTE FORCE VULNERATBILITY
					// Verify Google reCAPTCHA Here
					$curl = curl_init();

					curl_setopt_array($curl, [
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
						CURLOPT_POST => 1,
						CURLOPT_POSTFIELDS => [
							'secret' => '6LfewToUAAAAAB7fzVxy81bhIeDB5cWSsL2ZT6ba',
							'response' => $_POST['g-recaptcha-response'],
						],
					]);

					$response = json_decode(curl_exec($curl));

					if(!$response->success) {
						// if unsuccessful display error
						StatusMessage::add('Please complete the reCAPTCHA', 'danger');
						return $f3->reroute('/user/add');
					} else {
						// continue as normal if recaptcha attempt successful
						$user = $this->Model->Users;
						$user->copyfrom('POST');
						$user->created = mydate();
						$user->bio = '';
						$user->level = 1;

						//XSS VULERNERABILITY
						//Remove tags (<></>) from username, displayname and password
						$user->username = $f3->clean($username);
						$user->displayname = $f3->clean($displayname);
						$user->setPassword($f3->clean($password));
						// $user->setPassword($password);

						if(empty($displayname)) {
							$user->displayname = $user->username;
						}

						//Set the users password
						$user->setPassword($user->password);

						$user->save();
						StatusMessage::add('Registration complete','success');
						return $f3->reroute('/user/login');
					}
				}
			}
		}
	}

	public function login($f3) {
		/** YOU MAY NOT CHANGE THIS FUNCTION - Make any changes in Auth->checkLogin, Auth->login and afterLogin() */
		if ($this->request->is('post')) {

			//Check for debug mode
			$settings = $this->Model->Settings;
			$debug = $settings->getSetting('debug');

			//Either allow log in with checked and approved login, or debug mode login
			list($username,$password) = array($this->request->data['username'],$this->request->data['password']);
			if (
				($this->Auth->checkLogin($username,$password,$this->request,$debug) && ($this->Auth->login($username,$password))) ||
				($debug && $this->Auth->debugLogin($username))) {

					$this->afterLogin($f3);

			} else {
				StatusMessage::add('Invalid username or password','danger');
			}
	 }
	}

	/* Handle after logging in */
	private function afterLogin($f3) {
				StatusMessage::add('Logged in succesfully','success');

				//Redirect to where they came from
				if(isset($_GET['from'])) {
					$f3->reroute($_GET['from']);
				} else {
					$f3->reroute('/');
				}
	}

	public function logout($f3) {
		$this->Auth->logout();
		StatusMessage::add('Logged out succesfully','success');
		$f3->reroute('/');
	}


	public function profile($f3) {
		$id = $this->Auth->user('id');
		extract($this->request->data);
		$u = $this->Model->Users->fetch($id);
		$oldpass = $u->password;
		if($this->request->is('post')) {
			$u->copyfrom('POST');
			if(empty($u->password)) { $u->password = $oldpass; }

			//Handle avatar upload
			if(isset($_FILES['avatar']) && isset($_FILES['avatar']['tmp_name']) && !empty($_FILES['avatar']['tmp_name'])) {
				$url = File::Upload($_FILES['avatar']);
				$u->avatar = $url;
			} else if(isset($reset)) {
				$u->avatar = '';
			}

			$u->save();
			\StatusMessage::add('Profile updated succesfully','success');
			return $f3->reroute('/user/profile');
		}
		$_POST = $u->cast();
		$f3->set('u',$u);
	}


	// AUTHORISATION BYPASS VULNERABILITY
	// public function promote($f3) {
	// 	if ($this->Auth->user('level') < 2) {
	// 		StatusMessage::add('Access Denied','danger');
	// 		return $f3->reroute('/');
	// 	}
	// 	else {
	// 		$id = $this->Auth->user('id');
	// 		$u = $this->Model->Users->fetch($id);
	// 		$u->level = 2;
	// 		$u->save();
	// 		return $f3->reroute('/');
	// 	}
	// }

}
?>
