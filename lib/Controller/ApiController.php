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
use OCP\IRequest;

class ApiController extends Controller {

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
		return new JSONResponse(['message' => 'ok', 'data' => $storageInfo]);
	}
}
