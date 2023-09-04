<?php
/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author Bernd Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * Deviates I10N load requests to nmctheme for translation
 * overrides directly after registering the original translations
 *
 */

namespace OCA\NMCTheme\L10N;

use OC\Template\JSCombiner;
use OC\Template\JSResourceLocator;
use OCP\App\IAppManager;
use Psr\Log\LoggerInterface;

class L10NResourceLocatorExtension extends JSResourceLocator {
	private const LANG_THEME_URL = '/index.php/apps/nmctheme/lang';

	public function __construct(LoggerInterface $logger, JSCombiner $JSCombiner, IAppManager $appManager) {
		parent::__construct($logger, $JSCombiner, $appManager);
	}

	/**
	 * Deviate all language requests to the nmctheme language extension service
	 */
	public function doFind($script) {
		if (str_contains($script, '/l10n/')) {
			// only add script if corresponding app has a language json
			$pos = strpos($script, '/');
			$app = substr($script, 0, $pos);
			$file = substr($script, $pos, strlen($script));
			if ($app === 'core' || $app === '') {
				$this->append(\OC::$SERVERROOT, $app . $file .".js", \OC::$WEBROOT . self::LANG_THEME_URL, false);
				return;
			}

			$appPath = $this->appManager->getAppPath($app);
			if (is_file($appPath . $file . '.json')) {
				$this->append(\OC::$SERVERROOT, $app . $file . '.js', \OC::$WEBROOT . self::LANG_THEME_URL, false);
				return;
			}
		}

		parent::doFind($script);
	}

}
