<?php

declare(strict_types=1);

/**
 * Copyright (c) 2016 Joas Schilling <nickvergessen@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 *
 * All tests from server are copies and repeated to make sure that the
 * decoration works properly.
 *
 * TODO: Find a way to inherit the tests from server instead of copying it
 */

namespace OCA\NMCTheme\Tests\L10N;

use OC\L10N\Factory;
use OCP\IL10N;

class FactorySupportedLangTest extends FactoryTestCase {

	protected function setUp(): void {
		parent::setUp();



		$this->config
			->expects(self::any())
			->method('getSystemValue')
			->willReturnMap([
				['installed', false, true],
				['nmc_supported_locales', false, ['de', 'de_DE', 'en_GB',   // suppored
					'es', 'es_MX',          // supported not themed
					'de_AT', 'co', 'co_XX' ]], // not supported
			]);

		$this->factory = $this->getFactory();
		//$this->factory->getDecorated()->expects(self::once())
		//	->method('findL10nDir')
		//	->with($app)
		//	->willReturn(\OC::$SERVERROOT . '//l10n/');
	}

	public function dataForTranslationsForAppTest() {
		return [
			['core', 'fr', false],
			['lib', 'fr', false],
			['files', 'fr', false],
			['nmctheme', 'fr', false],
			['end_to_end_encryption', 'fr', false],
			['_unknown_app_', 'fr', false],
			['core', 'en', true],
			// server translation file non existing on V25
			['lib', 'en', false],
			['files', 'en', true],
			['nmctheme', 'en', true],
			['end_to_end_encryption', 'en', true],
			// does not exist V25
			['_unknown_app_', 'en', false],
			['core', 'de', true],
			['lib', 'de', true],
			['files', 'de', true],
			['nmctheme', 'de', true],
			['end_to_end_encryption', 'de', true],
			['_unknown_app_', 'de', true],
			['core', 'de_DE', true],
			['lib', 'de_DE', true],
			['files', 'de_DE', true],
			['nmctheme', 'de_DE', true],
			['end_to_end_encryption', 'de_DE', true],
			// core is always torn in
			['_unknown_app_', 'de_DE', true],
			['core', 'es', true],
			['lib', 'es', true],
			['files', 'es', true],
			['nmctheme', 'es', false],
			['end_to_end_encryption', 'es', true],
			// core is always torn in
			['_unknown_app_', 'es', true],
			['core', 'es_MX', true],
			['lib', 'es_MX', true],
			['files', 'es_MX', true],
			['nmctheme', 'es_MX', false],
			['end_to_end_encryption', 'es_MX', true],
			// core is always torn in as default
			['_unknown_app_', 'es_MX', true],
			['core', 'de_AT', false],
			['lib', 'de_AT', false],
			['files', 'de_AT', false],
			['nmctheme', 'de_AT', false],
			['end_to_end_encryption', 'de_AT', false],
			['_unknown_app_', 'de_AT', false],
			['core', 'co', false],
			['lib', 'co', false],
			['files', 'co', false],
			['nmctheme', 'co', false],
			['end_to_end_encryption', 'co', false],
			['_unknown_app_', 'co', false]
		];
	}

	/**
	 * @dataProvider dataForTranslationsForAppTest
	 */
	public function testTranslationsForApp(string $app, string $lang, bool $hasValues) {
		if ($hasValues) {
			$this->assertNotEmpty($this->factory->getTranslationsForApp($app, $lang));
		} else {
			$this->assertEmpty($this->factory->getTranslationsForApp($app, $lang));
		}
	}

