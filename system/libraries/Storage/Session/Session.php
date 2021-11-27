<?php

namespace Ceramic\Storage\Session;

class Session extends CMSessionDriver {

	public function __construct() {
		parent::__construct();
	}

	public function set(string $key, ?string $value = null): bool {
		return $this->write($key, $value);
	}

	public function get(?string $key = null): array|string {
		return (!empty($key)) ? $this->read($key) : $this->getCMStorage();
	}

}