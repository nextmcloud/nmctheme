/* eslint-disable no-console */

const iconMapping = {
	'dir-public': 'filetypes/folder-public',
	'dir-shared': 'filetypes/folder-shared',
	'dir-encrypted': 'filetypes/folder-encrypted',
	dir: 'filetypes/folder-regular',
	'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'filetypes/x-office-presentation',
	'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'filetypes/text',
	'text/markdown': 'filetypes/text',
}

window.addEventListener('DOMContentLoaded', function() {
	const initial = OC.MimeType.getIconUrl
	OC.MimeType.getIconUrl = (mimetype) => {
		if (iconMapping[mimetype]) return `/customapps/nmctheme/img/${iconMapping[mimetype]}.svg`
		return initial(mimetype)
	}
})
