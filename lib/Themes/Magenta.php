<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author Bernd Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\NMCTheme\Themes;

use OCA\Theming\ITheme;
use OCP\IL10N;
use OCP\IURLGenerator;

class Magenta implements ITheme {
	public function __construct(
		protected IURLGenerator $urlGenerator,
		protected IL10N $l,
	) {
	}

	public function getId(): string {
		return 'default';
	}

	public function getType(): int {
		return ITheme::TYPE_THEME;
	}

	public function getTitle(): string {
		return $this->l->t('System Design (Standard)');
	}

	public function getEnableLabel(): string {
		return $this->l->t('Enable system design');
	}

	public function getDescription(): string {
		return $this->l->t('MagentaCLOUD adapts to the settings of your system.');
	}

	public function getMediaQuery(): string {
		return '';
	}

	public function getCSSVariables(): array {
		$faviconPath = $this->urlGenerator->imagePath('nmctheme', 'favicon.svg');
		$logoPath = $this->urlGenerator->imagePath('nmctheme', 'telekom/tlogocarrier.svg');

		return [
			'--image-favicon' => "url('$faviconPath')",
			'--image-logoheader' => "url('$logoPath')",
		];
	}

	public function getCustomCss(): string {
		return '';
	}

	public function getMeta(): array {
		return [];
	}
}
