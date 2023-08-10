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
use OCP\App\IAppManager;
use OCP\IL10N;
use OCP\IURLGenerator;

class Magenta implements ITheme {
	protected IAppManager $appManager;
	protected IURLGenerator $urlGenerator;
	protected IL10N $l;

	public function __construct(IAppManager $appManager,
		IURLGenerator $urlGenerator,
		IL10N $l) {
		$this->appManager = $appManager;
		$this->urlGenerator = $urlGenerator;
		$this->l = $l;
	}

	public function getId(): string {
		return 'magenta25';
	}

	public function getType(): int {
		return ITheme::TYPE_THEME;
	}

	public function getTitle(): string {
		return $this->l->t('MagentaCLOUD');
	}

	public function getEnableLabel(): string {
		return $this->l->t('Enable MagentaCLOUD default.');
	}

	public function getDescription(): string {
		return $this->l->t('MagentaCLOUD new style for NC25 ff.');
	}

	public function getMediaQuery(): string {
		return '';
	}

	public function getCSSVariables(): array {
		$favIconPath = $this->urlGenerator->imagePath('nmctheme', 'favicon.svg');
		$logoPath = $this->urlGenerator->imagePath('nmctheme', 'telekom/tlogocarrier.svg');
		return [
			'--image-favicon' => "url('" . $favIconPath . "')",
			#            '--image-logo' =>  "url('" . $logoPath . "')",
			'--image-logoheader' => "url('" . $logoPath . "')"
		];
	}

	public function getCustomCss(): string {
		$telekomVariables = $this->urlGenerator->linkTo('nmctheme', 'dist/telekom/css/telekom-design-tokens.all.css');
		$themeVariables = $this->urlGenerator->linkTo('nmctheme', 'css/nmcdefault.css');
		$iconsVariables = $this->urlGenerator->linkTo('nmctheme', 'dist/icons.css');
		$themeStyle = $this->urlGenerator->linkTo('nmctheme', 'css/nmcstyle.css');

		
		return "
			@import url('{$telekomVariables}');
			@import url('{$themeVariables}');
			@import url('{$iconsVariables}');
			@import url('{$themeStyle}');
		";
	}
}
