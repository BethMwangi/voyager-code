/* eslint-env es6 */

// Import Dev packages.
import autoprefixer from 'autoprefixer';
import cleanCSS from 'gulp-clean-css';
import concat from 'gulp-concat';
import fs from 'fs';
import gulp from 'gulp';
import gulpif from 'gulp-if';
import imagemin from 'gulp-imagemin';
import log from 'fancy-log';
import merge from 'merge-stream';
import plumber from 'gulp-plumber';
import postcss from 'gulp-postcss';
import requireUncached from 'require-uncached';
import sass from 'gulp-sass';
import sourcemaps from 'gulp-sourcemaps';
import tabify from 'gulp-tabify';
import uglify from 'gulp-uglify';
import webpack from 'webpack';
import webpackStream from 'webpack-stream';
import yargs from 'yargs';

// Webpack Configs.
const webpackConfig = requireUncached('./webpack.config');

// Command arguments.
const { argv } = yargs;

// Is this development setup?
const devBuild = ((process.env.NODE_ENV || 'development').trim().toLowerCase() === 'development');

// If we're on development let's inform webpack.
if (devBuild) {
  webpackConfig.mode = 'development'; // Defaults to 'production' if is not on dev.
}

// Project paths.
const paths = {
  root: '/**/*',
  styles: {
    src: [
      'assets/sass/style.scss',
    ],
    dest: 'build',
    sass: 'assets/sass/**/*.scss',
  },
  scripts: {
    src: ['assets/js/main.js'],
    watch: ['assets/js/**/*.js'],
    dest: 'build',
  },
  php: {
    src: ['**/*.php'],
    dest: './',
  },
  images: {
    src: ['**/*.{jpg,JPG,png,svg,gif,GIF}'],
    dest: './',
  },
  themes: {
    'cf-starter': 'wp-content/themes/cf-starter/',
  },
  plugins: {
  },
};

/**
 * Helper function to check if we have themes or plugins.
 *
 * @param  {object}  object The object to check against.
 * @return {Boolean}        Is it empty or not.
 */
function isEmptyObject(object) {
  return Object.getOwnPropertyNames(object).length === 0;
}


/**
 * Return the styles pipes for an asset from the list.
 *
 * @param  {string} slug      The asset slug.
 * @param  {array}  assetList The themes/plugins list.
 * @return {stream}           The gulp stream.
 */
function makeStylePipes(slug, assetList) {
  const sources = [];
  paths.styles.src.forEach((src) => {
    const file = assetList[slug] + src;
    if (fs.existsSync(file)) {
      sources.push(assetList[slug] + src);
    }
  });

  log.info(`Building ${slug} Styles...`);

  return gulp.src(sources)
    // Init the sourcemaps.
    .pipe(sourcemaps.init())
    // Compile the sass.
    .pipe(sass({
      // Include the node_modules folder.
      includePaths: ['node_modules'],
    }).on('error', sass.logError))
    // Transform all spaces to 2 tabs.
    .pipe(tabify(2, true))
    // Add prefixes.
    .pipe(postcss([autoprefixer()]))
    // If not in debug mode minify the styles.
    .pipe(gulpif(!devBuild, cleanCSS({ sourceMap: true, level: 2 })))
    // Stop listening and write the sourcemaps.
    .pipe(sourcemaps.write('.'))
    // Spit it out in the dest folder.
    .pipe(gulp.dest(assetList[slug] + paths.styles.dest))
    .on('end', () => { log.info(`Finished ${slug} Styles Build.`); });
}


/**
 * Return the scripts pipes for an asset from the list.
 *
 * @param  {string} slug      The asset slug.
 * @param  {array}  assetList The themes/plugins list.
 * @return {stream}           The gulp stream.
 */
