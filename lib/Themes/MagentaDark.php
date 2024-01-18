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

class MagentaDark extends Magenta implements ITheme {
	public function __construct(
		IAppManager $appManager,
		IURLGenerator $urlGenerator,
		IL10N $l
	) {
		parent::__construct($appManager, $urlGenerator, $l);
	}

	public function getId(): string {
		return 'magenta25dark';
	}

	public function getTitle(): string {
		return $this->l->t('Dark Design');
	}

	public function getEnableLabel(): string {
		return $this->l->t('Enable dark design');
	}

	public function getDescription(): string {
		return $this->l->t('A dark design to relieve eye strain.');
	}

	public function getMediaQuery(): string {
		return '(prefers-color-scheme: dark)';
	}
}
