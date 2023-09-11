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

namespace OCA\NMCTheme\Test\L10N;

use OC\L10N\Factory;
use OC\L10N\LanguageNotFoundException;
use OCA\NMCTheme\L10N\FactoryDecorator;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\L10N\ILanguageIterator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase {
	/** @var IConfig|MockObject */
	protected $config;

	/** @var IRequest|MockObject */
	protected $request;

	/** @var IUserSession|MockObject */
	protected $userSession;

	/** @var ICacheFactory|MockObject */
	protected $cacheFactory;

	/** @var string */
	protected $serverRoot;

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(IConfig::class);
		$this->request = $this->createMock(IRequest::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->cacheFactory = $this->createMock(ICacheFactory::class);

		$this->serverRoot = \OC::$SERVERROOT;

		$this->config
		->expects(self::any())
			->method('getSystemValueBool')
			->willReturnMap([
				['installed', false, true],
			]);
	}

	/**
	 * Allows us to test private methods/properties
	 *
	 * @param $object
	 * @param $methodName
	 * @param array $parameters
	 * @return mixed
	 */
	protected static function invokePrivate($object, $methodName, array $parameters = []) {
		if (is_string($object)) {
			$className = $object;
		} else {
			$className = get_class($object);
		}
		$reflection = new \ReflectionClass($className);

		if ($reflection->hasMethod($methodName)) {
			$method = $reflection->getMethod($methodName);

			$method->setAccessible(true);

			return $method->invokeArgs($object, $parameters);
		} elseif ($reflection->hasProperty($methodName)) {
			$property = $reflection->getProperty($methodName);

			$property->setAccessible(true);

			if (!empty($parameters)) {
				if ($property->isStatic()) {
					$property->setValue(null, array_pop($parameters));
				} else {
					$property->setValue($object, array_pop($parameters));
				}
			}

			if (is_object($object)) {
				return $property->getValue($object);
			}

			return $property->getValue();
		}

		return false;
	}

	/**
	 * @param string[] $methods
	 * @param bool $mockRequestGetHeaderMethod
	 *
	 * @return Factory|MockObject
	 */
	protected function getFactory(array $methods = [], $mockRequestGetHeaderMethod = false) {
		if ($mockRequestGetHeaderMethod) {
			$this->request->expects(self::any())
				->method('getHeader')
				->willReturn('');
		}

		if (!empty($methods)) {
			return $this->getMockBuilder(Factory::class)
				->setConstructorArgs([
					$this->config,
					$this->request,
					$this->userSession,
					$this->cacheFactory,
					$this->serverRoot,
				])
				->setMethods($methods)
				->getMock();
		}

		return new FactoryDecorator($this->config,
			new Factory($this->config, $this->request, $this->userSession, $this->cacheFactory, $this->serverRoot));
	}

	public function dataFindAvailableLanguages(): array {
		return [
			[null],
			['files'],
		];
	}

	public function testFindLanguageWithExistingRequestLanguageAndNoApp(): void {
		$factory = $this->getFactory(['languageExists']);
		$this->invokePrivate($factory, 'requestLanguage', ['de']);
		$factory->expects(self::once())
			->method('languageExists')
			->with(null, 'de')
			->willReturn(true);

		self::assertSame('de', $factory->findLanguage());
	}

	public function testFindLanguageWithExistingRequestLanguageAndApp(): void {
		$factory = $this->getFactory(['languageExists']);
		$this->invokePrivate($factory, 'requestLanguage', ['de']);
		$factory->expects(self::once())
				->method('languageExists')
				->with('MyApp', 'de')
				->willReturn(true);

		self::assertSame('de', $factory->findLanguage('MyApp'));
	}

	public function testFindLanguageWithNotExistingRequestLanguageAndExistingStoredUserLanguage(): void {
		$factory = $this->getFactory(['languageExists']);
		$this->invokePrivate($factory, 'requestLanguage', ['de']);
		$factory->expects($this->exactly(2))
				->method('languageExists')
				->withConsecutive(
					['MyApp', 'de'],
					['MyApp', 'jp'],
				)
				->willReturnOnConsecutiveCalls(
					false,
					true,
				);
		$this->config
			->expects($this->exactly(1))
			->method('getSystemValue')
			->withConsecutive(
				['force_language', false],
			)->willReturnOnConsecutiveCalls(
				false,
			);
		$user = $this->getMockBuilder(IUser::class)
			->getMock();
		$user->expects(self::once())
			->method('getUID')
			->willReturn('MyUserUid');
		$this->userSession
			->expects(self::exactly(2))
			->method('getUser')
			->willReturn($user);
		$this->config
				->expects(self::once())
				->method('getUserValue')
				->with('MyUserUid', 'core', 'lang', null)
				->willReturn('jp');

		self::assertSame('jp', $factory->findLanguage('MyApp'));
	}

	public function testFindLanguageWithNotExistingRequestLanguageAndNotExistingStoredUserLanguage(): void {
		$factory = $this->getFactory(['languageExists'], true);
		$this->invokePrivate($factory, 'requestLanguage', ['de']);
		$factory->expects($this->exactly(3))
			->method('languageExists')
			->willReturnMap([
				['MyApp', 'de', false],
				['MyApp', 'jp', false],
				['MyApp', 'es', true],
			]);
		$this->config
			->expects($this->exactly(2))
			->method('getSystemValue')
			->willReturnMap([
				['force_language', false, false],
				['default_language', false, 'es']
			]);
		$user = $this->getMockBuilder(IUser::class)
			->getMock();
		$user->expects(self::once())
				->method('getUID')
				->willReturn('MyUserUid');
		$this->userSession
				->expects(self::exactly(2))
				->method('getUser')
				->willReturn($user);
		$this->config
				->expects(self::once())
				->method('getUserValue')
				->with('MyUserUid', 'core', 'lang', null)
				->willReturn('jp');

		self::assertSame('es', $factory->findLanguage('MyApp'));
	}

	public function testFindLanguageWithNotExistingRequestLanguageAndNotExistingStoredUserLanguageAndNotExistingDefault(): void {
		$factory = $this->getFactory(['languageExists'], true);
		$this->invokePrivate($factory, 'requestLanguage', ['de']);
		$factory->expects($this->exactly(3))
			->method('languageExists')
			->willReturnMap([
				['MyApp', 'de', false],
				['MyApp', 'jp', false],
				['MyApp', 'es', false],
			]);
		$this->config
			->expects($this->exactly(2))
			->method('getSystemValue')
			->willReturnMap([
				['force_language', false, false],
				['default_language', false, 'es']
			]);
		$user = $this->getMockBuilder(IUser::class)
			->getMock();
		$user->expects(self::once())
				->method('getUID')
				->willReturn('MyUserUid');
		$this->userSession
				->expects(self::exactly(2))
				->method('getUser')
				->willReturn($user);
		$this->config
				->expects(self::once())
				->method('getUserValue')
				->with('MyUserUid', 'core', 'lang', null)
				->willReturn('jp');
		$this->config
			->expects(self::never())
			->method('setUserValue');

		self::assertSame('en', $factory->findLanguage('MyApp'));
	}

	public function testFindLanguageWithNotExistingRequestLanguageAndNotExistingStoredUserLanguageAndNotExistingDefaultAndNoAppInScope(): void {
		$factory = $this->getFactory(['languageExists'], true);
		$this->invokePrivate($factory, 'requestLanguage', ['de']);
		$factory->expects($this->exactly(3))
			->method('languageExists')
			->willReturnMap([
				['MyApp', 'de', false],
				['MyApp', 'jp', false],
				['MyApp', 'es', false],
			]);
		$this->config
			->expects($this->exactly(2))
			->method('getSystemValue')
			->willReturnMap([
				['force_language', false, false],
				['default_language', false, 'es']
			]);
		$user = $this->getMockBuilder(IUser::class)
			->getMock();
		$user->expects(self::once())
				->method('getUID')
				->willReturn('MyUserUid');
		$this->userSession
				->expects(self::exactly(2))
				->method('getUser')
				->willReturn($user);
		$this->config
				->expects(self::once())
				->method('getUserValue')
				->with('MyUserUid', 'core', 'lang', null)
				->willReturn('jp');
		$this->config
				->expects(self::never())
				->method('setUserValue')
				->with('MyUserUid', 'core', 'lang', 'en');


		self::assertSame('en', $factory->findLanguage('MyApp'));
	}

	public function testFindLanguageWithForcedLanguage(): void {
		$factory = $this->getFactory(['languageExists']);
		$this->config
			->expects($this->once())
			->method('getSystemValue')
			->with('force_language', false)
			->willReturn('de');

		$factory->expects($this->once())
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
		$factory->expects(self::once())
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

	public function testFindAvailableLanguagesWithThemes(): void {
		$this->serverRoot .= '/tests/data';
		$app = 'files';

		$factory = $this->getFactory(['findL10nDir']);
		$factory->expects(self::once())
			->method('findL10nDir')
			->with($app)
			->willReturn($this->serverRoot . '/apps/files/l10n/');
		$this->config
			->expects(self::once())
			->method('getSystemValueString')
			->with('theme')
			->willReturn('abc');

		self::assertEqualsCanonicalizing(['en', 'zz'], $factory->findAvailableLanguages($app));
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
		$factory->expects(($lang === 'en') ? self::never() : self::once())
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
		$factory->expects(self::once())
			->method('findAvailableLanguages')
			->with($app)
			->willReturn($availableLanguages);

		$factory->expects(self::any())
			->method('respectDefaultLanguage')->willReturnCallback(function ($app, $lang) {
				return $lang;
			});

		$this->request->expects(self::once())
			->method('getHeader')
			->with('ACCEPT_LANGUAGE')
			->willReturn($header);

		if ($expected instanceof LanguageNotFoundException) {
			$this->expectException(LanguageNotFoundException::class);
			self::invokePrivate($factory, 'getLanguageFromRequest', [$app]);
		} else {
			self::assertSame($expected, self::invokePrivate($factory, 'getLanguageFromRequest', [$app]), 'Asserting returned language');
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
				if ($var === 'default_language') {
					return $defaultLang;
				} else {
					return $default;
				}
			});

		if ($loggedIn) {
			$user = $this->getMockBuilder(IUser::class)
				->getMock();
			$user->expects(self::any())
				->method('getUID')
				->willReturn('MyUserUid');
			$this->userSession
				->expects(self::any())
				->method('getUser')
				->willReturn($user);
			$this->config->expects(self::any())
				->method('getUserValue')
				->with('MyUserUid', 'core', 'lang', null)
				->willReturn($userLang);
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
		$factory->expects(self::any())
			->method('languageExists')
			->willReturnCallback(function ($app, $lang) use ($availableLang) {
				return in_array($lang, $availableLang);
			});
		$factory->expects(self::any())
			->method('findAvailableLanguages')
			->willReturnCallback(function ($app) use ($availableLang) {
				return $availableLang;
			});
		$factory->expects(self::any())
			->method('respectDefaultLanguage')->willReturnCallback(function ($app, $lang) {
				return $lang;
			});

		$lang = $factory->findLanguage();

		self::assertSame($expected, $lang);
	}

	public function testFindGenericLanguageByEnforcedLanguage(): void {
		$factory = $this->getFactory();
		$this->config->expects(self::once())
			->method('getSystemValue')
			->with('force_language', false)
			->willReturn('cz');

		$lang = $factory->findGenericLanguage();

		self::assertSame('cz', $lang);
	}

	public function testFindGenericLanguageByDefaultLanguage(): void {
		$factory = $this->getFactory(['languageExists']);
		$this->config->expects(self::exactly(2))
			->method('getSystemValue')
			->willReturnMap([
				['force_language', false, false,],
				['default_language', false, 'cz',],
			]);
		$factory->expects(self::once())
			->method('languageExists')
			->with(null, 'cz')
			->willReturn(true);

		$lang = $factory->findGenericLanguage();

		self::assertSame('cz', $lang);
	}

	public function testFindGenericLanguageByUserLanguage(): void {
		$factory = $this->getFactory();
		$this->config->expects(self::exactly(2))
			->method('getSystemValue')
			->willReturnMap([
				['force_language', false, false,],
				['default_language', false, false,],
			]);
		$user = $this->createMock(IUser::class);
		$this->userSession->expects(self::once())
			->method('getUser')
			->willReturn($user);
		$user->method('getUID')->willReturn('user123');
		$this->config->expects(self::once())
			->method('getUserValue')
			->with('user123', 'core', 'lang', null)
			->willReturn('cz');

		$lang = $factory->findGenericLanguage();

		self::assertSame('cz', $lang);
	}

	public function testFindGenericLanguageByRequestLanguage(): void {
		$factory = $this->getFactory(['findAvailableLanguages', 'languageExists']);
		$this->config->method('getSystemValue')
			->willReturnMap([
				['force_language', false, false,],
				['default_language', false, false,],
			]);
		$user = $this->createMock(IUser::class);
		$this->userSession->expects(self::once())
			->method('getUser')
			->willReturn($user);
		$user->method('getUID')->willReturn('user123');
		$this->config->expects(self::once())
			->method('getUserValue')
			->with('user123', 'core', 'lang', null)
			->willReturn(null);
		$this->request->expects(self::once())
			->method('getHeader')
			->with('ACCEPT_LANGUAGE')
			->willReturn('cz');
		$factory->expects(self::once())
			->method('findAvailableLanguages')
			->with(null)
			->willReturn(['cz']);

		$lang = $factory->findGenericLanguage();

		self::assertSame('cz', $lang);
	}

	public function testFindGenericLanguageFallback(): void {
		$factory = $this->getFactory(['findAvailableLanguages', 'languageExists']);
		$this->config->method('getSystemValue')
			->willReturnMap([
				['force_language', false, false,],
				['default_language', false, false,],
			]);
		$user = $this->createMock(IUser::class);
		$this->userSession->expects(self::once())
			->method('getUser')
			->willReturn($user);
		$user->method('getUID')->willReturn('user123');
		$this->config->expects(self::once())
			->method('getUserValue')
			->with('user123', 'core', 'lang', null)
			->willReturn(null);
		$this->request->expects(self::once())
			->method('getHeader')
			->with('ACCEPT_LANGUAGE')
			->willReturn('');
		$factory->expects(self::never())
			->method('findAvailableLanguages');
		$factory->expects(self::never())
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
		$factory->expects(self::any())
			->method('languageExists')->willReturn($langExists);
		$this->config->expects(self::any())
			->method('getSystemValue')->with('default_language', false)->willReturn($defaultLanguage);

		$result = $this->invokePrivate($factory, 'respectDefaultLanguage', ['app', $lang]);
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
			$matcher = $this->userSession->expects(self::once())
				->method('getUser');

			if ($hasSession) {
				$matcher->willReturn($this->createMock(IUser::class));
			} else {
				$this->expectException(\RuntimeException::class);
			}
		}

		$iterator = $factory->getLanguageIterator($iUserMock);
		self::assertInstanceOf(ILanguageIterator::class, $iterator);
	}

}
