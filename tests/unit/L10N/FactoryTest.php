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

use OC\L10N\LanguageNotFoundException;
use OCP\IUser;
use OCP\L10N\ILanguageIterator;

class FactoryTest extends FactoryTestCase {

	protected function setUp(): void {
		parent::setUp();
	}

	public function dataFindAvailableLanguages(): array {
		return [
			[null],
			['files'],
		];
	}

	public function testFindLanguageWithExistingRequestLanguageAndNoApp(): void {
		$factory = $this->getFactory(['languageExists']);
		$this->invokePrivate($factory->getDecorated(), 'requestLanguage', ['de']);
		$factory->getDecorated()->expects(self::once())
			->method('languageExists')
			->with(null, 'de')
			->willReturn(true);

		self::assertSame('de', $factory->findLanguage());
	}

	public function testFindLanguageWithExistingRequestLanguageAndApp(): void {
		$factory = $this->getFactory(['languageExists']);
		$this->invokePrivate($factory->getDecorated(), 'requestLanguage', ['de']);
		$factory->getDecorated()->expects(self::once())
				->method('languageExists')
				->with('MyApp', 'de')
				->willReturn(true);
 
		self::assertSame('de', $factory->findLanguage('MyApp'));
	}

	public function testFindLanguageWithNotExistingRequestLanguageAndExistingStoredUserLanguage(): void {
		$factory = $this->getFactory(['languageExists']);
		$this->invokePrivate($factory->getDecorated(), 'requestLanguage', ['de']);
		$factory->getDecorated()->expects(self::exactly(2))
				->method('languageExists')
				->willReturnMap([
					['MyApp', 'de', false],
					['MyApp', 'jp', true],
				]);
		
		$this->config
			->expects($this->any())
			->method('getSystemValue')
			->willReturnMap([
				['installed', false, true],
				['force_language', false, false],
				['default_language', false, 'es']
			]);
		$this->setUser('User_jp');
		self::assertSame('jp', $factory->findLanguage('MyApp'));
	}

	public function testFindLanguageWithNotExistingRequestLanguageAndNotExistingStoredUserLanguage(): void {
		$factory = $this->getFactory(['languageExists'], true);
		$this->invokePrivate($factory->getDecorated(), 'requestLanguage', ['de']);
		$factory->getDecorated()->expects(self::exactly(3))
			->method('languageExists')
			->willReturnMap([
				['MyApp', 'de', false],
				['MyApp', 'jp', false],
				['MyApp', 'es', true],
			]);
		$this->config
			->expects($this->any())
			->method('getSystemValue')
			->willReturnMap([
				['installed', false, true],
				['force_language', false, false],
				['default_language', false, 'es']
			]);
		$this->setUser('User_jp');
		self::assertSame('es', $factory->findLanguage('MyApp'));
	}

	public function testFindLanguageWithNotExistingRequestLanguageAndNotExistingStoredUserLanguageAndNotExistingDefault(): void {
		$factory = $this->getFactory(['languageExists'], true);
		$this->invokePrivate($factory->getDecorated(), 'requestLanguage', ['de']);
		$factory->getDecorated()->expects(self::exactly(3))
			->method('languageExists')
			->willReturnMap([
				['MyApp', 'de', false],
				['MyApp', 'jp', false],
				['MyApp', 'es', false],
			]);
		$this->config
			->expects($this->any())
			->method('getSystemValue')
			->willReturnMap([
				['installed', false, true],
				['force_language', false, false],
				['default_language', false, 'es']
			]);
		$this->config
			->expects(self::never())
			->method('setUserValue');

		$this->setUser('User_jp');
		self::assertSame('en', $factory->findLanguage('MyApp'));
	}

	public function testFindLanguageWithNotExistingRequestLanguageAndNotExistingStoredUserLanguageAndNotExistingDefaultAndNoAppInScope(): void {
		$factory = $this->getFactory(['languageExists'], true);
		$this->invokePrivate($factory->getDecorated(), 'requestLanguage', ['de']);
		$factory->getDecorated()->expects(self::exactly(3))
			->method('languageExists')
			->willReturnMap([
				['MyApp', 'de', false],
				['MyApp', 'jp', false],
				['MyApp', 'es', false],
			]);
		$this->config
			->expects($this->any())
			->method('getSystemValue')
			->willReturnMap([
				['installed', false, true],
				['force_language', false, false],
				['default_language', false, 'es']
			]);
		$this->config
				->expects(self::never())
				->method('setUserValue')
				->with('MyUserUid', 'core', 'lang', 'en');

		$this->setUser('User_en');
		self::assertSame('en', $factory->findLanguage('MyApp'));
	}

