<?php
defined('CORE_PATH') OR exit('No direct script access allowed');

class Welcome extends Controller {
	private $copyright = "Ceramic";
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://ceramic.sgn.heliohost.org/user_guide/general/urls.html
	 */
	/*public function __construct() {
		$this->load->view('welcome_message');
	}*/

	public function refresh(){
		$captcha = create_captcha();

		// Unset previous captcha and set new captcha word
		$this->session->unset_userdata('captchaCode');
		$this->session->set_userdata('captchaCode',$captcha['word']);

		// Display captcha image
		echo $captcha['image'];
	}

	public function __default()
	{
		$template = $this->load->getTemplate();
		$template->set("copyright", "Copyright &copy; {$this->copyright} 2020. All rights reserved.");
		$this->load->setTemplate($template);
		$this->load->view('welcome_message');
	}
	public function index()
	{
		$template = $this->load->getTemplate();
		$template->set("copyright", "Copyright &copy; {$this->copyright} 2020. All rights reserved.");
		$this->load->setTemplate($template);
		$this->load->view('welcome_message');
	}
}
