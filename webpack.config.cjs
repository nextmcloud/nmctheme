const { VueLoaderPlugin } = require('vue-loader')
const path = require('path')

module.exports = {
	entry: {
		filessettings: './src/js/filessettings.js',
		nmcsettings: './src/js/nmcsettings.js',
		// keep src for future use: l10nappender: './src/l10nappender.ts',
		conflictdialog: './src/js/conflictdialog.js',
		nmcfooter: './src/nmcfooter.ts',
		nmclogo: './src/nmclogo.ts',
	},
	output: {
		path: path.resolve(__dirname, 'dist'),
		filename: '[name].js',
	},
	devtool: 'source-map',
	module: {
		rules: [
			{
				test: /\.css$/,
				use: ['style-loader', 'css-loader'],
			},
			{
				test: /\.scss$/,
				use: ['style-loader', 'css-loader', 'sass-loader'],
			},
			{
				test: /\.vue$/,
				loader: 'vue-loader',
			},
			{
				test: /\.(png|jpe?g|gif|svg|woff2?|eot|ttf)$/,
				type: 'asset/inline',
			},
			{
				test: /\.tsx?$/,
				use: 'ts-loader',
				exclude: /node_modules/,
			},
		],
	},
	plugins: [
		new VueLoaderPlugin(),
	],
	resolve: {
		fallback: {
			stream: false,
			https: false,
			http: false,
			path: false,
		},
		alias: {
			vue$: path.resolve('./node_modules/vue'),
		},
		extensions: ['.*', '.js', '.ts', '.vue', '.json'],
	},
}
