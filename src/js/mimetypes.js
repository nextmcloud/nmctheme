import { generateUrl } from '@nextcloud/router'

/**
 * Derive icon name from mapped mime type.
 *
 * @param {string} mimetype Identifier of the mapped mime type
 * @return {string} Icon name
 */
function _iconName(mimetype) {
	const dirMappings = {
		dir: 'folder',
		'dir-encrypted': 'folder-encrypted',
		'dir-shared': 'folder-shared',
		'dir-public': 'folder-public',
		'dir-external': 'folder-external',
		'dir-external-root': 'folder-external',
	}

	return mimetype in dirMappings ? dirMappings[mimetype] : mimetype.replace('/', '-')
}

/**
 * Add a cachebuster id to any URL.
 *
 * @param {string} path Original URL path
 * @return {string} URL including cache buster
 */
function cacheBusterUrl(path) {
	let url = generateUrl(path)

	if (window.OCA?.Theming?.cacheBuster !== undefined) {
		url += '?v=' + window.OCA.Theming.cacheBuster
	}

	return url
}

/**
 * Find a matching icon in the MagentaCLOUD theme.
 *
 * @param {string} mimetype Mime type
 * @return {string} Icon URL
 */
function getThemeIconUrl(mimetype) {
	if (mimetype === undefined || mimetype === null) {
		return undefined
	}

	// Resolve aliases supplied by Nextcloud.
	while (
		window.OC?.MimeTypeList?.aliases !== undefined
		&& mimetype in window.OC.MimeTypeList.aliases
	) {
		mimetype = window.OC.MimeTypeList.aliases[mimetype]
	}

	// Route all mime type icons through nmctheme.
	const path = `/apps/nmctheme/mime/img/${_iconName(mimetype)}.svg`

	return cacheBusterUrl(path)
}

/**
 * Override the legacy OC.MimeType API.
 *
 * In current Nextcloud versions OC.MimeType is backed by an ES module
 * namespace. Its exported properties are read-only getters, so
 *
 *     OC.MimeType.getIconUrl = ...
 *
 * is no longer possible.
 *
 * Instead, replace the legacy OC.MimeType reference with a mutable wrapper
 * containing the original exports and our customized getIconUrl function.
 */
function installMimeTypeOverride() {
	if (window.OC?.MimeType === undefined) {
		return
	}

	const originalMimeType = window.OC.MimeType

	const customMimeType = {
		...originalMimeType,
		getIconUrl: getThemeIconUrl,
	}

	const descriptor = Object.getOwnPropertyDescriptor(window.OC, 'MimeType')

	// Normal case: OC.MimeType itself can still be replaced.
	if (
		descriptor === undefined
		|| descriptor.writable === true
		|| descriptor.set !== undefined
	) {
		window.OC.MimeType = customMimeType
		return
	}

	// Fallback if OC.MimeType is non-writable but configurable.
	if (descriptor.configurable === true) {
		Object.defineProperty(window.OC, 'MimeType', {
			...descriptor,
			value: customMimeType,
			get: undefined,
			set: undefined,
		})
		return
	}

	console.warn(
		'[nmctheme] Unable to override OC.MimeType because the property is read-only',
	)
}

if (document.readyState === 'loading') {
	window.addEventListener(
		'DOMContentLoaded',
		installMimeTypeOverride,
		{ once: true },
	)
} else {
	installMimeTypeOverride()
}
