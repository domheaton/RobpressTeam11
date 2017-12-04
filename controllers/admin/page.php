<?php

namespace Admin;

class Page extends AdminController {

	public function index($f3) {
		$pages = $this->Model->Pages->fetchAll();
		$f3->set('pages',$pages);
	}

	public function add($f3) {
		if($this->request->is('post')) {
			$pagename = strtolower(str_replace(" ","_",$this->request->data['title']));
			if($pagename != "") {
				$this->Model->Pages->create($pagename);

				\StatusMessage::add('Page created succesfully','success');
				return $f3->reroute('/admin/page/edit/' . $pagename);
			}
			else {
				\StatusMessage::add('Please enter a Title for the new page','danger');
				return $f3->reroute('/admin/page');
			}
		}
	}

	public function edit($f3) {
		// $pagename = $f3->get('PARAMS.3');

		// XSS VULNERABILITY
		$pagename = $f3->clean($f3->get('PARAMS.3'));

		if ($this->request->is('post')) {
			$pages = $this->Model->Pages;
			$pages->title = $pagename;
			$pages->content = $this->request->data['content'];
			$pages->save();

			\StatusMessage::add('Page updated succesfully','success');
			return $f3->reroute('/admin/page');
		}

		$pagetitle = ucfirst(str_replace("_"," ",str_ireplace(".html","",$pagename)));
		$page = $this->Model->Pages->fetch($pagename);
		$f3->set('pagetitle',$pagetitle);
		$f3->set('page',$page);
	}

	public function delete($f3) {
		$pagename = $f3->get('PARAMS.3');
		$this->Model->Pages->delete($pagename);
		\StatusMessage::add('Page deleted succesfully','success');
		return $f3->reroute('/admin/page');
	}

}

?>
