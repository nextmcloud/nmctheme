<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author B. Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * These tests check the themeing override/extend translation functionality
 * AND for missing translation translations
 *
 * The app registration must be run for the test, so make sure the app is
 * activated in phpunit bootstrap.php.
 *
 */
namespace OCA\NMCTheme\Test\L10N;

use OCA\NMCTheme\Controller\L10NAppendController;
use OCA\NMCTheme\L10N\FactoryDecorator;
use OCP\App\IAppManager;
use OCP\IRequest;
use OCP\L10N\IFactory;

use PHPUnit\Framework\TestCase;

class DecoratorTest extends TestCase {

	/** @var string[] */
	protected array $langFiles;

	/** @var string */
	protected string $l10nPath;

	/** @var string[] */
	protected array $longLangs;
	/** @var string[] */
	protected array $shortLangs;
	/** @var string[] */
	protected array $languages;


	protected function setUp(): void {
		parent::setUp();

		$this->app = new \OCP\AppFramework\App("nmctheme");
		$this->appMgr = $this->app->getContainer()->get(IAppManager::class);
		$this->l10nPath = $this->appMgr->getAppPath("nmctheme") . '/l10n/';

		$files = scandir($this->l10nPath);
		$this->langFiles = array_values(array_filter($files, function ($filename) {
			return (strpos($filename, '.json') != false);
		}));
		$this->longLangs = array_values(array_map(function ($filename) {
			return substr($filename, 0, strpos($filename, '.json'));
		}, $this->langFiles));

		$this->shortLangs = array_values(array_map(function ($longlang) {
			$pos = strpos($longlang, '_');
			if ($pos == false) {
				return $longlang;
			} else {
				return substr($longlang, 0, $pos);
			}
		}, $this->longLangs));

		$this->languages = array_unique(array_merge($this->shortLangs, $this->longLangs));

		$this->nmcFactory = $this->app->getContainer()->get(IFactory::class);
		$this->assertInstanceOf(FactoryDecorator::class, $this->nmcFactory);
	}

	/**
	 * Check the readability of the translation files
	 */
	public function testJsonLoad(): void {
		foreach ($this->langFiles as $filename) {
			$json = json_decode(file_get_contents($this->l10nPath . '/' . $filename), true);
			$lastErr = json_last_error();
			$this->assertEquals(0, $lastErr, "json problem: " . $lastErr . "-" . json_last_error_msg());
		}
	}


	/**
	 * Iterate over all translations
	 */
	public function testAllOverrides(): void {
		$arguments = [ "0", "1", "2", "3" , "4", "5", "6", "7", "8", "9", "10"];

		foreach ($this->languages as $lang) {
			$overrides = $this->nmcFactory->getOverrides($lang);
			foreach ($overrides as $app => $apptranslations) {
				$l = $this->nmcFactory->get($app, $lang);
				foreach ($apptranslations as $key => $translation) {
					if (\is_array($translation)) {
						$this->assertEquals($translation[0] . '|' . $translation[1],
							$l->t($key, []), "Translation({$lang},{$app}) failing singular override!");
						;
					} else {
						$expected = vsprintf($translation, $arguments);
						$this->assertEquals($expected, $l->t($key, $arguments),
							"Translation({$lang},{$app}) failing override!");
						;
					}
				}
			}
		}
	  
	}


	/**
	 * Assert that all theme overrides are contained in all translation overrides
	 * in the theme. To do this, there should be no diff in the keys in the different
	 * files.
	 */
	public function testMissingTranslations(): void {
		for ($pair1 = 0; $pair1 < sizeof($this->longLangs); $pair1++) {
			for ($pair2 = $pair1 + 1; $pair2 < sizeof($this->longLangs); $pair2++) {
				$appoverrides1 = $this->nmcFactory->getOverrides($this->longLangs[$pair1]);
				$appoverrides2 = $this->nmcFactory->getOverrides($this->longLangs[$pair2]);

				// check for the same app key set
				$diffapps = array_diff(array_keys($appoverrides1), array_keys($appoverrides2));
				$this->assertEmpty($diffapps,
					"App sections differ {$this->langFiles[$pair1]} <==> {$this->langFiles[$pair2]} :\n- " .
						implode(",\n- ", $diffapps));

				// check the same translation keys
				foreach (array_keys($appoverrides1) as $app) {
					$translations1 = $appoverrides1[$app];
					$translations2 = $appoverrides2[$app];
					$diffkeys = array_diff(array_keys($translations1), array_keys($translations2));
					$this->assertEmpty($diffkeys,
						"----\nApp override keys differ: {$this->langFiles[$pair1]}[{$app}] <==> {$this->langFiles[$pair2]}[{$app}] :\n# " .
							implode(",\n# ", $diffkeys));
				}
			}
		}
	}


	public function testL10NAppendControllerCreate() {
		new L10NAppendController(
			$this->app->getContainer()->get(IRequest::class),
			$this->app->getContainer()->get(FactoryDecorator::class)
		);
	}
}
