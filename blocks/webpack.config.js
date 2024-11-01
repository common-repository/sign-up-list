const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
	...defaultConfig,
	entry: {
		'sul-entries': './src/sul-entries',
		'sul-sign-up': './src/sul-sign-up',
	},
};