import path from 'path'
// we follow the implementation of Nextcloud in trouble with eslint, so ignore warning
import * as sass from 'sass' // eslint-disable-line n/no-unpublished-import
import fs from 'fs'
import { fileURLToPath } from 'url'

// TODO ----- this part is copied from nextcloud/server/core/src/icons.js
//      and should be taken from there as soon as it is accepted
const colors = {
	dark: '000',
	white: 'fff',
	// gold but for backwards compatibility called yellow
	yellow: 'fecb00',
	red: 'e9322d',
	orange: 'eca700',
	green: '46ba61',
	grey: '969696',
	magenta: 'e20074',
	info: '2238df',
	success: '00b367',
	danger: 'e82010',
	warning: 'f97012',
}

const colorSvg = function(svg = '', color = '000') {
	if (!color.match(/^[0-9a-f]{3,6}$/i)) {
		// Prevent not-sane colors from being written into the SVG
		console.warn(color, 'does not match the required format')
		color = '000'
	}

	// add fill (fill is not present on black elements)
	const fillRe = /<((circle|rect|path|polygon)((?!fill)[a-z0-9 =".\-#():;,])+)\/>/gmi
	svg = svg.replace(fillRe, '<$1 fill="#' + color + '"/>')

	// replace any fill or stroke colors
	svg = svg.replace(/stroke="#([a-z0-9]{3,6})"/gmi, 'stroke="#' + color + '"')
	svg = svg.replace(/fill="#([a-z0-9]{3,6})"/gmi, 'fill="#' + color + '"')
	svg = svg.replace(/fill:#([a-z0-9]{3,6})/gmi, 'fill:#' + color)

	return svg
}

const generateVariablesAliases = function(variables, invert = false) {
	let css = ''
	Object.keys(variables).forEach(variable => {
		if (variable.indexOf('original-') !== -1) {
			let finalVariable = variable.replace('original-', '')
			if (invert) {
				finalVariable = finalVariable.replace('white', 'tempwhite')
					.replace('dark', 'white')
					.replace('tempwhite', 'dark')
			}
			css += `${finalVariable}: var(${variable});`
		}
	})
	return css
}
// ----- this end copy from nextcloud/server/core/src/icons.js

const __dirname = path.dirname(fileURLToPath(import.meta.url))

const variables = {}

const icons = {
	add: path.join(__dirname, '../img', 'actions', 'add.svg'),
	address: path.join(__dirname, '../img', 'actions', 'address.svg'),
	'arrow-left': path.join(__dirname, '../img', 'actions', 'arrow-left.svg'),
	download: path.join(__dirname, '../img', 'actions', 'download.svg'),
	logout: path.join(__dirname, '../img', 'actions', 'logout.svg'),
	menu: path.join(__dirname, '../img', 'actions', 'menu.svg'),
	search: path.join(__dirname, '../img', 'actions', 'search.svg'),
	share: path.join(__dirname, '../img', 'actions', 'share.svg'),
	shared: path.join(__dirname, '../img', 'actions', 'share.svg'),
	public: path.join(__dirname, '../img', 'actions', 'share.svg'),
	star: path.join(__dirname, '../img', 'actions', 'star.svg'),
	starred: path.join(__dirname, '../img', 'actions', 'starred.svg'),
	upload: path.join(__dirname, '../img', 'actions', 'upload.svg'),
	user: path.join(__dirname, '../img', 'actions', 'user.svg'),
	folder: path.join(__dirname, '../img', 'actions', 'folder.svg'),
	'folder-description': path.join(__dirname, '../img', 'actions', 'folder-description.svg'),
	'mime-folder': path.join(__dirname, '../img', 'filetypes', 'folder.svg'),
	'mime-folder-audio': path.join(__dirname, '../img', 'filetypes', 'folder-audio.svg'),
	'mime-folder-encrypted': path.join(__dirname, '../img', 'filetypes', 'folder-encrypted.svg'),
	'mime-folder-photo': path.join(__dirname, '../img', 'filetypes', 'folder-photo.svg'),
	'mime-folder-public': path.join(__dirname, '../img', 'filetypes', 'folder-public.svg'),
	'mime-folder-regular': path.join(__dirname, '../img', 'filetypes', 'folder-regular.svg'),
	'mime-folder-shared': path.join(__dirname, '../img', 'filetypes', 'folder-shared.svg'),
	'mime-folder-video': path.join(__dirname, '../img', 'filetypes', 'folder-video.svg'),
	files: path.join(__dirname, '../img', 'places', 'files.svg'),
	history: path.join(__dirname, '../img', 'actions', 'history.svg'),
	tag: path.join(__dirname, '../img', 'actions', 'tag.svg'),
	delete: path.join(__dirname, '../img', 'actions', 'delete.svg'),
	close: path.join(__dirname, '../img', 'close.svg'),
	'close-x': path.join(__dirname, '../img', 'actions', 'close.svg'),
	home: path.join(__dirname, '../img', 'actions', 'home.svg'),
	settings: path.join(__dirname, '../img', 'settings', 'admin.svg'),
	help: path.join(__dirname, '../img', 'settings', 'help.svg'),
	customercenter: path.join(__dirname, '../img', 'settings', 'customercenter.svg'),
	admin: path.join(__dirname, '../img', 'settings', 'apps.svg'),
	apps: path.join(__dirname, '../img', 'actions', 'add.svg'),
	link: path.join(__dirname, '../img', 'actions', 'link.svg'),
	'upload-to-cloud': path.join(__dirname, '../img', 'actions', 'upload-to-cloud.svg'),
	clipboard: path.join(__dirname, '../img', 'actions', 'clipboard.svg'),
	mail: path.join(__dirname, '../img', 'actions', 'mail.svg'),
	users: path.join(__dirname, '../img', 'settings', 'users.svg'),
	play: path.join(__dirname, '../img', 'actions', 'play.svg'),
	'play-previous': path.join(__dirname, '../img', 'actions', 'play-previous.svg'),
	'play-next': path.join(__dirname, '../img', 'actions', 'play-next.svg'),
	'play-video': path.join(__dirname, '../img', 'icons', 'play-video.svg'),
	external: path.join(__dirname, '../img', 'actions', 'copy-paste.svg'),
	attachment: path.join(__dirname, '../img', 'actions', 'attachment.svg'),
	edit: path.join(__dirname, '../img', 'email', 'edit.svg'),
	'mail-opened': path.join(__dirname, '../img', 'email', 'opened.svg'),
	'auto-login': path.join(__dirname, '../img', 'actions', 'auto-login.svg'),
	warning: path.join(__dirname, '../img', 'settings', 'warning.svg'),
	check: path.join(__dirname, '../img', 'checkmarktick.svg'),
	'toggle-filelist': path.join(__dirname, '../img', 'actions', 'toggle-filelist.svg'),
	'toggle-pictures': path.join(__dirname, '../img', 'actions', 'toggle-pictures.svg'),
	restore: path.join(__dirname, '../img', 'actions', 'restore.svg'),
	'cut-paste': path.join(__dirname, '../img', 'actions', 'cut-paste.svg'),
	'compress-zip': path.join(__dirname, '../img', 'actions', 'compress-zip.svg'),
	richdocuments: path.join(__dirname, '../img', 'actions', 'richdocuments.svg'),
	alert: path.join(__dirname, '../img', 'rich-workspace', 'warning.svg'),
	bold: path.join(__dirname, '../img', 'rich-workspace', 'bold.svg'),
	checklist: path.join(__dirname, '../img', 'rich-workspace', 'checklist.svg'),
	callout: path.join(__dirname, '../img', 'rich-workspace', 'callout.svg'),
	code: path.join(__dirname, '../img', 'rich-workspace', 'code.svg'),
	emoji: path.join(__dirname, '../img', 'rich-workspace', 'emoji.svg'),
	h1: path.join(__dirname, '../img', 'rich-workspace', 'h1.svg'),
	h2: path.join(__dirname, '../img', 'rich-workspace', 'h2.svg'),
	h3: path.join(__dirname, '../img', 'rich-workspace', 'h3.svg'),
	h4: path.join(__dirname, '../img', 'rich-workspace', 'h4.svg'),
	h5: path.join(__dirname, '../img', 'rich-workspace', 'h5.svg'),
	h6: path.join(__dirname, '../img', 'rich-workspace', 'h6.svg'),
	image: path.join(__dirname, '../img', 'rich-workspace', 'image.svg'),
	italic: path.join(__dirname, '../img', 'rich-workspace', 'italic.svg'),
	ol: path.join(__dirname, '../img', 'rich-workspace', 'ol.svg'),
	quote: path.join(__dirname, '../img', 'rich-workspace', 'quote.svg'),
	redo: path.join(__dirname, '../img', 'rich-workspace', 'redo.svg'),
	strikethrough: path.join(__dirname, '../img', 'rich-workspace', 'strikethrough.svg'),
	success: path.join(__dirname, '../img', 'rich-workspace', 'success.svg'),
	table: path.join(__dirname, '../img', 'rich-workspace', 'table.svg'),
	'table-settings': path.join(__dirname, '../img', 'rich-workspace', 'table-settings.svg'),
	ul: path.join(__dirname, '../img', 'rich-workspace', 'ul.svg'),
	underline: path.join(__dirname, '../img', 'rich-workspace', 'underline.svg'),
	undo: path.join(__dirname, '../img', 'rich-workspace', 'undo.svg'),
	'text-link': path.join(__dirname, '../img', 'rich-workspace', 'link.svg'),
	'text-file': path.join(__dirname, '../img', 'rich-workspace', 'file.svg'),
	albums: path.join(__dirname, '../img', 'media', 'albums.svg'),
	'all-media': path.join(__dirname, '../img', 'media', 'all-media.svg'),
	camera: path.join(__dirname, '../img', 'device', 'camera.svg'),
	'photo-camera': path.join(__dirname, '../img', 'device', 'photo-camera.svg'),
	'hide-menu': path.join(__dirname, '../img', 'actions', 'hide-menu.svg'),
	'photos-videos': path.join(__dirname, '../img', 'media', 'photos-videos.svg'),
	appearance: path.join(__dirname, '../img', 'settings', 'appearance.svg'),
	'arrow-previous': path.join(__dirname, '../img', 'back-nav.svg'),
	'arrow-next': path.join(__dirname, '../img', 'breadcrumb-arrow.svg'),
	filter: path.join(__dirname, '../img', 'actions', 'filter.svg'),
	calendar: path.join(__dirname, '../img', 'content', 'calendar.svg'),
	news: path.join(__dirname, '../img', 'content', 'news.svg'),
	'filetype-diagram': path.join(__dirname, '../img', 'filetypes', 'x-office-drawing.svg'),
	'filetype-document': path.join(__dirname, '../img', 'filetypes', 'x-office-document.svg'),
	'filetype-presentation': path.join(__dirname, '../img', 'filetypes', 'x-office-presentation.svg'),
	'filetype-spreadsheet': path.join(__dirname, '../img', 'filetypes', 'x-office-spreadsheet.svg'),
	video: path.join(__dirname, '../img', 'actions', 'play.svg'),
	export: path.join(__dirname, '../img', 'actions', 'export.svg'),
	import: path.join(__dirname, '../img', 'actions', 'import.svg'),
}

const iconsColor = {
	starred: {
		path: path.join(__dirname, '../img', 'actions', 'starred.svg'),
		color: 'yellow',
	},
	'delete-starred': {
		path: path.join(__dirname, '../img', 'actions', 'delete.svg'),
		color: 'warning',
	},
	file: {
		path: path.join(__dirname, '../img', 'filetypes', 'text.svg'),
		color: 'grey',
	},
	'breadcrumb-arrow': {
		path: path.join(__dirname, '../img', 'breadcrumb-arrow.svg'),
		color: 'grey',
	},
	checkmark: {
		path: path.join(__dirname, '../img', 'email', 'checkmark.svg'),
		color: 'magenta',
	},
	info: {
		path: path.join(__dirname, '../img', 'rich-workspace', 'callout.svg'),
		color: 'info',
	},
	success: {
		path: path.join(__dirname, '../img', 'rich-workspace', 'success.svg'),
		color: 'success',
	},
	warning: {
		path: path.join(__dirname, '../img', 'rich-workspace', 'warning.svg'),
		color: 'warning',
	},
	danger: {
		path: path.join(__dirname, '../img', 'rich-workspace', 'danger.svg'),
		color: 'danger',
	},
}

let css = ''
Object.keys(icons).forEach(icon => {
	const path = icons[icon]

	const svg = fs.readFileSync(path, 'utf8')
	const darkSvg = colorSvg(svg, '000000')
	const whiteSvg = colorSvg(svg, 'ffffff')

	variables[`--original-icon-${icon}-dark`] = Buffer.from(darkSvg, 'utf-8').toString('base64')
	variables[`--original-icon-${icon}-white`] = Buffer.from(whiteSvg, 'utf-8').toString('base64')
})

Object.keys(iconsColor).forEach(icon => {
	const { path, color } = iconsColor[icon]

	const svg = fs.readFileSync(path, 'utf8')
	const coloredSvg = colorSvg(svg, colors[color])
	variables[`--icon-${icon}-${color}`] = Buffer.from(coloredSvg, 'utf-8').toString('base64')
})

css += ':root {'
Object.keys(variables).forEach(variable => {
	const data = variables[variable]
	css += `${variable}: url(data:image/svg+xml;base64,${data});`
})
css += '}'

css += 'body {'
css += generateVariablesAliases(variables)
css += '}'

css += '@media (prefers-color-scheme: dark) { body {'
css += generateVariablesAliases(variables, true)
css += '}}'

css += '[data-themes*=light] {'
css += generateVariablesAliases(variables)
css += '}'

css += '[data-themes*=dark] {'
css += generateVariablesAliases(variables, true)
css += '}'

const distFolder = path.join(__dirname, '../dist')
fs.mkdirSync(distFolder, { recursive: true })
fs.writeFileSync(path.join(distFolder, 'icons.css'), sass.compileString(css).css)
