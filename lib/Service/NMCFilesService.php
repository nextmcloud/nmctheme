<?php
/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author Bernd Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\NMCTheme\Service;

use OC\Files\View;
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
				'order' => 15,
				'name' => $this->l10n->t('Shared with others'),
			];
		});

		\OCA\Files\App::getNavigationManager()->add(function () {
			return [
				'id' => 'sharingin',
				'appname' => 'files_sharing',
				'script' => 'list.php',
				'order' => 16,
				'name' => $this->l10n->t('Shared with you'),
			];
		});
	}

	public static function buildFileStorageStatistics(string $dir = '/') {
		// information about storage capacities
		$storageInfo = \OC_Helper::getStorageInfo($dir);
		$trashbin = self::getTrashbinSize(\OC_User::getUser());
		$free = $storageInfo['free'] - $trashbin;
		$used = $storageInfo['used'] + $trashbin;
		$relative = self::getUsedRelative($storageInfo['quota'], $storageInfo['total'], $used);
		
		return [
			'freeSpace' => $free,
			'quota' => $storageInfo['quota'],
			'total' => $storageInfo['total'],
			'used' => $used,
			'relative' => $relative,
			'trashbin' => $trashbin,
			'owner' => $storageInfo['owner'],
			'ownerDisplayName' => $storageInfo['ownerDisplayName'],
			'mountType' => $storageInfo['mountType'],
			'mountPoint' => $storageInfo['mountPoint'],
		];
	}

	private static function getTrashbinSize($user) {
		$view = new View('/' . $user);
		$fileInfo = $view->getFileInfo('/files_trashbin');
		return isset($fileInfo['size']) ? $fileInfo['size'] : 0;
	}

	private static function getUsedRelative($quota, $total, $used) {
		if ($total > 0) {
			if ($quota > 0 && $total > $quota) {
				$total = $quota;
			}
			// prevent division by zero or error codes (negative values)
			$relative = round(($used / $total) * 10000) / 100;
		} else {
			$relative = 0;
		}

		return $relative;
	}
}
