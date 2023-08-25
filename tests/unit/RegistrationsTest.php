<?php
/*
 * @copyright Copyright (c) 2021 T-Systems International
 *
 * @author Bernd Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 */

declare(strict_types=1);

use OC\AppFramework\Bootstrap\Coordinator;
use OCA\NMCTheme\AppInfo\Application;

use OCA\NMCTheme\L10N\FactoryDecorator;
use OCA\NMCTheme\Service\NMCThemesService;

use OCA\NMCTheme\URLGeneratorDecorator;
use OCA\Theming\Service\ThemesService;

use OCP\IURLGenerator;
use OCP\L10N\IFactory;

use PHPUnit\Framework\TestCase;

class RegistrationsTest extends TestCase {
	public function setUp() :void {
		parent::setUp();

		$this->app = new Application();
		$coordinator = \OC::$server->get(Coordinator::class);
		$this->app->register($coordinator->getRegistrationContext()->for('nmctheme'));
	}

	public function testThemingOverrideRegistration() :void {
		$themesService = \OC::$server->getRegisteredAppContainer("theming")->get(ThemesService::class);
		$this->assertInstanceOf(NMCThemesService::class, $themesService, "FATAL: NMCTheme is not registered!");
	}

	public function testDecoratedURLGenerator() :void {
		$urlGenerator = $this->app->getContainer()->get(IURLGenerator::class);
		$this->assertInstanceOf(URLGeneratorDecorator::class, $urlGenerator, "FATAL: URLGeneratorDecorator failed to register!");
	}

	public function testDecoratedIFactoryL10N() :void {
		$factoryDecorator = $this->app->getContainer()->get(IFactory::class);
		$this->assertInstanceOf(FactoryDecorator::class, $factoryDecorator, "FATAL: L10N FactoryDecorator failed to register!");
	}
}
