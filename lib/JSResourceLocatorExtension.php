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

namespace OCA\NMCTheme;

use OC\Template\JSCombiner;
use OC\Template\JSResourceLocator;
use OCP\App\IAppManager;
use Psr\Log\LoggerInterface;

class JSResourceLocatorExtension extends JSResourceLocator {
	private const LANG_THEME_URL = '/index.php/apps/nmctheme/lang';

	private $ownAppManager;

	/**
	 * The constructor changed between V25 and V27 multiple times,
	 * so we try to handle different backports in this constructor.
	 *
	 */
	public function __construct(LoggerInterface $logger, JSCombiner $jsCombiner, IAppManager $appManager) {
		$this->ownAppManager = $appManager;

		// later
		try {
			parent::__construct($logger, $jsCombiner, $appManager);
			return;
		} catch (\Throwable $eWrongConstruct1) {
			// ignore the exception, try another constructor
		}

		// V26
		try {
			parent::__construct($logger, $jsCombiner);
			return;
		} catch (\Throwable $eWrongConstruct2) {
			// ignore the exception, try another constructor
		}

		// V25
		parent::__construct($logger,
			'',
			[ \OC::$SERVERROOT => \OC::$WEBROOT ],
			[ \OC::$SERVERROOT => \OC::$WEBROOT ],
			$jsCombiner);
		return;
	}

	/**
	 * Deviate all language requests to the nmctheme language extension service
	 */
	public function doFind($script) {
        // Translation extensions
		if (str_contains($script, '/l10n/')) {
			// only add script if corresponding app has a language json
			$pos = strpos($script, '/');
			$app = substr($script, 0, $pos);
			$file = substr($script, $pos, strlen($script));
			if ($app === 'core' || $app === '') {
				$this->append(\OC::$SERVERROOT, $app . $file .".js", \OC::$WEBROOT . self::LANG_THEME_URL, false);
				return;
			}

			$appPath = $this->ownAppManager->getAppPath($app);
			if (is_file($appPath . $file . '.json')) {
				$this->append(\OC::$SERVERROOT, $app . $file . '.js', \OC::$WEBROOT . self::LANG_THEME_URL, false);
				return;
			}
		}

		parent::doFind($script);
	}

}
