/**
 * Simple (ugly) code to handle the nb participant
 */
var $container = $('.finance_project');

$container.find('button').on('click', function(e) {
  e.preventDefault();

    $.ajax({
        url: '/participant_project/1',
        method: 'POST'
    }).then(function(data) {
        // the key nb_Participant is find in PostController
       $container.find('.js-total-participant').text(data.nb_Participant);
    });
});