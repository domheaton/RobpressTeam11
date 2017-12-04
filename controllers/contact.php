<?php

class Contact extends Controller {

	public function index($f3) {
		if($this->request->is('post')) {
			extract($this->request->data);
			$from = "From: $from";

			//Check for debug mode
			$settings = $this->Model->Settings;
			$debug = $settings->getSetting('debug');

			if ($debug == true) {
				mail($to,$subject,$message,$from);

				StatusMessage::add('Thank you for contacting us');
				return $f3->reroute('/');
			}
			else {
				// CSRF VULNERABILITY
				// check hidden form value with session_id
				$f3->set('csrf',$csrf);
				if ($csrf != session_id()) {
					return $f3->reroute('/403.htm');
				}
				else {
					mail($to,$subject,$message,$from);

					StatusMessage::add('Thank you for contacting us');
					return $f3->reroute('/');
				}

				// mail($to,$subject,$message,$from);
	      //
				// StatusMessage::add('Thank you for contacting us');
				// return $f3->reroute('/');
			}
		}
	}

}

?>
