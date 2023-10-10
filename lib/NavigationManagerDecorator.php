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

use OCP\IConfig;
use OCP\INavigationManager;

class NavigationManagerDecorator implements INavigationManager {

	protected IConfig $config;
	protected INavigationManager $decorated;

	public function __construct(IConfig $config, INavigationManager $decorated) {
		$this->config = $config;
		$this->decorated = $decorated;
	}

	/**
	 * No decoration, only delegate.
	 */
	public function add($entry) {
		$this->decorated->add($entry);
	}

	/**
	 * No decoration, only delegate.
	 */
	public function setActiveEntry($appId) {
		return $this->decorated->setActiveEntry($appId);
	}

	/**
	 * No decoration, only delegate.
	 */
	public function getActiveEntry() {
		return $this->decorated->getActiveEntry();
	}

	/**
	 * Deactivate use menu entries.
	 */
	public function getAll(string $type = self::TYPE_APPS): array {
		$all = $this->decorated->getAll($type);

		if($type === 'settings') {
			$deactivate = $this->config->getSystemValue('nmc_deactivate_user_menu_entry', false);

			foreach ($deactivate as $value) {
				unset($all[$value]);
			}
		}

		return $all;
	}

	/**
	 * No decoration, only delegate.
	 */
	public function setUnreadCounter(string $id, int $unreadCounter): void {
		$this->decorated->setUnreadCounter($id, $unreadCounter);
	}
}
