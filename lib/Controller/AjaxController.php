<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author M. Mura <mauro-efisio.mura@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\NMCTheme\Controller;

use OCA\NMCTheme\Service\NMCFilesService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Files\NotFoundException;
use OCP\IRequest;

class AjaxController extends Controller {

	/** @var NMCFilesService */
	private $filesService;

	public function __construct(string $appName, IRequest $request, NMCFilesService $filesService) {
		parent::__construct($appName, $request);
		$this->filesService = $filesService;
	}

	/**
	 * @NoAdminRequired
	 */
	public function getStorageStats(string $dir = '/'): JSONResponse {
		$storageInfo = $this->filesService->buildFileStorageStatistics($dir ?: '/');
		$l = \OC::$server->getL10N('files');

		$maxUploadFileSize = \OCP\Util::maxUploadFilesize($dir, $storageInfo['free']);
		$maxHumanFileSize = \OCP\Util::humanFileSize($maxUploadFileSize);
		$maxHumanFileSize = $l->t('Upload (max. %s)', [$maxHumanFileSize]);

		$storageInfo['uploadMaxFilesize'] = $maxUploadFileSize;
		$storageInfo['maxHumanFilesize'] = $maxHumanFileSize;
		$storageInfo['usedSpacePercent'] = $storageInfo['relative'];

		try {
			return new JSONResponse([
				'status' => 'success',
				'data' => $storageInfo,
			]);
		} catch (NotFoundException $e) {
			return new JSONResponse([
				'status' => 'error',
				'data' => [
					'message' => 'Folder not found'
				],
			]);
		}
	}
}
