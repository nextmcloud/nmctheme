module.exports = {
	extends: [
		'@nextcloud',
	],
	rules: {
		"camelcase": "off",
		"jsdoc/require-param-description": "off",
		"n/no-unpublished-import": ["error", {
            "allowModules": ["throttle-debounce"]
        }],
		"vue/require-default-prop": "off",
		"vue/require-prop-type-constructor": "off",
		"@typescript-eslint/no-explicit-any": "off"
	}
}