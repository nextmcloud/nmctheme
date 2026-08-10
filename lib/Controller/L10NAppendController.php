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
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\IRequest;

/**
 * Overrides app translations by theme translations
 * for JavaScript.
 */
class L10NAppendController extends Controller {

	protected FactoryDecorator $factory;

	public function __construct(
		IRequest $request,
		FactoryDecorator $factory
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->factory = $factory;
	}

	/**
	 * @NoSameSiteCookieRequired
	 * @NoTwoFactorRequired
	 *
	 * @param string $app
	 * @param string $lang
	 */
	#[NoCSRFRequired]
	#[PublicPage]
	public function getTranslations(string $app, string $lang): DataDisplayResponse {
		if ($app !== 'core' && $app !== '') {
			$app = basename($app);
		}

		$translations = $this->factory->getTranslationsForApp($app, $lang);
		$overrides = $this->factory->getOverrides($lang);

		if (array_key_exists($app, $overrides)) {
			$translations = array_merge($translations, $overrides[$app]);
		}

		if (!empty($translations)) {
			$registrations = 'OC.L10N.register("' . $app . '", ';
			$registrations .= json_encode($translations, JSON_PRETTY_PRINT);
			$registrations .= ",\n\"nplurals=2; plural=(n != 1);\");";
		} else {
			$registrations = '';
		}

		$response = new DataDisplayResponse(
			$registrations,
			Http::STATUS_OK,
			['Content-Type' => 'application/javascript;charset=utf-8']
		);

		$response->cacheFor(86400);

		return $response;
	}
}
