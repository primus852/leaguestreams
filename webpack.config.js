var Encore = require('@symfony/webpack-encore');

const {VueLoaderPlugin} = require('vue-loader');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .addEntry('js/app', './assets/js/app.js')
    //.addStyleEntry('css/style', './assets/css/custom.less')
    .enableLessLoader(function (options) {
        // https://github.com/webpack-contrib/less-loader#examples
        // http://lesscss.org/usage/#command-line-usage-options
        // options.relativeUrls = false;
    })
    .addLoader({
        test: /\.vue$/,
        loader: 'vue-loader',
        options: {
            mode: 'production'
        }
    })
    .addPlugin(new VueLoaderPlugin())
    .autoProvidejQuery()
    .addAliases({
        vue: 'vue/dist/vue.min'
    })
;

const config = Encore.getWebpackConfig();
config.watchOptions = {
    poll: true,
};


module.exports = config;