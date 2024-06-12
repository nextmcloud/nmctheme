// webpack with standard nextcloud config
const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry = {
	...webpackConfig.entry,
	filessettings: path.join(__dirname, 'src', 'js', 'filessettings.js'),
	conflictdialog: path.join(__dirname, 'src', 'js', 'conflictdialog.js'),
	mimetypes: path.join(__dirname, 'src', 'js', 'mimetypes.js'),
	nmcfooter: path.join(__dirname, 'src', 'nmcfooter.ts'),
	nmcheader: path.join(__dirname, 'src', 'nmcheader.ts'),
	nmclogo: path.join(__dirname, 'src', 'nmclogo.ts'),
	nmcfiles: path.join(__dirname, 'src', 'nmcfiles.ts'),
	shareicons: path.join(__dirname, 'src', 'js', 'shareicons.js'),
	tooltip: path.join(__dirname, 'src', 'js', 'tooltip.js'),
}

// Workaround for https://github.com/nextcloud/webpack-vue-config/pull/432 causing problems with nextcloud-vue-collections
webpackConfig.resolve.alias = {}

module.exports = webpackConfig
