<?php

namespace OCA\NMCTheme\Controller;

use OC\IntegrityCheck\Helpers\FileAccessHelper;
use OCA\NMCTheme\AppInfo\Application;
use OCP\App\IAppManager;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\Response;
use OCP\IRequest;

class TouchIconController extends Controller {
	/** @var FileAccessHelper */
	private $fileAccessHelper;

	/** @var IAppManager */
	private $appManager;

	public function __construct(
		IRequest $request,
		FileAccessHelper $fileAccessHelper,
		IAppManager $appManager,
	) {
		parent::__construct(Application::APP_ID, $request);
		$this->fileAccessHelper = $fileAccessHelper;
		$this->appManager = $appManager;
	}

	/**
	 * Return a 512x512 icon for touch devices
	 *
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * @param string $app ID of the app
	 * @return DataDisplayResponse<Http::STATUS_OK, array{Content-Type: 'image/png'}>|FileDisplayResponse<Http::STATUS_OK, array{Content-Type: 'image/x-icon'|'image/png'}>|NotFoundResponse<Http::STATUS_NOT_FOUND, array{}>
	 *
	 * 200: Touch icon returned
	 * 404: Touch icon not found
	 */
	public function getTouchIcon(string $app = 'core'): Response {

		$touchIconPath = $this->appManager->getAppPath(Application::APP_ID) . "/img/";
		$iconFile = $this->fileAccessHelper->file_get_contents($touchIconPath . 'favicon-touch.png');
		$response = new DataDisplayResponse($iconFile, Http::STATUS_OK, ['Content-Type' => 'image/png']);
		$response->cacheFor(86400);

		return $response;
	}
}
