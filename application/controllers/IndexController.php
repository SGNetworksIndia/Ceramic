<?php

defined('CORE_PATH') OR exit('No direct script access allowed');
// application/controllers/admin/IndexController.class.php

class IndexController extends Controller{

	public function mainAction(){

		include CURR_VIEW_PATH . "main.html";

		// Load Captcha class

		$this->load->library("Captcha");

		$captcha = new Captcha;

		$captcha->hello();

		$userModel = new UserModel("user");

		$users = $userModel->getUsers();

	}

	public function indexAction(){

		$userModel = new UserModel("user");

		$users = $userModel->getUsers();

		// Load View template

		include  CURR_VIEW_PATH . "index.html";

	}

	public function menuAction(){

		include CURR_VIEW_PATH . "menu.html";

	}

	public function dragAction(){

		include CURR_VIEW_PATH . "drag.html";

	}

	public function topAction(){

		include CURR_VIEW_PATH . "top.html";

	}

}