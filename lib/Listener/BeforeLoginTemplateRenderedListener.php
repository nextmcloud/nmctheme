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

use OCA\Theming\Util;
use OCP\AppFramework\Http\Events\BeforeLoginTemplateRenderedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IURLGenerator;

class BeforeLoginTemplateRenderedListener implements IEventListener {
	public function __construct(
		private IURLGenerator $urlGenerator,
		private Util $themingUtil,
	) {
	}

	public function handle(Event $event): void {
		if (!$event instanceof BeforeLoginTemplateRenderedEvent) {
			return;
		}

		$response = $event->getResponse();

		if ($response->getStatus() >= 400 && $response->getStatus() < 600) {
			$params = $response->getParams();
			$params['bodyid'] = 'body-status';
			$response->setParams($params);
		}

		$cacheBuster = $this->themingUtil->getCacheBuster();

		$styles = [
			'telekom-design-tokens.ods.css',
			'nmcdefault.css',
			'nmcstyle.css',
			'icons.css',
		];

		foreach ($styles as $style) {
			\OCP\Util::addHeader(
				'link',
				[
					'rel' => 'stylesheet',
					'href' => $this->urlGenerator->linkTo(
						'nmctheme',
						'css/' . $style,
					) . '?nmcv=' . $cacheBuster,
				],
				'',
			);
		}
	}
}