	public function testFindLanguageWithForcedLanguage(): void {
		$factory = $this->getFactory(['languageExists']);
		$this->config
			->expects($this->any())
			->method('getSystemValue')
			->willReturnMap([
				['installed', false, true],
				['force_language', false, 'de'],
				['default_language', false, 'es']
			]);

		$factory->getDecorated()->expects($this->once())
			->method('languageExists')
			->with('MyApp', 'de')
			->willReturn(true);

		self::assertSame('de', $factory->findLanguage('MyApp'));
	}

	/**
	 * @dataProvider dataFindAvailableLanguages
	 *
	 * @param string|null $app
	 */
	public function testFindAvailableLanguages($app): void {
		$factory = $this->getFactory(['findL10nDir']);
		$factory->getDecorated()->expects(self::once())
			->method('findL10nDir')
			->with($app)
			->willReturn(\OC::$SERVERROOT . '/tests/data/l10n/');

		self::assertEqualsCanonicalizing(['cs', 'de', 'en', 'ru'], $factory->findAvailableLanguages($app));
	}

	public function dataLanguageExists(): array {
		return [
			[null, 'en', [], true],
			[null, 'de', [], false],
			[null, 'de', ['ru'], false],
			[null, 'de', ['ru', 'de'], true],
			['files', 'en', [], true],
			['files', 'de', [], false],
			['files', 'de', ['ru'], false],
			['files', 'de', ['de', 'ru'], true],
		];
	}

	/**
	 * @dataProvider dataLanguageExists
	 *
	 * @param string|null $app
	 * @param string $lang
	 * @param string[] $availableLanguages
	 * @param string $expected
	 */
	public function testLanguageExists($app, $lang, array $availableLanguages, $expected): void {
		$factory = $this->getFactory(['findAvailableLanguages']);
		$factory->getDecorated()->expects(($lang === 'en') ? self::never() : self::once())
			->method('findAvailableLanguages')
			->with($app)
			->willReturn($availableLanguages);

		self::assertSame($expected, $factory->languageExists($app, $lang));
	}

	public function dataSetLanguageFromRequest(): array {
		return [
			// Language is available
			[null, 'de', ['de'], 'de'],
			[null, 'de,en', ['de'], 'de'],
			[null, 'de-DE,en-US;q=0.8,en;q=0.6', ['de'], 'de'],
			// Language is not available
			[null, 'de', ['ru'], new LanguageNotFoundException()],
			[null, 'de,en', ['ru', 'en'], 'en'],
			[null, 'de-DE,en-US;q=0.8,en;q=0.6', ['ru', 'en'], 'en'],

			// Language for app
			['files_pdfviewer', 'de', ['de'], 'de'],
			['files_pdfviewer', 'de,en', ['de'], 'de'],
			['files_pdfviewer', 'de-DE,en-US;q=0.8,en;q=0.6', ['de'], 'de'],
			// Language for app is not available
			['files_pdfviewer', 'de', ['ru'], new LanguageNotFoundException()],
			['files_pdfviewer', 'de,en', ['ru', 'en'], 'en'],
			['files_pdfviewer', 'de-DE,en-US;q=0.8,en;q=0.6', ['ru', 'en'], 'en'],
		];
	}

