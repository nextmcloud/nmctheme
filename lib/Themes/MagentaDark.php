<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2022 Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
 * @author John Molakvoæ <skjnldsv@protonmail.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\NMCTheme\Themes;

use OCA\Theming\ITheme;
use OCP\IL10N;
use OCP\IURLGenerator;

class MagentaDark implements ITheme {
	public IURLGenerator $urlGenerator;
	public IL10N $l;

	public function __construct(IURLGenerator $urlGenerator,
								IL10N $l) {
		$this->urlGenerator = $urlGenerator;
		$this->l = $l;
	}

	public function getId(): string {
		return 'magenta25dark';
	}

	public function getType(): int {
		return ITheme::TYPE_THEME;
	}

	public function getTitle(): string {
		return $this->l->t('MagentaCLOUD dark');
	}

	public function getEnableLabel(): string {
		return $this->l->t('Enable MagentaCLOUD dark theme.');
	}

	public function getDescription(): string {
		return $this->l->t('MagentaCLOUD new dark style for NC25 ff.');
	}

	public function getMediaQuery(): string {
		return '(prefers-color-scheme: dark)';
	}

	public function getCSSVariables(): array {
		return [];
	}

	public function getCustomCss(): string {
		$telekomTokens  = $this->urlGenerator->linkTo('nmctheme', 'css/telekom-design-tokens.all.css');
		$themeVariables = $this->urlGenerator->linkTo('nmctheme', 'css/nmcdark.css');
		
        return "
            @import url('$telekomTokens');
            @import url('$themeVariables');        
        ";
	}
}
