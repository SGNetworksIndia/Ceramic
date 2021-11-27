<?php
use JetBrains\PhpStorm\NoReturn;

defined('CORE_PATH') or exit('No direct script access allowed');

/**
 * @property \Autoindex $autoindex
 * @property \Input $input
 * @property \Session $session
 */
class Demo extends Controller {
	private string $copyright = "Ceramic";

	public function __common() {
		$this->forceHTTPS(true);
	}

	#[NoReturn] public function __default() {
		$cmtemplate = $this->load->getTemplate();
		$cmtemplate->set("cmproduct", "Ceramic");
		$cmtemplate->set("cmfeature", "AutoIndex");
		$cmtemplate->set("copyright", "Copyright &copy; {$this->copyright} 2020. All rights reserved.");
		$this->load->setTemplate($cmtemplate);
		$this->load->loadOnContext = true;
		$this->load->library('autoindex');
		$this->load->view('demo/header');
		$this->autoindex->render();
		$this->load->view('demo/footer');
	}

	public function captcha() {
		$cmtemplate = $this->load->getTemplate();
		$cmtemplate->set("cmproduct", "Ceramic");
		$cmtemplate->set("cmfeature", "CAPTCHA Helper");
		$cmtemplate->set("copyright", "Copyright &copy; {$this->copyright} 2020. All rights reserved.");
		$this->load->setTemplate($cmtemplate);

		$this->load->loadOnContext = true;
		$this->load->library('input');
		$this->load->library('form_validation');
		$this->load->helper('captcha');
		$this->load->library('Session/session');

		// If captcha form is submitted
		if($this->input->post('submit')) {
			$inputCaptcha = $this->input->post('captcha');
			$sessCaptcha = $this->session->userdata('captchaCode');
			if($inputCaptcha === $sessCaptcha) {
				$this->user_success('Captcha code matched.');
			} else {
				$this->user_error('Captcha code does not match, please try again.');
			}
		}
		$captcha = create_captcha();
		$data['captcha'] = $captcha;
		// Unset previous captcha and set new captcha word
		$this->session->unset_userdata('captchaCode');
		$this->session->set_userdata('captchaCode', $data['captcha']['word']);
		$this->load->view('demo/header', $data);
		$this->load->view('demo/captcha', $data);
		$this->load->view('demo/footer', $data);
	}

	private function user_success($msg, $heading = 'Success') {
		if($heading) {
			$html = '<h3 style="background-color: #36f146; padding: 10px 15px; margin: 0; border-radius: 10px 10px 0 0; color: #fff;">' . $heading . '</h3>';
			$html .= '<div style="border:1px solid #36f146; border-radius: 0 0 10px 10px; padding: 15px 0 15px 15px;">';
		} else {
			$html = '<div style="border:1px solid #baffba; border-radius: 10px; color: #009900; background-color: #baffba; padding: 15px;">';
		}
		$html .= $msg;
		$html .= '</div>';
		echo $html;
	}

	private function user_error($msg, $heading = 'Error') {
		if($heading) {
			$html = '<h3 style="background-color: #f13646; padding: 10px 15px; margin: 0; border-radius: 10px 10px 0 0; color: #fff;">' . $heading . '</h3>';
			$html .= '<div style="border:1px solid #f13646; border-radius: 0 0 10px 10px; padding: 15px 0 15px 15px;">';
		} else {
			$html = '<div style="border:1px solid #ffbaba; border-radius: 10px; color: #990000; background-color: #ffbaba; padding: 15px;">';
		}
		$html .= $msg;
		$html .= '</div>';
		echo $html;
	}

	public function templating() {
		$cmtemplate = $this->load->getTemplate();
		$cmtemplate->set("cmproduct", "Ceramic");
		$cmtemplate->set("cmfeature", "Template Engine");
		$cmtemplate->set("copyright", "Copyright &copy; {$this->copyright} 2020. All rights reserved.");

		$template = $this->load->getTemplate();
		/*
		 * To use custom HTML tags use the Formatter class:
		 * CONSTANTS:   {{key}} = The key of the variable set using Template::set(String key, String value) method
		 *              {{value}} = The value of the variable set using Template::set(String key, String value) method
		 *
		 * $formatter = $template->getFormatter($template::CM_TEMPLATE_VARIABLES_FORMATTER);
		 * $formatter->format("<u><em><strong>{{value}}</strong></em></u>");
		 * $template->setFormatter($formatter);
		 */
		$template->important($template::CM_TEMPLATE_VARIABLES_FORMATTER);
		$template->emphasize($template::CM_TEMPLATE_VARIABLES_FORMATTER);
		$template->underline($template::CM_TEMPLATE_VARIABLES_FORMATTER);
		$template->set("name", "Annie Jones");
		$template->set("occupation", "Full-stack Web Developer");
		$template->set("product", "Ceramic");
		$template->set("feature", "Template Engine");

		$this->load->addTemplate($cmtemplate);
		$this->load->addTemplate($template);
		$this->load->view('demo/header');
		$this->load->view('demo/templating');
		$this->load->view('demo/footer');
	}

	private function refresh() {
		$captcha = create_captcha();

		// Unset previous captcha and set new captcha word
		$this->session->unset_userdata('captchaCode');
		$this->session->set_userdata('captchaCode', $captcha['word']);

		// Display captcha image
		echo $captcha['image'];
	}
}