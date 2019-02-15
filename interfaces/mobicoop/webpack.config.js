const Encore = require('@symfony/webpack-encore');
const StyleLintPlugin = require('stylelint-webpack-plugin');
const fs = require('fs');

let files = fs.readdirSync('./assets/js/page',{
  withFileTypes: true
});

console.log('files', files)

Encore
  // directory where compiled assets will be stored
  .setOutputPath('public/build/')
  // public path used by the web server to access the output path
  .setPublicPath('/build')
  // only needed for CDN's or sub-directory deploy
  // .setManifestKeyPrefix('build/')

  /*
   * ENTRY CONFIG
   *
   * Add 1 entry for each "page" of your app
   * (including one that's included on every page - e.g. "app")
   *
   * Each entry will result in one JavaScript file (e.g. app.js)
   * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
  */

    //.addEntry('app', './src/MobicoopBundle/Resources/assets/js/app.js')
    
    .addEntry('app', './src/MobicoopBundle/Resources/assets/js/app.js')
//    .addEntry('autocomplete', './src/MobicoopBundle/Resources/assets/js/page/autocomplete.js')
//    .addEntry('ad_create', './src/MobicoopBundle/Resources/assets/js/page/ad/create.js')
//    .addEntry('home', './src/MobicoopBundle/Resources/assets/js/page/home.js')
//    .addEntry('users', './src/MobicoopBundle/Resources/assets/js/page/users.js')
    
    .splitEntryChunks()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    // .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())
    .addLoader({
        test: /\.(js|vue)$/,
        enforce: 'pre',
        loader: 'eslint-loader',
        exclude: ['/node_modules','/vendor','/public'],
        options: {
          fix: true
        }
    })
    .addPlugin(new StyleLintPlugin({
      failOnWarning: false,
      failOnError: false,
      testing: false,
      fix: true,
      emitErrors: false,
      syntax : 'scss'
    }))
    // enables Sass/SCSS support
    .enableSassLoader()
    .configureBabel(function(babelConfig) {
        // add additional presets
        babelConfig.plugins.push('transform-class-properties');
        // babelConfig.presets.push('stage-3');
    })
    // This will add compatibility for old nav
    .enablePostCssLoader()
    .enableVueLoader()
    //fixed dev-server
    .setManifestKeyPrefix('/build')
    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
;

for (let file of files){
  if(file.isFile()){
    Encore.addEntry(file.name, `./assets/js/page/${file.name}`)
  }
}

module.exports = Encore.getWebpackConfig();
