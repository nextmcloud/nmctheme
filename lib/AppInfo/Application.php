<?php
/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author B. Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\NMCTheme\AppInfo;

use OCP\IUserSession;
use OCP\IConfig;
use OCA\NMCTheme\Listener\BeforeTemplateRenderedListener;
use OCA\NMCTheme\Service\NMCThemesService;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use Psr\Container\ContainerInterface;
use OCA\Theming\Service\ThemesService;
use OCA\Theming\Themes\DarkHighContrastTheme;
use OCA\Theming\Themes\DarkTheme;
use OCA\Theming\Themes\DefaultTheme;
use OCA\Theming\Themes\DyslexiaFont;
use OCA\Theming\Themes\HighContrastTheme;
use OCA\Theming\Themes\LightTheme;
use OCA\NMCTheme\Themes\Magenta;
use OCA\NMCTheme\Themes\MagentaDark;
use OCA\NMCTheme\Themes\TeleNeoWebFont;

class Application extends App implements IBootstrap {
	public const APP_ID = 'nmctheme';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		// explicitly register own NMCThemesManager to override the Nextcloud standard
        $this->getContainer()->registerService(ThemesService::class, function(ContainerInterface $c) {
            return new NMCThemesService(
                $c->get(IUserSession::class),
                $c->get(IConfig::class),
                $c->get(Magenta::class),        
                [$c->get(MagentaDark::class)],
                [$c->get(TeleNeoWebFont::class)],
                $c->get(DefaultTheme::class),
                $c->get(LightTheme::class),
                $c->get(DarkTheme::class),
                $c->get(HighContrastTheme::class),
                $c->get(DarkHighContrastTheme::class),
                $c->get(DyslexiaFont::class)
            );
        });
        
        // the listener is helpful to enforce theme constraints and inject additional parts
		// $context->registerEventListener(BeforeTemplateRenderedEvent::class, BeforeTemplateRenderedListener::class);
	}

	public function boot(IBootContext $context): void {
		// /** @var ThemesService $themesService */
		// $themesService = $this->getContainer()->get(ThemesService::class);

		// /** @var Magenta $magentaDefault */
		// $magentaDefault = $this->getContainer()->get(Magenta::class);

		// /** @var MagentaDark $magentaDark */
		// $magentaDark = $this->getContainer()->get(MagentaDark::class);

		// /** @var TeleNeoWebFont $teleNeoWebFont */
		// $teleNeoWebFont = $this->getContainer()->get(TeleNeoWebFont::class);

		// $themesService->registerThemes([$teleNeoWebFont, $magentaDefault, $magentaDark], true);
	}
}
