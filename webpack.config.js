var Encore = require('@symfony/webpack-encore');

Encore.setOutputPath('public/build/').setPublicPath('/build').
    addEntry('app_scrypt', './assets/js/app.js').
    addEntry('admin_scrypt', './assets/js/app_admin.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks().enableSingleRuntimeChunk().
    cleanupOutputBeforeBuild().
    enableBuildNotifications().
    enableSourceMaps(!Encore.isProduction())
    .enableVersioning(true)

    .configureBabel(() => {
    }, {
      useBuiltIns: 'usage',
      corejs: 3,
    }).
    enableSassLoader().
    enableVueLoader().
    autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
