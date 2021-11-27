<?php

namespace Ceramic\Storage\Session;

use Ceramic\Storage\CMStorage;
use function array_key_exists;
use function md5;
use function time;

abstract class CMSessionDriver extends CMStorage implements CMSessionHandler {
	private string $storageName = 'cm-session';

	public function __construct() {
		$this->open($this->storageName);
	}

	public function open(string $name, ?string $path = null): bool {
		parent::open($name, $path);

		$data = (array_key_exists($name, $_SESSION)) ? $_SESSION[$name] : ['cm-session-hash' => md5(time())];
		$this->setCMStorage($data);
		$this->updateCMStorage();

		return true;
	}

	protected function updateCMStorage() {
		$_SESSION[$this->storageName] = $this->getCMStorage();
	}

	public function unset(string $key) {
		if(array_key_exists($key, $_SESSION)) {
			unset($_SESSION[$key]);
			$this->delete($key);
		}
	}

}