<?php

namespace Ceramic\Storage\Session;

interface CMSessionHandler {
	public function get(?string $key = null): mixed;

	public function set(string $key, ?string $value = null): bool;

	public function unset(string $key);
}