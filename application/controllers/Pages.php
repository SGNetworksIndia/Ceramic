<?php
defined('CORE_PATH') OR exit('No direct script access allowed');
class Pages extends Controller {

	public function view($page = 'home'){
		if (!file_exists(APP_PATH.'views/demo/'.$page.'.php')){
			// Whoops, we don't have a page for that!
			show_404();
		}

		$data['title'] = ucfirst($page); // Capitalize the first letter

		$this->load->view('templates/header', $data);
		$this->load->view('demo/'.$page, $data);
		$this->load->view('templates/footer', $data);
	}
}
/* URL: /Pages/view/home */