function makeScriptsPipes(slug, assetList) {
  log.info(`Building ${slug} Scripts...`);

  return gulp.src(assetList[slug] + paths.scripts.src)
    // Ignore the eslint warnings and compile the files anyway.
    .pipe(plumber())
    // Compile the file with this webpack config.
    .pipe(webpackStream(webpackConfig, webpack))
    // If not in debug mode minify the files.
    .pipe(gulpif(!devBuild, uglify()))
    .pipe(concat('main.js'))
    // Spit it out in the dest folder.
    .pipe(gulp.dest(assetList[slug] + paths.scripts.dest));
}


/**
 * Return the images pipes for an asset from the list.
 *
 * @param  {string} slug      The asset slug.
 * @param  {array}  assetList The themes/plugins list.
 * @return {stream}           The gulp stream.
 */
function makeImagePipes(slug, assetList) {
  log.info(`Optimizing ${slug} images set.`);
  return gulp.src(assetList[slug] + paths.images.src)
    .pipe(imagemin())
    .pipe(gulp.dest(assetList[slug] + paths.images.dest));
}


/**
 * Themes assets build.
 *
 * @param  {function} done The gulp done event function.
 */
export function buildThemesAssets(done) {
  if (isEmptyObject(paths.themes)) {
    log.error('No themes to compile.. skipping!');
    return done();
  }

  // The THEME STYLES.
  const styles = Object.keys(paths.themes).map(theme => makeStylePipes(theme, paths.themes));
  // The THEME SCRIPTS.
  const scripts = Object.keys(paths.themes).map(theme => makeScriptsPipes(theme, paths.themes));

  return merge(styles, scripts);
}


/**
 * Theme assets build.
 *
 * @param  {function} done The gulp done event function.
 */
export function buildThemeAssets(done) {
  const { theme } = argv; // Get the theme argv.

  if (!theme || isEmptyObject(paths.themes) || typeof paths.themes[theme] === 'undefined') {
    log.error('You must specify a theme from the list!');
    return done();
  }
  // The THEME STYLES.
  const styles = makeStylePipes(theme, paths.themes);
  // The THEME SCRIPTS.
  const scripts = makeScriptsPipes(theme, paths.themes);

  return merge(styles, scripts);
}


/**
 * Plugins assets build.
 *
 * @param  {function} done The gulp done event function.
 */
export function buildPluginsAssets(done) {
  if (isEmptyObject(paths.plugins)) {
    log.error('No plugins to compile.. skipping!');
    return done();
  }

  // The PLUGIN STYLES.
  const styles = Object.keys(paths.plugins).map(plugin => makeStylePipes(plugin, paths.plugins));
  // The PLUGIN SCRIPTS.
  const scripts = Object.keys(paths.plugins).map(plugin => makeScriptsPipes(plugin, paths.plugins));

  return merge(styles, scripts);
}


/**
 * Plugin assets build.
 *
 * @param  {function} done The gulp done event function.
 */
export function buildPluginAssets(done) {
  const { plugin } = argv; // Get the plugin argv.

  if (!plugin || isEmptyObject(paths.plugins) || typeof paths.plugins[plugin] === 'undefined') {
    log.error('You must specify a plugin from the list!');
    return done();
  }

  // The THEME STYLES.
  const styles = makeStylePipes(plugin, paths.plugins);
  // The THEME SCRIPTS.
  const scripts = makeScriptsPipes(plugin, paths.plugins);

  return merge(styles, scripts);
}

/**
 * Watch everything.
 */
