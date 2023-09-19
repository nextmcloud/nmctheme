<?php

declare(strict_types=1);

namespace OCA\NMCTheme\Tests\L10N;

use OC\L10N\Factory;
use OCA\NMCTheme\L10N\FactoryDecorator;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FactoryTestCase extends TestCase {
	/** @var IConfig|MockObject */
	protected $config;

	/** @var IRequest|MockObject */
	protected $request;

	/** @var IUserSession|MockObject */
	protected $userSession;

	/** @var ICacheFactory|MockObject */
	protected $cacheFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->config = $this->createMock(IConfig::class);
		$this->request = $this->createMock(IRequest::class);
		$this->cacheFactory = $this->createMock(ICacheFactory::class);
		$this->userSession = $this->createMock(IUserSession::class);

		$this->config
			->expects(self::any())
			->method('getSystemValueBool')
			->willReturnMap([
				['installed', false, true],
			]);
	}

	protected function setUser(string $id) {
		$this->user = $this->createMock(IUser::class);
		$this->userSession->expects(self::atLeastOnce())
				->method('getUser')
				->willReturn($this->user);
		$echo = function ($value) { return $value; };
		$this->config->expects(self::atLeastOnce())
					->method('getUserValue')
					->willReturnMap([
						['User_de', 'core', 'lang', null, 'de'],
						['User_jp', 'core', 'lang', null, 'jp'],
						['User_en', 'core', 'lang', null, 'en'],
						['User_cz', 'core', 'lang', null, 'cz'],
						['User_fr', 'core', 'lang', null, 'fr'],
						['User_nl', 'core', 'lang', null, 'nl'],
						['User_null', 'core', 'lang', null, null],
					]);
		$this->user->expects(self::any())
				->method('getUID')
				->willReturn($id);
		return $this->user;
	}

	protected function numberOfConstructParams($class_name) {
		$class_reflection = new \ReflectionClass($class_name);
		$constructor = $class_reflection->getConstructor();
		if ($constructor === null) {
			return 0;
		} else {
			return $constructor->getNumberOfParameters();
		}
	}
	

	/**
	 * @param string[] $methods
	 * @param bool $mockRequestGetHeaderMethod
	 *
	 * @return Factory|MockObject
	 */
	protected function getFactory(array $methods = [], $mockRequestGetHeaderMethod = false) : FactoryDecorator {
		if ($mockRequestGetHeaderMethod) {
			$this->request->expects(self::any())
				->method('getHeader')
				->willReturn('');
		}

		if (!empty($methods)) {
			if ($this->numberOfConstructParams('OC\L10N\Factory') == 4) {
				// V25 constructor
				$decorated = $this->getMockBuilder(Factory::class)
									->setConstructorArgs([
										$this->config,
										$this->request,
										$this->userSession,
										\OC::$SERVERROOT,
									])
									->setMethods($methods)
									->getMock();

			} else {
				// post V25
				$cacheFactory = $this->createMock(ICacheFactory::class);
				$decorated = $this->getMockBuilder(Factory::class)
				->setConstructorArgs([
					$this->config,
					$this->request,
					$this->userSession,
					$decorated,
					\OC::$SERVERROOT,
				])
				->setMethods($methods)
				->getMock();
			}
		} else {
			if ($this->numberOfConstructParams('OC\L10N\Factory') == 4) {
				// V25 constructor
				$decorated = new Factory($this->config, $this->request, $this->userSession, \OC::$SERVERROOT);
			} else {
				// post V25
				$cacheFactory = $this->createMock(ICacheFactory::class);
				$decorated = new Factory($this->config, $this->request, $this->userSession, $this->cacheFactory, \OC::$SERVERROOT);
			}
	
		}
		
		$this->factory = new FactoryDecorator($this->config, $decorated);
		return $this->factory;
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

	protected function addRequestLocales(array $locales) {
		$this->request->expects(self::any())
			->method('getHeader')
			->with('ACCEPT_LANGUAGE')
			->willReturn(implode('; ', $locales));

		
	}
}
