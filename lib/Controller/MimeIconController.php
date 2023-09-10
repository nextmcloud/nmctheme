<?php
/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author B. Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\NMCTheme\Controller;

use OCP\App\IAppManager;
use OCA\NMCTheme\AppInfo\Application;
use OCP\AppFramework\Controller;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\Files\IMimeTypeDetector;
use OCP\IRequest;

/**
 * Class MimeIconController
 * Delivers icons for filelist depending on mimetype.
 * If no icon is overridden in nmctheme, the server icon is delivered.
 * If none is found, and unknown icon is delivered
 */
class MimeIconController extends Controller {
    const FOLDER_ICON_FILES = [
        "folder", 
        "folder-encrypted",
        "folder-shared",
        "folder-public",
        "folder-external",
        "folder-external"];


	private IMimeTypeDetector $mimetypes;
    private IAppManager $appManager;

	/**
	 * AppendController for translation constructor.
	 *
	 * @param IRequest $request
	 * @param IMimeTypeDetector mimetype metadata (standard + custom merged)
     * @param IAppAManager the application manager to get the app path
	 */
	public function __construct(
        IRequest $request,
		IMimeTypeDetector $mimetypes,
        IAppManager $appManager) {
		parent::__construct(Application::APP_ID, $request);
		$this->mimetypes = $mimetypes;
        $this->appManager = $appManager;
	}

	/**
	 * @NoCSRFRequired
	 * @PublicPage
	 * @NoSameSiteCookieRequired
	 * @NoTwoFactorRequired
	 *
	 * The UNTANGLED mimetype alias list.
	 * Opposite to the standard mimetype list, the server
	 * resolves references to other aliases until non-alias value is found.
	 *
	 * To avoid endless recursions, the indirections are limited to a
	 * depth of 10.
	 */
	public function getMimeTypeList() {
		try {
			$aliases = $this->mimetypes->getAllAliases();
		} catch(\Throwable $eNoSuchMethod) {
			return 'OC.MimeTypeList={ aliases: {}, files: [], themes: [] }';
		}

		$resolvedAliases = [];
		foreach ($aliases as $key => $value) {
            // filter out comments
            if (str_starts_with($key, '_')) {
                continue;
            }

            // resolve values until depth 10
            if (array_key_exists($value, $aliases)) {
                for ($depth = 0; $depth < 10 && array_key_exists($value, $aliases); $depth++) {
                    $value = $aliases[$value];
                }    
            }
            // we build a new array so that all values resolve properly
			$resolvedAliases[$key] = $value;
		}
        ksort($resolvedAliases);

        $mimefiles = array_map(function($name) { return '"' . str_replace('/', '-', $name) . '"'; }, 
            array_unique(array_merge(self::FOLDER_ICON_FILES, array_values($resolvedAliases))));
        sort($mimefiles);

		$mimetypes = 'OC.MimeTypeList={ aliases: ';
		$mimetypes .= json_encode($resolvedAliases, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) . ',';
		$mimetypes .= 'files: [' . implode(",\n", $mimefiles) . '],';
		$mimetypes .= 'themes: [] }';

		$response = new DataDisplayResponse($mimetypes, Http::STATUS_OK,
			['Content-Type' => 'application/javascript;charset=utf-8']);
		$response->cacheFor(86400);
		return $response;

	}

	/**
	 * @NoCSRFRequired
	 * @PublicPage
	 * @NoSameSiteCookieRequired
	 * @NoTwoFactorRequired
	 *
	 * @param string $iconFilename  the computed icon filename for mimetype after alias mapping
	 * @return FileResponse<Http::STATUS_OK, array{Content-Type: 'application/javascript'}>|
	 */
	public function getMimeIcon(string $iconname) {
		$themeImgPath = $this->appManager->getAppPath(Application::APP_ID) . "/img/filetypes/";
		$iconFile =
			file_get_contents($themeImgPath . $iconname . '.svg');
		if ($iconFile === false) {
			$iconFile = file_get_contents(\OC::$SERVERROOT . '/core/img/filetypes/' . $iconname . '.svg');
		}
		if ($iconFile === false) {
			$iconFile = file_get_contents($themeImgPath . 'unknown-file.svg');
		}

		$response = new DataDisplayResponse($iconFile, Http::STATUS_OK,
			['Content-Type' => 'image/svg+xml']);
		$response->cacheFor(86400);
		return $response;
	}
}
