<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author Bernd Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * Although the code for this MagentaCLOUD web application customisation for Nextcloud is free and available under the AGPL3
 * license, Deutsche Telekom (including T-Systems) fully reserves all rights to the Telekom brand. To prevent users
 * from getting confused about the source of a digital product or experience, there are stringent restrictions on using
 * the Telekom brand and design - especially the fonts referenced herein, even when built into code that we provide.
 * For any customization other than explicitly for Telekom or T-Systems, you must replace the Deutsche Telekom and
 * T-Systems brand elements contained in the provided nmctheme.
 */
namespace OCA\NMCTheme\Themes;

use OCA\Theming\ITheme;
use OCP\IL10N;
use OCP\IURLGenerator;

class TeleNeoWebFont implements ITheme {
	public IURLGenerator $urlGenerator;
	public IL10N $l;

	public function __construct(IURLGenerator $urlGenerator,
								IL10N $l) {
		$this->urlGenerator = $urlGenerator;
		$this->l = $l;
	}

	public function getId(): string {
		return 'teleneoweb';
	}

	public function getType(): int {
		return ITheme::TYPE_FONT;
	}

	public function getTitle(): string {
		return $this->l->t('Telekom TeleNeoWeb brand font');
	}

	public function getEnableLabel(): string {
		return $this->l->t('Enable TeleNeoWeb font');
	}

	public function getDescription(): string {
		return $this->l->t('Telekom TeleNeoWeb is a branding restricted font of Deutsche Telekom for their products.');
	}

	public function getMediaQuery(): string {
		return '';
	}

	public function getCSSVariables(): array {
		// the font is referenced in the theming variables
		return [];
	}

	public function getCustomCss(): string {
		// TODO: may reduce the delivered fonts to only those actually used in the design
		$fontPrefixUrl = $this->urlGenerator->linkTo('nmctheme', 'fonts/TeleNeoWeb');

		return "
          @font-face {
            font-family: 'TeleNeoWeb';
            font-weight: 900;
            font-style: normal;
            src: url('${fontPrefixUrl}-Ultra.eot');
            src: url('${fontPrefixUrl}-Ultra.woff') format('woff'),
                 url('${fontPrefixUrl}-Ultra.woff2') format('woff2');
          }
          @font-face {
            font-family: 'TeleNeoWeb';
            font-weight: 900;
            font-style: italic;
            src: url('${fontPrefixUrl}-UltraItalic.eot');
            src: url('${fontPrefixUrl}-UltraItalic.woff') format('woff'),
                 url('${fontPrefixUrl}-UltraItalic.woff2') format('woff2');
          }
          @font-face {
            font-family: 'TeleNeoWeb';
            font-weight: 800;
            font-style: normal;
            src: url('${fontPrefixUrl}-ExtraBold.eot');
            src: url('${fontPrefixUrl}-ExtraBold.woff') format('woff'),
                 url('${fontPrefixUrl}-ExtraBold.woff2') format('woff2');
          }
          @font-face {
            font-family: 'TeleNeoWeb';
            font-weight: 800;
            font-style: italic;
            src: url('${fontPrefixUrl}-ExtraBoldItalic.eot');
            src: url('${fontPrefixUrl}-ExtraBoldItalic.woff') format('woff'),
                 url('${fontPrefixUrl}-ExtraBoldItalic.woff2') format('woff2');
          }
          @font-face {
            font-family: 'TeleNeoWeb';
            font-weight: 700;
            font-style: normal;
            src: url('${fontPrefixUrl}-Bold.eot');
            src: url('${fontPrefixUrl}-Bold.woff') format('woff'),
                 url('${fontPrefixUrl}-Bold.woff2') format('woff2');
          }
          @font-face {
            font-family: 'TeleNeoWeb';
            font-weight: 700;
            font-style: italic;
            src: url('${fontPrefixUrl}-BoldItalic.eot');
            src: url('${fontPrefixUrl}-BoldItalic.woff') format('woff'),
                 url('${fontPrefixUrl}-BoldItalic.woff2') format('woff2');
          }
          @font-face {
            font-family: 'TeleNeoWeb';
            font-weight: 500;
            font-style: normal;
            src: url('${fontPrefixUrl}-Medium.eot');
            src: url('${fontPrefixUrl}-Medium.woff') format('woff'),
                 url('${fontPrefixUrl}-Medium.woff2') format('woff2');
          }
          @font-face {
            font-family: 'TeleNeoWeb';
            font-weight: 500;
            font-style: italic;
            src: url('${fontPrefixUrl}-MediumItalic.eot');
            src: url('${fontPrefixUrl}-MediumItalic.woff') format('woff'),
                 url('${fontPrefixUrl}-MediumItalic.woff2') format('woff2');
          }
          @font-face {
            font-family: 'TeleNeoWeb';
            font-weight: 400;
            font-style: normal;
            src: url('${fontPrefixUrl}-Regular.eot');
            src: url('${fontPrefixUrl}-Regular.woff') format('woff'),
              url('${fontPrefixUrl}-Regular.woff2') format('woff2');
          }
          @font-face {
            font-family: 'TeleNeoWeb';
            font-weight: 400;
            font-style: italic;
            src: url('${fontPrefixUrl}-RegularItalic.eot');
            src: url('${fontPrefixUrl}-RegularItalic.woff') format('woff'),
              url('${fontPrefixUrl}-RegularItalic.woff2') format('woff2');
          }
          @font-face {
            font-family: 'TeleNeoWeb';
            font-weight: 200;
            font-style: normal;
            src: url('${fontPrefixUrl}-Thin.eot');
            src: url('${fontPrefixUrl}-Thin.woff') format('woff'),
              url('${fontPrefixUrl}-Thin.woff2') format('woff2');
          }
          @font-face {
            font-family: 'TeleNeoWeb';
            font-weight: 200;
            font-style: italic;
            src: url('${fontPrefixUrl}-ThinItalic.eot');
            src: url('${fontPrefixUrl}-ThinItalic.woff') format('woff'),
              url('${fontPrefixUrl}-ThinItalic.woff2') format('woff2');
          }";
	}
}
