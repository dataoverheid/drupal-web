const gulp = require('gulp');
const sass = require('gulp-sass');
const sourcemaps = require('gulp-sourcemaps');
const concat = require('gulp-concat');
const autoprefixer = require('gulp-autoprefixer');
const browserSync = require('browser-sync').create();
const rename = require('gulp-rename');
const header = require('gulp-header');

sass.compiler = require('node-sass');

// Copy config JSON to gulpSettings-default to gulpSettings to change the
// settings.
let config;
try {
  config = require('./gulpSettings.json');
}
catch (error) {
  console.log(' ---------------------------------------', '\n', '   No config found. Using default.', '\n', '---------------------------------------');
  config = {
    "siteUrl": "dataoverheid.local",
    "browserSyncPort": 1547,
    "browsers": [
      "firefox"
    ]
  }
}

gulp.task('sass', function () {
  return gulp.src('./sass/**/*.scss')
    .pipe(sourcemaps.init())
    .pipe(header('@import \'./sass/_globals/_variables.scss\';\n'))
    .pipe(sass.sync({
      outputStyle: 'compressed',
      precision: 2
    }).on('error', sass.logError))
    .pipe(autoprefixer({cascade: false}))
    .pipe(concat('dataoverheid.css'))
    .pipe(sourcemaps.write('maps'))
    .pipe(gulp.dest('./css'))
    .pipe(browserSync.stream());
});

gulp.task('reload-twig', function () {
  //Requires a file to start the pipe.
  return gulp.src('./indicia.theme').pipe(browserSync.stream());
});

gulp.task('browserSync', function () {
  return browserSync.init({
    proxy: config.siteUrl,
    port: config.browserSyncPort,
    baseDir: "./",
    open: true,
    notify: false,
    browser: config.browsers
  });
});

gulp.task('watcher', function () {
  console.log('Watching scss, js,fonts, twig');
  gulp.watch('./sass/**/*.scss', gulp.series(['sass']));
  gulp.watch('./sass/**/*.twig', gulp.series(['reload-twig']));
});

// compile once
gulp.task('build', gulp.series(['sass']));

// start the watcher.
gulp.task('watch', gulp.series('build', 'watcher'));

// Serves with browsersync.
gulp.task('serve', gulp.parallel('browserSync', 'watch'));

// Defaults to serve
gulp.task('default', gulp.series(['serve']));
