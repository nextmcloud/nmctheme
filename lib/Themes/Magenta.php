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
	private IAppManager $appManager;
	private IURLGenerator $urlGenerator;
	private IL10N $l;

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
		return [];
	}

	public function getCustomCss(): string {
		$telekomVariables = $this->urlGenerator->linkTo('nmctheme', 'css/telekom-design-tokens.all.css');
		$themeVariables = $this->urlGenerator->linkTo('nmctheme', 'css/nmcdefault.css');
		$iconsVariables = $this->urlGenerator->linkTo('nmctheme', 'dist/icons.css');

		$header = $this->urlGenerator->linkTo('nmctheme', 'css/core/header.css');

		$appmenu = $this->urlGenerator->linkTo('nmctheme', 'css/components/appmenu.css');
		$ncbreadcrumb = $this->urlGenerator->linkTo('nmctheme', 'css/components/ncbreadcrumb.css');
		$ncappnavigation = $this->urlGenerator->linkTo('nmctheme', 'css/components/ncappnavigation.css');
		$ncheadermenu = $this->urlGenerator->linkTo('nmctheme', 'css/components/ncheadermenu.css');
		$ncactionbutton = $this->urlGenerator->linkTo('nmctheme', 'css/components/ncactionbutton.css');

		$apps = $this->urlGenerator->linkTo('nmctheme', 'css/apps/apps.css');
		$files = $this->urlGenerator->linkTo('nmctheme', 'css/apps/files.css');
		
		return "
			@import url('{$telekomVariables}');
			@import url('{$themeVariables}');
			@import url('{$iconsVariables}');
			@import url('{$header}');
			@import url('{$appmenu}');
            @import url('{$ncbreadcrumb}');
            @import url('{$ncappnavigation}');
			@import url('{$ncheadermenu}');
			@import url('{$ncactionbutton}');
			@import url('{$apps}');
			@import url('{$files}');
		";
	}
}
