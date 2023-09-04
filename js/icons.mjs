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
	yellow: 'a08b00',
	red: 'e9322d',
	orange: 'eca700',
	green: '46ba61',
	grey: '969696',
}

const colorSvg = function(svg = '', color = '000') {
	if (!color.match(/^[0-9a-f]{3,6}$/i)) {
		// Prevent not-sane colors from being written into the SVG
		console.warn(color, 'does not match the required format')
		color = '000'
	}

	// add fill (fill is not present on black elements)
	const fillRe = /<((circle|rect|path)((?!fill)[a-z0-9 =".\-#():;,])+)\/>/gmi
	svg = svg.replace(fillRe, '<$1 fill="#' + color + '"/>')

	// replace any fill or stroke colors
	svg = svg.replace(/stroke="#([a-z0-9]{3,6})"/gmi, 'stroke="#' + color + '"')
	svg = svg.replace(/fill="#([a-z0-9]{3,6})"/gmi, 'fill="#' + color + '"')

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
	folder: path.join(__dirname, '../img', 'filetypes', 'folder.svg'),
	files: path.join(__dirname, '../img', 'places', 'files.svg'),
	history: path.join(__dirname, '../img', 'actions', 'history.svg'),
	tag: path.join(__dirname, '../img', 'actions', 'tag.svg'),
	delete: path.join(__dirname, '../img', 'actions', 'delete.svg'),
	close: path.join(__dirname, '../img', 'close.svg'),
}

const iconsColor = {
	starred: {
		path: path.join(__dirname, '../img', 'actions', 'star-dark.svg'),
		color: 'yellow',
	},
	file: {
		path: path.join(__dirname, '../img', 'filetypes', 'text.svg'),
		color: 'grey',
	},
	'breadcrumb-arrow': {
		path: path.join(__dirname, '../img', 'breadcrumb-arrow.svg'),
		color: 'grey',
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
