<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author B. Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\NMCTheme\Listener;

use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IURLGenerator;

class BeforeTemplateRenderedListener implements IEventListener {
	private IURLGenerator $urlGenerator;

	public function __construct(
		IURLGenerator $urlGenerator
	) {
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * The registration of Magenta themes is only done
	 * if rendering in browser takes place. (and not in
	 * application bootstrapping).
	 */
	public function handle(Event $event): void {
		$response = $event->getResponse();

		if (($response->getStatus() >= 400) && ($response->getStatus() < 600)) {
			// render client error states with own layout => own #body-status id
			$tmplparams = $response->getParams();
			$tmplparams['bodyid'] = "body-status";
			$response->setParams($tmplparams);
		}

		// you can add additional styles, links and scripts before rendering
		\OCP\Util::addScript('nmctheme', 'nmcfooter', 'theming');
		\OCP\Util::addScript("nmctheme", "../dist/filessettings");
	}
}
