const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js', 'public/assets/js/argon-dashboard.js')
    .postCss('resources/css/app.css', 'public/css', [
        //
    ])
    .sass('resources/scss/argon-dashboard.scss', 'public/assets/css/argon-dashboard.css', [
        //
    ]);

mix.js('resources/js/plataforma.js', 'public/js').sass('resources/scss/plataforma.scss', 'css/plataforma.css',[]);