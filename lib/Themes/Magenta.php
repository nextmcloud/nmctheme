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
	public IURLGenerator $urlGenerator;
	public IL10N $l;

	public function __construct(IURLGenerator $urlGenerator,
								IL10N $l) {
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
		$telekomTokens = $this->urlGenerator->linkTo('nmctheme', 'css/telekom-design-tokens.all.css');
		$themeVariables = $this->urlGenerator->linkTo('nmctheme', 'css/nmcdefault.css');
		
		return "
            @import url('$telekomTokens');
            @import url('$themeVariables');        
        ";
	}
}
