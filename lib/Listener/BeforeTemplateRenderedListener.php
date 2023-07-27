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
use OCA\Theming\Service\ThemesService;
use OCA\NMCTheme\Themes\TeleNeoWebFont;

class BeforeTemplateRenderedListener implements IEventListener {
	private ThemesService $themesService;
	private TeleNeoWebFont $teleNeoWebFont;

	public function __construct(
		ThemesService $themesService,
		TeleNeoWebFont $teleNeoWebFont) {
		$this->themesService = $themesService;
		$this->teleNeoWebFont = $teleNeoWebFont;
	}

	/**
	 * The registration of Magenta themes is only done
	 * if rendering in browser takes place. (and not in
	 * application bootstrapping).
	 */
	public function handle(Event $event): void {
		// always enable TeleNeoWebFonts if this theme is installed
		$this->themesService->enableTheme($this->teleNeoWebFont);
	}
}
