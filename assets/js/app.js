/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.scss';
import getTestJS from './components/test';
import $ from 'jquery';
import 'bootstrap'; // add functions to jQuery
//global.$ = $; 
 
// Need jQuery? Install it with "yarn add jquery", then uncomment to import it.
// import $ from 'jquery';

// this code is custom to add input filename in the input field. do not delete
$('.custom-file-input').on('change', function(event) {
    var inputFile = event.currentTarget;
    $(inputFile).parent()
        .find('.custom-file-label')
        .html(inputFile.files[0].name);
});