export function watch() {
  if (!isEmptyObject(paths.themes)) {
    // Watch every theme STYLES.
    Object.keys(paths.themes).forEach((theme) => {
      // Watch all the theme sass files.
      gulp.watch(paths.themes[theme] + paths.styles.sass, function watchStyle() { // eslint-disable-line
        return makeStylePipes(theme, paths.themes);
      });
    });

    // Watch every theme SCRIPTS.
    Object.keys(paths.themes).forEach((theme) => {
      // Watch the theme js main file.
      gulp.watch(paths.themes[theme] + paths.scripts.watch, function watchScript() { // eslint-disable-line
        return makeScriptsPipes(theme, paths.themes);
      });
    });
  }

  if (!isEmptyObject(paths.plugins)) {
    // Watch every plugin STYLES.
    Object.keys(paths.plugins).forEach((plugin) => {
      // Watch all the plugin sass files.
      gulp.watch(paths.plugins[plugin] + paths.styles.sass, function watchStyle() { // eslint-disable-line
        return makeStylePipes(plugin, paths.plugins);
      });
    });

    // Watch every plugin SCRIPTS.
    Object.keys(paths.plugins).forEach((plugin) => {
      // Watch the plugin js main file.
      gulp.watch(paths.plugins[plugin] + paths.scripts.watch, function watchScript() { // eslint-disable-line
        return makeScriptsPipes(plugin, paths.plugins);
      });
    });
  }
}


/**
 * Theme assets watch.
 *
 * @param  {function} done The gulp done event function.
 */
export function watchThemeAssets(done) {
  const { theme } = argv; // Get the theme argv.

  // If we don't have the theme slug or if it doesn't exist.
  if (!theme || isEmptyObject(paths.themes) || typeof paths.themes[theme] === 'undefined') {
    log.error('You must specify a theme from the list!');
    return done();
  }

  // First build the assets.
  buildThemeAssets();

  // Watch all the theme sass files.
  gulp.watch(paths.themes[theme] + paths.styles.sass, function watchStyle() { // eslint-disable-line
    return makeStylePipes(theme, paths.themes);
  });

  // Watch the theme js main file.
  gulp.watch(paths.themes[theme] + paths.scripts.watch, function watchScript() { // eslint-disable-line
    return makeScriptsPipes(theme, paths.themes);
  });

  return done();
}


/**
 * Plugin assets watch.
 *
 * @param  {function} done The gulp done event function.
 */
export function watchPluginAssets(done) {
  const { plugin } = argv; // Get the plugin argv.

  // If we don't have the plugin slug or if it doesn't exist.
  if (!plugin || isEmptyObject(paths.plugins) || typeof paths.plugins[plugin] === 'undefined') {
    log.error('You must specify a plugin from the list!');
    return done();
  }

  // First build the assets.
  buildPluginsAssets();

  // Watch all the plugin sass files.
  gulp.watch(paths.plugins[plugin] + paths.styles.sass, function watchStyle() { // eslint-disable-line
    return makeStylePipes(plugin, paths.plugins);
  });

  // Watch the plugin js main file.
  gulp.watch(paths.plugins[plugin] + paths.scripts.watch, function watchScript() { // eslint-disable-line
    return makeScriptsPipes(plugin, paths.plugins);
  });

  return done();
}


/**
 * Optimize images.
 *
 * @param  {function} done The gulp done event function.
 */
export function optimizeImages(done) {
  const stream = merge();
  let plugins;
  let themes;


  // If we have plugins check the standards.
  if (!isEmptyObject(paths.plugins)) {
    // The PLUGINS images optimization.
    plugins = Object.keys(paths.plugins).map(plugin => makeImagePipes(plugin, paths.plugins));
    stream.add(plugins);
  }

  // If we have themes check the standards.
  if (!isEmptyObject(paths.themes)) {
    // The THEMES images optimization.
    themes = Object.keys(paths.themes).map(theme => makeImagePipes(theme, paths.themes));
    stream.add(themes);
  }

  if (stream.isEmpty()) {
    log.error('No images were optimized.');
    return done();
  }

  return stream;
}


/**
 * Map out the sequence of events on build.
 */
const build = gulp.series(buildThemesAssets, buildPluginsAssets);

/**
 * The default gulp command.
 */
export default build;


/**
 * Map out the sequence of events on dev.
 */
const dev = gulp.series(build, watch);

/**
 * The dev gulp command.
 */
export { dev };
