<?php
/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author B. Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\NMCTheme\AppInfo;

use OC\AppFramework\Bootstrap\Coordinator;
use OC\AppFramework\DependencyInjection\DIContainer;
use OC\L10N\Factory;
use OC\NavigationManager;
use OC\Preview\MimeIconProvider;
use OC\Search\SearchComposer;
use OC\Template\JSCombiner;
use OC\Template\JSResourceLocator;
use OC\URLGenerator;
use OCA\NMCTheme\JSResourceLocatorExtension;
use OCA\NMCTheme\L10N\FactoryDecorator;
use OCA\NMCTheme\Listener\BeforeTemplateRenderedListener;
use OCA\NMCTheme\MimeIconProviderDecorator;
use OCA\NMCTheme\NavigationManagerDecorator;
use OCA\NMCTheme\Search\SearchComposerDecorator;
use OCA\NMCTheme\Service\NMCThemesService;
use OCA\NMCTheme\Themes\Magenta;
use OCA\NMCTheme\Themes\MagentaDark;
use OCA\NMCTheme\Themes\MagentaLight;
use OCA\NMCTheme\Themes\TeleNeoWebFont;
use OCA\NMCTheme\URLGeneratorDecorator;
use OCA\Theming\Service\ThemesService;
use OCA\Theming\Themes\DarkHighContrastTheme;
use OCA\Theming\Themes\DarkTheme;
use OCA\Theming\Themes\DefaultTheme;
use OCA\Theming\Themes\DyslexiaFont;
use OCA\Theming\Themes\HighContrastTheme;
use OCA\Theming\Themes\LightTheme;
use OCP\App\IAppManager;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\AppFramework\QueryException;
use OCP\Files\IMimeTypeDetector;
use OCP\IConfig;
use OCP\INavigationManager;

// FIXME: required private accesses; we have to find better ways
// when integrating upstream
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\L10N\IFactory;
use OCP\Preview\IMimeIconProvider;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

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
	public function otherAppContainer(string $appName) {
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
				$this->getContainer()->getServer()->query(URLGenerator::class),
			);
		});
	}

	/**
	 * Take mimetype customisations out of our nmctheme package, not from anywhere else
	 */
	protected function registerMimeTypeCustomisations(IRegistrationContext $context) {
		$this->getContainer()->getServer()->registerService(IMimeTypeDetector::class, function (ContainerInterface $c) {
			return new \OC\Files\Type\Detection(
				$c->get(IURLGenerator::class),
				$c->get(LoggerInterface::class),
				$c->get(IAppManager::class)->getAppPath(self::APP_ID) . '/resources/config/',
				\OC::$SERVERROOT . '/resources/config/'
			);
		});
	}


	/**
	 * Decorate the IURLGenerator to intercept request for theming favicons.
	 */
	protected function registerJSResourceLocatorExtension(IRegistrationContext $context) {
		$this->getContainer()->getServer()->registerService(JSResourceLocator::class, function (ContainerInterface $c) {
			return new JSResourceLocatorExtension(
				$c->get(LoggerInterface::class),
				$this->getContainer()->getServer()->query(JSCombiner::class),
				$c->get(IAppManager::class)
			);
		});
	}


	/**
	 * Decorate the IMimeIconProvider.
	 */
	protected function registerMimeIconProviderDecorator(IRegistrationContext $context) {
		$this->getContainer()->getServer()->registerService(IMimeIconProvider::class, function (ContainerInterface $c) {
			return new MimeIconProviderDecorator(
				$c->get(IConfig::class),
				$this->getContainer()->getServer()->query(MimeIconProvider::class),
				$c->get(IMimeTypeDetector::class),
				$c->get(IURLGenerator::class),
			);
		});
	}


	/**
	 * Decorate the INavigationManager.
	 */
	protected function registerNavigationManagerDecorator(IRegistrationContext $context) {
		$this->getContainer()->getServer()->registerService(INavigationManager::class, function (ContainerInterface $c) {
			return new NavigationManagerDecorator(
				$c->get(IConfig::class),
				$this->getContainer()->getServer()->query(NavigationManager::class),
			);
		});
	}


	/**
	 * Decorate the L10N IFactory of server with the L10N theming factory
	 * so that any request for translation is either overridden by a value
	 * from this app or delegated to the original factory
	 */
	protected function registerIFactoryDecorator(IRegistrationContext $context) {
		$this->getContainer()->getServer()->registerService(IFactory::class, function (ContainerInterface $c) {
			return new FactoryDecorator(
				$c->get(IConfig::class),
				$this->getContainer()->getServer()->query(Factory::class)
			);
		});
		$context->registerServiceAlias(Factory::class, IFactory::class);
		$context->registerServiceAlias(FactoryDecorator::class, IFactory::class);
	}

	/**
	 * Decorate SearchComposer with blacklisted, unwanted search providers -
	 * to avoid them from being listed and used for searches
	 *
	 * For blacklisting, the ids of the Search\IProvider is used
	 */
	protected function registerSearchComposerDecorator(IRegistrationContext $context) {
		$this->getContainer()->getServer()->registerService(\OC\Search\SearchComposer::class,
			function (ContainerInterface $c) {
				return new SearchComposerDecorator(
					new SearchComposer(
						$c->get(Coordinator::class),
						$c->get(ContainerInterface::class),
						$c->get(IURLGenerator::class),
						$c->get(LoggerInterface::class)
					),
					[
						'contacts', // from apps/dav
						'calendar',
						'tasks',
						'settings_apps', // from apps/settings
						'settings',
						'users',
						'systemtags', // from apps/systemtags (first candidate to enable in the future!)
						'comments'    // from apps comments, (to enable as soon as comments is supported)
					]
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
		$this->otherAppContainer("theming")->registerService(ThemesService::class, function ($c) {
			return new NMCThemesService(
				$c->get(IUserSession::class),
				$c->get(IConfig::class),
				$c->get(LoggerInterface::class),
				$c->get(Magenta::class),
				[$c->get(MagentaLight::class), $c->get(MagentaDark::class)],
				[$c->get(TeleNeoWebFont::class)],
				$c->get(DefaultTheme::class),   // the rest is overhead due to undefined interface (yet)
				$c->get(LightTheme::class),
				$c->get(DarkTheme::class),
				$c->get(HighContrastTheme::class),
				$c->get(DarkHighContrastTheme::class),
				$c->get(DyslexiaFont::class)
			);
		});

		// intercept language reference generation to deviate to appender service
		$this->registerJSResourceLocatorExtension($context);
		
		// intercept requests for favicons to enforce own behavior
		$this->registerURLGeneratorDecorator($context);

		// intercept requests for main navigation elements
		$this->registerMimeIconProviderDecorator($context);

		// intercept requests for main navigation elements
		$this->registerNavigationManagerDecorator($context);

		// intercept requests for translations, theme specific translations have prio
		$this->registerIFactoryDecorator($context);

		// load mimetype customisations from within the theme to keep all customisations in one place
		$this->registerMimeTypeCustomisations($context);

		// blacklist unwanted search providers for full-text search
		$this->registerSearchComposerDecorator($context);

		/**
		 * Add listeners that can inject additional information or scripts before rendering
		 */

		// the listener is helpful to enforce theme constraints and inject additional parts
		$context->registerEventListener(BeforeTemplateRenderedEvent::class, BeforeTemplateRenderedListener::class);
	}

	public function boot(IBootContext $context): void {

	}
}
