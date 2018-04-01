let mix = require('laravel-mix');

mix.options({
    postCss: [
        require('postcss-discard-comments')({ removeAll: true })
    ],
    //purifyCss: true
});

mix.scripts([
    'node_modules/jquery/dist/jquery.js', // This file is necessary
    'node_modules/popper.js/dist/umd/popper.js', // This file is necessary
    'node_modules/bootstrap/dist/js/bootstrap.js', // this is the full version
    //'node_modules/startbootstrap-sb-admin/js/sb-admin.js',
    //'node_modules/startbootstrap-sb-admin/js/sb-admin-charts.js',
    //'node_modules/startbootstrap-sb-admin/js/sb-admin-datatables.js',
    //'node_modules/jquery.easing/jquery.easing.js',
    //'node_modules/pickadate/lib/picker.js', // pickadate library for both date and time
    //'node_modules/pickadate/lib/picker.date.js', // pickadate library only for date field
    //'node_modules/pickadate/lib/picker.date.js', // pickadate library only for time field
    //'node_modules/select2/dist/js/select2.full.js',
    //'node_modules/spin.js/spin.js',
    //'resources/assets/js/admin/spin.js-2.3.2/spin.js',
    //'resources/assets/js/admin/admin.js'
], 'public/js/scripts.js').version();

mix.sass('resources/assets/sass/styles.scss', 'public/css').version();

mix.browserSync({
    proxy: 'mixit.local',
    notify: false
});
