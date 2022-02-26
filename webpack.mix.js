// webpack.mix.js

let mix = require('laravel-mix');
mix.setPublicPath('src/assets/compiled');
mix.js('src/assets/site/js/main.js', 'craft-tablecloth.js');
mix.js('src/assets/cp/js/cp.js', 'craft-tablecloth-cp.js');
mix.minify('src/assets/compiled/craft-tablecloth.js')
mix.minify('src/assets/compiled/craft-tablecloth-cp.js')

