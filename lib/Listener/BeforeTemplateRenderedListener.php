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

use OCP\AppFramework\Http\TemplateResponse;
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
		// you can add extra styles depending on situation
		// if ($event->getResponse()->getRenderAs() === TemplateResponse::RENDER_AS_USER) {
		//     \OCP\Util::addStyle("nmctheme", "some_extra_xxx");
		// }
	}
}
