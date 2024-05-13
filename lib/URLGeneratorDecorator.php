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

use OCP\IURLGenerator;

/**
 * Deliver different favicon pathes fro nmctheme.
 * All other pathes are computed by the standard NC URLGenerator.
 *
 * The handling of icons in the original themeing app
 * is designed for individual icons per app and has no really planned
 * mechanism to override this behavior in own theme. The themeiing favicons
 * do not work on all modern browsers anyway
 *
 * We catch requests for favicons in the URLGenerator and direct them to
 * our icons set.
 */
class URLGeneratorDecorator implements IURLGenerator {
	protected IURLGenerator $decorated;

	public function __construct(IURLGenerator $decorated) {
		$this-> decorated = $decorated;
	}

	/**
	 * No decoration, only delegate.
	 */
	public function linkToRoute(string $routeName, array $arguments = []): string {
		if ($routeName === 'theming.Icon.getTouchIcon') {
			return $this->decorated->linkToRoute('nmctheme.TouchIcon.getTouchIcon', $arguments);
		}

		return $this->decorated->linkToRoute($routeName, $arguments);
	}

	/**
	 * No decoration, only delegate.
	 */
	public function linkToRouteAbsolute(string $routeName, array $arguments = []): string {
		return $this->decorated->linkToRouteAbsolute($routeName, $arguments);
	}

	/**
	 * No decoration, only delegate.
	 */
	public function linkToOCSRouteAbsolute(string $routeName, array $arguments = []): string {
		return $this->decorated->linkToOCSRouteAbsolute($routeName, $arguments);
	}

	/**
	 * No decoration, only delegate.
	 */
	public function linkTo(string $appName, string $file, array $args = []): string {
		return $this->decorated->linkTo($appName, $file, $args);
	}

	/**
	 * The decorator answer to favicon requests with pathes to the
	 * theming images.
	 *
	 * All other requests go to the standard URLGenerator
	 */
	public function imagePath(string $appName, string $file): string {
		if ($file === 'favicon.ico') {
			return $this->decorated->linkTo('nmctheme', 'img/favicon.ico');
		}

		if ($file === 'favicon-touch.png') {
			return $this->decorated->linkTo('nmctheme', 'img/favicon-touch.png');
		}

		if ($file === 'favicon-mask.svg') {
			return $this->decorated->linkTo('nmctheme', 'img/favicon-mask.svg');
		}
		
		return $this->decorated->imagePath($appName, $file);
	}


	/**
	 * No decoration, only delegate.
	 */
	public function getAbsoluteURL(string $url): string {
		return $this->decorated->getAbsoluteURL($url);
	}

	/**
	 * No decoration, only delegate.
	 */
	public function linkToDocs(string $key): string {
		return $this->decorated->linkToDocs($key);
	}

	/**
	 * No decoration, only delegate.
	 */
	public function linkToDefaultPageUrl(): string {
		return $this->decorated->linkToDefaultPageUrl();
	}

	/**
	 * No decoration, only delegate.
	 */
	public function getBaseUrl(): string {
		return $this->decorated->getBaseUrl();
	}

	/**
	 * No decoration, only delegate.
	 */
	public function getWebroot(): string {
		return $this->decorated->getWebroot();
	}
}