	/**
	 * @dataProvider dataSetLanguageFromRequest
	 *
	 * @param string|null $app
	 * @param string $header
	 * @param string[] $availableLanguages
	 * @param string $expected
	 */
	public function testGetLanguageFromRequest($app, $header, array $availableLanguages, $expected): void {
		$factory = $this->getFactory(['findAvailableLanguages', 'respectDefaultLanguage']);
		$factory->getDecorated()->expects(self::once())
			->method('findAvailableLanguages')
			->with($app)
			->willReturn($availableLanguages);

		$factory->getDecorated()->expects(self::any())
			->method('respectDefaultLanguage')->willReturnCallback(function ($app, $lang) {
				return $lang;
			});

		$this->request->expects(self::once())
			->method('getHeader')
			->with('ACCEPT_LANGUAGE')
			->willReturn($header);

		if ($expected instanceof LanguageNotFoundException) {
			$this->expectException(LanguageNotFoundException::class);
			self::invokePrivate($factory->getDecorated(), 'getLanguageFromRequest', [$app]);
		} else {
			self::assertSame($expected, self::invokePrivate($factory->getDecorated(), 'getLanguageFromRequest', [$app]), 'Asserting returned language');
		}
	}

	public function dataGetL10nFilesForApp(): array {
		return [
			[null, 'de', [\OC::$SERVERROOT . '/core/l10n/de.json']],
			['core', 'ru', [\OC::$SERVERROOT . '/core/l10n/ru.json']],
			['lib', 'ru', [\OC::$SERVERROOT . '/lib/l10n/ru.json']],
			['settings', 'de', [\OC::$SERVERROOT . '/apps/settings/l10n/de.json']],
			['files', 'de', [\OC::$SERVERROOT . '/apps/files/l10n/de.json']],
			['files', '_lang_never_exists_', []],
			['_app_never_exists_', 'de', [\OC::$SERVERROOT . '/core/l10n/de.json']],
		];
	}

	public function dataFindLanguage(): array {
		return [
			// Not logged in
			[false, [], 'en'],
			[false, ['fr'], 'fr'],
			[false, ['de', 'fr'], 'de'],
			[false, ['nl', 'de', 'fr'], 'de'],

			[true, [], 'en'],
			[true, ['fr'], 'fr'],
			[true, ['de', 'fr'], 'de'],
			[true, ['nl', 'de', 'fr'], 'nl'],
		];
	}

	/**
	 * @dataProvider dataFindLanguage
	 *
	 * @param bool $loggedIn
	 * @param array $availableLang
	 * @param string $expected
	 */
	public function testFindLanguage($loggedIn, $availableLang, $expected): void {
		$userLang = 'nl';
		$browserLang = 'de';
		$defaultLang = 'fr';

		$this->config->expects(self::any())
			->method('getSystemValue')
			->willReturnCallback(function ($var, $default) use ($defaultLang) {
				if ($var === 'installed') {
					return true;
				} elseif ($var === 'default_language') {
					return $defaultLang;
				} else {
					return $default;
				}
			});

		if ($loggedIn) {
			$user = $this->setUser('User_' . $userLang);
		} else {
			$this->userSession
				->expects(self::any())
				->method('getUser')
				->willReturn(null);
		}

		$this->request->expects(self::any())
			->method('getHeader')
			->with($this->equalTo('ACCEPT_LANGUAGE'))
			->willReturn($browserLang);

		$factory = $this->getFactory(['languageExists', 'findAvailableLanguages', 'respectDefaultLanguage']);
		$factory->getDecorated()->expects(self::any())
			->method('languageExists')
			->willReturnCallback(function ($app, $lang) use ($availableLang) {
				return in_array($lang, $availableLang);
			});
		$factory->getDecorated()->expects(self::any())
			->method('findAvailableLanguages')
			->willReturnCallback(function ($app) use ($availableLang) {
				return $availableLang;
			});
		$factory->getDecorated()->expects(self::any())
			->method('respectDefaultLanguage')->willReturnCallback(function ($app, $lang) {
				return $lang;
			});

		$lang = $factory->findLanguage();

		self::assertSame($expected, $lang);
	}

	public function testFindGenericLanguageByEnforcedLanguage(): void {
		$factory = $this->getFactory();
		$this->config
			->expects($this->any())
			->method('getSystemValue')
			->willReturnMap([
				['installed', false, true],
				['force_language', false, 'cz'],
				['default_language', false, 'es']
			]);

		$lang = $factory->findGenericLanguage();

		self::assertSame('cz', $lang);
	}

