<?php

namespace Ceramic\Storage;

use function array_key_exists;
use function array_merge;

abstract class CMStorage implements CMStorageHandler {
	protected array $CMStorageData = [];
	private string $CMStorageName = 'cm-storage';

	public function open(string $name, ?string $path = null): bool {
		$this->CMStorageName = $name;
		$this->CMStorageData[$this->CMStorageName] = [];
		return true;
	}

	public function read(string $key): string {
		$bin = $this->getCMStorage();
		return (array_key_exists($key, $bin)) ? $bin[$key] : "";
	}

	protected function getCMStorage(bool $includeStorageBin = false) {
		return (!$includeStorageBin && array_key_exists($this->CMStorageName, $this->CMStorageData)) ? $this->CMStorageData[$this->CMStorageName] : $this->CMStorageData;
	}

	public function write(string $key, ?string $value = null): bool {
		$this->setCMStorage([$key => $value]);

		return true;
	}

	protected function setCMStorage(array $data, bool $override = false) {
		$storage = $this->getCMStorage(true);
		$bin = $storage[$this->CMStorageName];

		if(empty($bin) || $override)
			$bin = $data;
		else
			$bin = array_merge($bin, $data);

		$this->CMStorageData[$this->CMStorageName] = $bin;
		$this->updateCMStorage();
	}

	protected abstract function updateCMStorage();

	public function delete(string $key): bool {
		$bin = $this->getCMStorage();
		if(array_key_exists($key, $bin)) {
			unset($bin[$key]);
			$this->setCMStorage($bin, true);

			return true;
		}

		return false;
	}

	public function close() {
		$this->updateCMStorage();
	}

	public function destroy(string $name) {
		$this->updateCMStorage();
	}

	public function gc(int $maxLifetime) { }
}