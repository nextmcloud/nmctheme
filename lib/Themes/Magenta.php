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
use OCA\Theming\Util;
use OCP\App\IAppManager;
use OCP\IL10N;
use OCP\IURLGenerator;

class Magenta implements ITheme {
	protected Util $themingUtil;
	protected IAppManager $appManager;
	protected IURLGenerator $urlGenerator;
	protected IL10N $l;

	public function __construct(
		Util $themingUtil,
		IAppManager $appManager,
		IURLGenerator $urlGenerator,
		IL10N $l) {
		$this->themingUtil = $themingUtil;
		$this->appManager = $appManager;
		$this->urlGenerator = $urlGenerator;
		$this->l = $l;
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
		$favIconPath = $this->urlGenerator->imagePath('nmctheme', 'favicon.svg');
		$logoPath = $this->urlGenerator->imagePath('nmctheme', 'telekom/tlogocarrier.svg');
		return [
			'--image-favicon' => "url('" . $favIconPath . "')",
			'--image-logoheader' => "url('" . $logoPath . "')"
		];
	}

	public function getCustomCss(): string {
		$telekomVariables = $this->urlGenerator->linkTo('nmctheme', 'css/telekom-design-tokens.all.css');
		$themeVariables = $this->urlGenerator->linkTo('nmctheme', 'css/nmcdefault.css');
		$iconsVariables = $this->urlGenerator->linkTo('nmctheme', 'css/icons.css');
		$themeStyle = $this->urlGenerator->linkTo('nmctheme', 'css/nmcstyle.css');

		$cacheBuster = $this->themingUtil->getCacheBuster();

		$telekomVariables .= '?nmcv=' . $cacheBuster;
		$themeVariables .= '?nmcv=' . $cacheBuster;
		$iconsVariables .= '?nmcv=' . $cacheBuster;
		$themeStyle .= '?nmcv=' . $cacheBuster;

		return "
			@import url('{$telekomVariables}');
			@import url('{$themeVariables}');
			@import url('{$iconsVariables}');
			@import url('{$themeStyle}');
		";
	}

	public function getMeta(): array {
		return [[
			'name' => '',
			'content' => '',
		]];
	}
}
