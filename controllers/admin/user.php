<?php

namespace Admin;

class User extends AdminController {

	public function index($f3) {
		$users = $this->Model->Users->fetchAll();

		// XSS VULNERABILITY
		// $f3->set('users',$users);
		$f3->set('users',$f3->clean($users));
	}

	public function edit($f3) {
		$id = $f3->get('PARAMS.3');

		// SQL VULNERABILITY
		// Prevents search of database for a user with a non-numeric parameter
		// Redirects to 'page not found' error if user profile not found
		if(!is_numeric($id)) {
			return $f3->reroute('/404.htm');
		} else {
			$u = $this->Model->Users->fetch($id);
			if($this->request->is('post')) {
				$u->copyfrom('POST');
				$u->setPassword($this->request->data['password']);
				$u->save();
				\StatusMessage::add('User updated succesfully','success');
				return $f3->reroute('/admin/user');
			}
			$_POST = $u->cast();
			$f3->set('u',$u);
		}
	}

	public function delete($f3) {
		$id = $f3->get('PARAMS.3');

		// SQL VULNERABILITY (also XSS)
		// Prevents search of database for a user with a non-numeric parameter
		// Redirects to 'page not found' error if user profile not found
		if(!is_numeric($id)) {
			return $f3->reroute('/404.htm');
		}

		$u = $this->Model->Users->fetch($id);

		// INFORMATION EXPOSURE VULNERABILITY
		if(empty($u)) {
			return $f3->reroute('/404.htm');
		}

		if($id == $this->Auth->user('id')) {
			\StatusMessage::add('You cannot remove yourself','danger');
			return $f3->reroute('/admin/user');
		}

		//Remove all posts and comments
		$posts = $this->Model->Posts->fetchAll(array('user_id' => $id));
		foreach($posts as $post) {
			$post_categories = $this->Model->Post_Categories->fetchAll(array('post_id' => $post->id));
			foreach($post_categories as $cat) {
				$cat->erase();
			}
			$post->erase();
		}
		$comments = $this->Model->Comments->fetchAll(array('user_id' => $id));
		foreach($comments as $comment) {
			$comment->erase();
		}
		$u->erase();

		\StatusMessage::add('User has been removed','success');
		return $f3->reroute('/admin/user');
	}


}

?>
