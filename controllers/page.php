<?php

class Page extends Controller {

	function display($f3) {
		$pagename = urldecode($f3->get('PARAMS.3'));
		$page = $this->Model->Pages->fetch($pagename);
		$pagetitle = ucfirst(str_replace("_"," ",str_replace(".html","",$pagename)));

		// XSS VULNERABILITY
		// Prevents the creation of a page where  cross-site scripting can be used within
		// Now the temporary page is just plain text rather than harmful
		// $f3->set('pagetitle'$pagetitle);
		// $f3->set('page',$page);
		$f3->set('pagetitle',$f3->clean($pagetitle));
		$f3->set('page',$f3->clean($page));
	}

}

?>
