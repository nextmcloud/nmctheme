<?php
/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author B. Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\NMCTheme\AppInfo;

use OC\AppFramework\DependencyInjection\DIContainer;
use OC\URLGenerator;
use OCA\NMCTheme\Listener\BeforeTemplateRenderedListener;
use OCA\NMCTheme\Service\NMCThemesService;
use OCA\NMCTheme\Themes\Magenta;
use OCA\NMCTheme\Themes\MagentaDark;
use OCA\NMCTheme\Themes\TeleNeoWebFont;
use OCA\NMCTheme\URLGeneratorDecorator;
use OCA\Theming\Service\ThemesService;
use OCA\Theming\Themes\DarkHighContrastTheme;
use OCA\Theming\Themes\DarkTheme;
use OCA\Theming\Themes\DefaultTheme;
use OCA\Theming\Themes\DyslexiaFont;
use OCA\Theming\Themes\HighContrastTheme;
use OCA\Theming\Themes\LightTheme;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\AppFramework\QueryException;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\IUserSession;

class Application extends App implements IBootstrap {
	public const APP_ID = 'nmctheme';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	/**
	 * Services are aquired by DI with priority for registrations for the own app.
	 * As ThemesService is registered before or after nmctheme, and we want to
	 * register NMCTheme version instead we either have to:
	 * - register a theming app container early if nmctheme is registered before
	 *   theming
	 * - use the already registered container if theming is registered before
	 *   nmctheme
	 *
	 * The "foreign" theming container can the be used for enforcing the registration
	 * of the NMCThemesService factory method.
	 */
	public function getCapturedThemeingContainer() {
		$appName = "theming";
		try {
			$container = \OC::$server->getRegisteredAppContainer($appName);
		} catch (QueryException $e) {
			$container = new DIContainer($appName);
			\OC::$server->registerAppContainer($appName, $container);
		}

		return $container;
	}

	/**
	 * Decorate the IURLGenerator to intercept request for theming favicons.
	 */
	protected function registerURLGeneratorDecorator(IRegistrationContext $context) {
		$this->getContainer()->getServer()->registerService(IURLGenerator::class, function ($c) {
			return new URLGeneratorDecorator(
				$this->getContainer()->getServer()->query(URLGenerator::class)
			);
		});
	}


	/**
	 * Register all kind of decorators so that the theme is in control
	 * of:
	 * - the set of available themes
	 * - the translations (to be overridden)
	 * - the favicons
	 */
	public function register(IRegistrationContext $context): void {

        /**
         * Register decorators softly extending Nextcloud upstream standard
         */

		// explicitly register own NMCThemesManager to override the Nextcloud standard
		$this->getCapturedThemeingContainer()->registerService(ThemesService::class, function ($c) {
			return new NMCThemesService(
				$c->get(IUserSession::class),
				$c->get(IConfig::class),
				$c->get(Magenta::class),
				[$c->get(MagentaDark::class)],
				[$c->get(TeleNeoWebFont::class)],
				$c->get(DefaultTheme::class),   // the rest is overhead due to undefined interface (yet)
				$c->get(LightTheme::class),
				$c->get(DarkTheme::class),
				$c->get(HighContrastTheme::class),
				$c->get(DarkHighContrastTheme::class),
				$c->get(DyslexiaFont::class)
			);
		});
		
        // intercept requests for favicons to enforce own behavior
		$this->registerURLGeneratorDecorator($context);

        /**
         * Add listeners that can inject additional information or scripts before rendering
         */

		// the listener is helpful to enforce theme constraints and inject additional parts
		$context->registerEventListener(BeforeTemplateRenderedEvent::class, BeforeTemplateRenderedListener::class);
	}

	public function boot(IBootContext $context): void {
	}
}
