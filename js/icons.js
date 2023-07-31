import iconsPkg from 'nextcloud/core/src/icons.js'
import path from 'path'
import * as sass from 'sass'
import fs from 'fs'
import { fileURLToPath } from 'url'

const { colors, colorSvg } = iconsPkg;

const __dirname = path.dirname(fileURLToPath(import.meta.url))

const variables = {}

const icons = {
  "add": path.join(__dirname, '../img', 'actions', 'add.svg'),
  "address": path.join(__dirname, '../img', 'actions', 'address.svg'),
  "download": path.join(__dirname, '../img', 'actions', 'download.svg'),
  "logout": path.join(__dirname, '../img', 'actions', 'logout.svg'),
  "share": path.join(__dirname, '../img', 'actions', 'share.svg'),
  "star": path.join(__dirname, '../img', 'actions', 'star.svg'),
  "starred": path.join(__dirname, '../img', 'actions', 'starred.svg'),
  "upload": path.join(__dirname, '../img', 'actions', 'upload.svg'),
  "user": path.join(__dirname, '../img', 'actions', 'user.svg'),
  "folder": path.join(__dirname, '../img', 'filetypes', 'folder.svg'),
  'files': path.join(__dirname, '../img', 'places', 'files.svg'),
}

const iconsColor = {
  'starred': {
		path: path.join(__dirname, '../img', 'actions', 'star-dark.svg'),
		color: 'yellow',
	},
	'file': {
		path: path.join(__dirname, '../img', 'filetypes', 'text.svg'),
		color: 'grey',
	},
}

const generateVariablesAliases = function(invert = false) {
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
css += generateVariablesAliases(true)
css += '}}'

css += '[data-themes*=light] {'
css += generateVariablesAliases()
css += '}'

css += '[data-themes*=dark] {'
css += generateVariablesAliases(true)
css += '}'

fs.writeFileSync(path.join(__dirname, '../dist', 'icons.css'), sass.compileString(css).css)