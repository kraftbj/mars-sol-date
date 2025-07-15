const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const webpack = require( 'webpack' );

module.exports = {
	...defaultConfig,
	plugins: [
		...defaultConfig.plugins.filter(
			( plugin ) => plugin.constructor.name !== 'BannerPlugin'
		),
		new webpack.BannerPlugin( {
			banner: `@source https://github.com/kraftbj/mars-sol-date\n@license GPL-2.0-or-later`,
			raw: false,
			entryOnly: true,
		} ),
	],
	optimization: {
		...defaultConfig.optimization,
		minimizer: [
			new ( require( 'terser-webpack-plugin' ) )( {
				terserOptions: {
					format: {
						comments: /@source|@license/,
					},
				},
				extractComments: false,
			} ),
		],
	},
};
