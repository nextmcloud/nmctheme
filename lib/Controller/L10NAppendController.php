<?php
/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author B. Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\NMCTheme\Controller;

use OCA\NMCTheme\AppInfo\Application;
use OCA\NMCTheme\L10N\FactoryDecorator;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\IRequest;

/**
 * Class L10NAppendController
 * Overrides app translations by theme translations
 * for JAvascript. It assures that the override takes
 * place directly after registering translations -
 * before any other actions
 */
class L10NAppendController extends Controller {
	/** @var FactoryDecorator */
	protected $factory;

	/**
	 * AppendController for translation constructor.
	 *
	 * @param IRequest $request
	 * @param FactoryDecorator $factory
	 */
	public function __construct(
		IRequest $request,
		FactoryDecorator $factory
	) {
		parent::__construct(Application::APP_ID, $request);

		$this->factory = $factory;
	}


	/**
	 * @NoCSRFRequired
	 * @PublicPage
	 * @NoSameSiteCookieRequired
	 * @NoTwoFactorRequired
	 *
	 * @param string $app location of the related app
	 * @param string $lang language of the translation
	 * @return DataDisplayResponse<Http::STATUS_OK, array{Content-Type: 'application/javascript'}>|
	 */
	public function getTranslations(string $app, string $lang) {
		if ($app !== 'core' && $app !== '') {
			$app = basename($app);
		}

		$translations = $this->factory->getTranslationsForApp($app, $lang);
		$overrides = $this->factory->getOverrides($lang);
		if (array_key_exists($app, $overrides)) {
			$translations = array_merge($translations, $overrides[$app]);
		}
		if (!empty($translations)) {
			$registrations = 'OC.L10N.register("'. $app . '", ';
			$registrations .= json_encode($translations, JSON_PRETTY_PRINT);
			$registrations .= ",\n\"nplurals=2; plural=(n != 1);\");";
		} else {
			$registrations = "";
		}
		$response = new DataDisplayResponse($registrations, Http::STATUS_OK, ['Content-Type' => 'application/javascript;charset=utf-8']);
		$response->cacheFor(86400);
		return $response;
	}
}