	public function testFindGenericLanguageByDefaultLanguage(): void {
		$factory = $this->getFactory(['languageExists']);
		$this->config->expects(self::exactly(2))
			->method('getSystemValue')
			->willReturnMap([
				['installed', false, true],
				['force_language', false, false],
				['default_language', false, 'cz',],
			]);
		$factory->getDecorated()->expects(self::once())
			->method('languageExists')
			->with(null, 'cz')
			->willReturn(true);

		$lang = $factory->findGenericLanguage();

		self::assertSame('cz', $lang);
	}

	public function testFindGenericLanguageByUserLanguage(): void {
		$factory = $this->getFactory();
		$this->config->expects(self::any())
			->method('getSystemValue')
			->willReturnMap([
				['installed', false, true],
				['force_language', false, false,],
				['default_language', false, false,],
			]);
		$this->setUser('User_cz');

		$lang = $factory->findGenericLanguage();

		self::assertSame('cz', $lang);
	}

	public function testFindGenericLanguageByRequestLanguage(): void {
		$factory = $this->getFactory(['findAvailableLanguages', 'languageExists']);
		$this->config->expects(self::any())
			->method('getSystemValue')
			->willReturnMap([
				['installed', false, true],
				['force_language', false, false,],
				['default_language', false, false,],
			]);
		$this->setUser('User_null');
		$this->request->expects(self::once())
			->method('getHeader')
			->with('ACCEPT_LANGUAGE')
			->willReturn('cz');
		$factory->getDecorated()->expects(self::once())
			->method('findAvailableLanguages')
			->with(null)
			->willReturn(['cz']);

		$lang = $factory->findGenericLanguage();

		self::assertSame('cz', $lang);
	}

	public function testFindGenericLanguageFallback(): void {
		$factory = $this->getFactory(['findAvailableLanguages', 'languageExists']);
		$this->config->expects(self::any())
			->method('getSystemValue')
			->willReturnMap([
				['installed', false, true],
				['force_language', false, false],
				['default_language', false, false],
			]);
		$this->setUser('User_null');
		$this->request->expects(self::once())
			->method('getHeader')
			->with('ACCEPT_LANGUAGE')
			->willReturn('');
		$factory->getDecorated()->expects(self::never())
			->method('findAvailableLanguages');
		$factory->getDecorated()->expects(self::never())
			->method('languageExists');

		$lang = $factory->findGenericLanguage();

		self::assertSame('en', $lang);
	}

	public function dataTestRespectDefaultLanguage(): array {
		return [
			['de', 'de_DE', true, 'de_DE'],
			['de', 'de', true, 'de'],
			['de', false, true, 'de'],
			['fr', 'de_DE', true, 'fr'],
		];
	}

	/**
	 * test if we respect default language if possible
	 *
	 * @dataProvider dataTestRespectDefaultLanguage
	 *
	 * @param string $lang
	 * @param string $defaultLanguage
	 * @param bool $langExists
	 * @param string $expected
	 */
	public function testRespectDefaultLanguage($lang, $defaultLanguage, $langExists, $expected): void {
		$factory = $this->getFactory(['languageExists']);
		$factory->getDecorated()->expects(self::any())
			->method('languageExists')->willReturn($langExists);
		$this->config->expects(self::any())
				->method('getSystemValue')
				->willReturnMap([
					['installed', false, true],
					['force_language', false, false],
					['default_language', false, $defaultLanguage],
				]);

		$result = $this->invokePrivate($factory->getDecorated(), 'respectDefaultLanguage', ['app', $lang]);
		self::assertSame($expected, $result);
	}

	public function languageIteratorRequestProvider():array {
		return [
			[ true, $this->createMock(IUser::class)],
			[ false, $this->createMock(IUser::class)],
			[ false, null]
		];
	}

	/**
	 * @dataProvider languageIteratorRequestProvider
	 */
	public function testGetLanguageIterator(bool $hasSession, IUser $iUserMock = null): void {
		$factory = $this->getFactory();

		if ($iUserMock === null) {
			if ($hasSession) {
				$this->setUser("User_de");
			} else {
				$this->userSession->expects(self::atLeastOnce())
					->method('getUser')
					->willReturn(null);
				$this->expectException(\RuntimeException::class);
			}
		}

		$iterator = $factory->getLanguageIterator($iUserMock);
		self::assertInstanceOf(ILanguageIterator::class, $iterator);
	}

}
