/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

// Importations des fichiers jQuery et bootstrap
import './scripts/jquery.js';
import './bootstrap-5.2.2/js/bootstrap.min.js';

// Permet d'utiliser la variable $ de jquery dans les fichiers JS
const $ = require('jquery');
global.$ = global.jQuery = $;