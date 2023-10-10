<?php
/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author Bernd Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\NMCTheme\Service;

use OCP\IL10N;

class NMCFilesService {

	private IL10N $l10n;

	public function __construct(IL10N $l10n) {
		$this->l10n = $l10n;
	}

	public function rearrangeFilesAppNavigation() {

		$entries = \OCA\Files\App::getNavigationManager()->getAll();
		
		if (array_key_exists('recent', $entries)) {
			$entry = $entries['recent'];
			$entry['classes'] = "hidden-visually";
			\OCA\Files\App::getNavigationManager()->add($entry);
		}
		
		if (array_key_exists('shareoverview', $entries)) {
			$entry = $entries['shareoverview'];
			$entry['classes'] = "hidden-visually";
			unset($entry['sublist']);
			unset($entry['expandedState']);
			\OCA\Files\App::getNavigationManager()->add($entry);
		}
		
		if (array_key_exists('favorites', $entries)) {
			$entry = $entries['favorites'];
			$entry['order'] = -5;
			unset($entry['sublist']);
			unset($entry['expandedState']);
			\OCA\Files\App::getNavigationManager()->add($entry);
		}
	}

	public function addFilesAppNavigationEntries() {

		\OCA\Files\App::getNavigationManager()->add(function () {
			return [
				'id' => 'sharingout',
				'appname' => 'files_sharing',
				'script' => 'list.php',
				'order' => 16,
				'name' => $this->l10n->t('Shared with others'),
			];
		});

		\OCA\Files\App::getNavigationManager()->add(function () {
			return [
				'id' => 'sharingin',
				'appname' => 'files_sharing',
				'script' => 'list.php',
				'order' => 15,
				'name' => $this->l10n->t('Shared with you'),
			];
		});
	}
}
