// webpack.mix.js

let mix = require('laravel-mix');
mix.setPublicPath('src/assets/compiled');
mix.js('src/assets/site/js/main.js', 'craft-tablecloth.min.js');
mix.js('src/assets/cp/js/cp.js', 'craft-tablecloth-cp.min.js');

