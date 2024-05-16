<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author Bernd Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\NMCTheme;

use OCP\Files\IMimeTypeDetector;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\Preview\IMimeIconProvider;

class MimeIconProviderDecorator implements IMimeIconProvider {

	protected IConfig $config;
	protected IMimeIconProvider $decorated;
	protected IMimeTypeDetector $mimetypeDetector;
	protected IURLGenerator $urlGenerator;

	public function __construct(IConfig $config, IMimeIconProvider $decorated, IMimeTypeDetector $mimetypeDetector, IURLGenerator $urlGenerator) {
		$this->config = $config;
		$this->decorated = $decorated;
		$this->mimetypeDetector = $mimetypeDetector;
		$this->urlGenerator = $urlGenerator;
	}

	public function getMimeIconUrl(string $mime): null|string {
		if (!$mime) {
			return null;
		}

		// Fetch all the aliases
		$aliases = $this->mimetypeDetector->getAllAliases();

		// Remove comments
		$aliases = array_filter($aliases, static function ($key) {
			return !($key === '' || $key[0] === '_');
		}, ARRAY_FILTER_USE_KEY);

		// Map all the aliases recursively
		foreach ($aliases as $alias => $value) {
			if ($alias === $mime) {
				$mime = $value;
			}
		}

		$fileName = str_replace('/', '-', $mime);

		if ($url = $this->searchfileName($fileName)) {
			return $url;
		}

		$mimeType = explode('/', $mime)[0];

		if ($url = $this->searchfileName($mimeType)) {
			return $url;
		}

		return null;
	}
	
	private function searchfileName(string $fileName): null|string {

		// always return nmctheme style icons
		$path = "/customapps/nmctheme/img/filetypes/$fileName.svg";

		if (file_exists(\OC::$SERVERROOT . $path)) {
			return $this->urlGenerator->getAbsoluteURL($path);
		}

		return null;
	}
}
