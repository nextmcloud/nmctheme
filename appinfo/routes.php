<?php
/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author B. Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
return [
	'routes' => [
		[
			'name' => 'L10NAppend#getTranslations',
			'url' => '/lang/{app}/l10n/{lang}.js',
			'verb' => 'GET',
		],
		[
			'name' => 'MimeIcon#getMimeTypeList',
			'url' => '/mime/js/mimetypelist.js',
			'verb' => 'GET',
		],
		[
			'name' => 'MimeIcon#getMimeIcon',
			'url' => '/mime/img/{iconname}.svg',
			'verb' => 'GET',
		],
		[
			'name' => 'TouchIcon#getTouchIcon',
			'url' => '/touchicon/{app}',
			'verb' => 'GET',
			'defaults' => ['app' => 'nmctheme'],
		],
	]
];