	public function dataForGetL10N() {
		return [
			['core', 'fr', null, 'en', 'en'],
			['lib', 'fr', null, 'en', 'en'],
			['files', 'fr', null, 'en', 'en'],
			['nmctheme', 'fr', null, 'en', 'en'],
			['_unknown_app_', 'fr', null, 'en', 'en'],
			['core', 'fr', 'fr_BE', 'en', 'en'],
			['lib', 'fr', 'fr_BE', 'en', 'en'],
			['files', 'fr', 'fr_BE', 'en', 'en'],
			['nmctheme', 'fr', 'fr_BE', 'en', 'en'],
			['_unknown_app_', 'fr', 'fr_BE', 'en', 'en'],
			['core', 'en', null, 'en', 'en'],
			['lib', 'en', null, 'en', 'en'],
			['files', 'en', null, 'en', 'en'],
			['nmctheme', 'en', null, 'en', 'en'],
			['_unknown_app_', 'en', null, 'en', 'en'],
			['core', 'en', 'en_US', 'en', 'en'],
			['lib', 'en', 'en_US', 'en', 'en'],
			['files', 'en', 'en_US', 'en', 'en'],
			['nmctheme', 'en', 'en_US', 'en', 'en'],
			['_unknown_app_', 'en', 'en_US', 'en', 'en'],
			['core', 'en', 'en_GB', 'en', 'en_GB'],
			['lib', 'en', 'en_GB', 'en', 'en_GB'],
			['files', 'en', 'en_GB', 'en', 'en_GB'],
			['nmctheme', 'en', 'en_GB', 'en', 'en_GB'],
			['_unknown_app_', 'en', 'en_GB', 'en', 'en_GB'],
			['core', null, 'en_US', 'en', 'en'],
			['lib', null, 'en_US', 'en', 'en'],
			['files', null, 'en_US', 'en', 'en'],
			['nmctheme', null, 'en_US', 'en', 'en'],
			['_unknown_app_', null, 'en_US', 'en', 'en'],
			['core', null, 'de_DE', 'en', 'de_DE'],
			['lib', null, 'de_DE', 'en', 'de_DE'],
			['files', null, 'de_DE', 'en', 'de_DE'],
			['nmctheme', null, 'de_DE', 'en', 'de_DE'],
			['_unknown_app_', null, 'de_DE', 'en', 'de_DE'],
			['core', 'de', 'de_DE', 'de', 'de_DE'],
			['lib', 'de', 'de_DE', 'de', 'de_DE'],
			['files', 'de', 'de_DE', 'de', 'de_DE'],
			['nmctheme', 'de', 'de_DE', 'de', 'de_DE'],
			['_unknown_app_', 'de', 'de_DE', 'de', 'de_DE'],
			['core', 'de', null, 'de', 'de'],
			['lib', 'de', null, 'de', 'de'],
			['files', 'de', null, 'de', 'de'],
			['nmctheme', 'de', null, 'de', 'de'],
			['_unknown_app_', 'de', null, 'de', 'de'],
		];
	}

	/**
	 * @dataProvider dataForGetL10N
	 */
	public function testGetL10N(string $app, string|null $lang, string|null $locale,
		string $expectedLang, string $expectedLocale) {
		$l10n = $this->factory->get($app, $lang, $locale);
		$this->assertInstanceOf(IL10N::class, $l10n);
		$this->assertEquals($expectedLang, $l10n->getLanguageCode());
		$this->assertEquals($expectedLocale, $l10n->getLocaleCode());
	}


	public function testFindAvailableLocales() {
		$locales = $this->factory->findAvailableLocales();
		$this->assertCount(7, $locales);

		// TODO: more testing!
	}

	public function testFindAvailableLanguages() {
		$languages = $this->factory->findAvailableLanguages();
		$this->assertCount(6, $languages);
		$languages = $this->factory->findAvailableLanguages('nmctheme');
		$this->assertCount(4, $languages);

		// TODO: more testing!
	}

	public function dataForFindLanguage() {
		return [
			['User_jp', 'en', 'en', 'en'],
			['User_null', 'en', 'en', 'en'],
			['User_de', 'de', 'de', 'de'],
			['User_es', 'es', 'en', 'es']
		];
	}

	/**
	 * @dataProvider dataForFindLanguage
	 */
	public function testFindLanguage(string $username, string $expectedCore,
		string $expectedTheme, string $expectedUnknown) {
		$this->setUser($username);
		self::assertSame($expectedCore, $this->factory->findLanguage('core'));
		self::assertSame($expectedTheme, $this->factory->findLanguage('nmctheme'));
		self::assertSame($expectedUnknown, $this->factory->findLanguage('_unknown_'));
	}

}
