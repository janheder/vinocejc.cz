const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const sourcemaps = require('gulp-sourcemaps');
const sassGlob = require('gulp-sass-glob');
const browserSync = require('browser-sync').create();
const concat = require('gulp-concat');
const pug = require('gulp-pug-3');
const uglify = require('gulp-uglify-es').default;
const postcss = require('gulp-postcss');
const autoprefixer= require('autoprefixer');


//compile scss into css
function style() {
    var processors = [ autoprefixer()];
    return gulp.src('./src/scss/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sassGlob())
    .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
    .pipe(concat('style.min.css'))
    .pipe(postcss(processors))
    .pipe(sourcemaps.write('./maps'))
    .pipe(gulp.dest('./dist/css'))
    .pipe(browserSync.stream());
}
//compile scss into css
function style2() {
    var processors = [ autoprefixer()];
    return gulp.src('./src/scss-2/*.scss')
    .pipe(sourcemaps.init())
    .pipe(sassGlob())
    .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
    .pipe(concat('style-2.min.css'))
    .pipe(postcss(processors))
    .pipe(sourcemaps.write('./maps'))
    .pipe(gulp.dest('./dist/css'))
    .pipe(browserSync.stream());
}

function buildhtml() {
    return gulp.src('./src/pug/*.pug')
    .pipe(
        pug({ 
            pretty: 1
        })
    )
    .pipe(gulp.dest('./dist'));
}

//compile js
function scripts() {
    return gulp.src('./src/js/*.js')
    .pipe(sourcemaps.init())
    .pipe(uglify({mangle: false}))
    .pipe(sourcemaps.write('./maps'))
    .pipe(gulp.dest('./dist/js'))
    .pipe(browserSync.stream());
}



function watch() {
    browserSync.init({
        server: {
           baseDir: "./dist/"
        }
    });
    gulp.watch('./src/scss/**/*.scss', style)
    gulp.watch('./src/scss-2/**/*.scss', style2)
    gulp.watch('./src/pug/**/*.pug', buildhtml)
    gulp.watch('./src/js/**/*.js', scripts)
    gulp.watch('./dist/**/*.html').on('change',browserSync.reload);
    gulp.watch('./dist/js/**/*.js').on('change', browserSync.reload);
}


exports.style = style;
exports.style = style2;
exports.buildhtml = pug;
exports.scripts = scripts;
exports.watch = watch;