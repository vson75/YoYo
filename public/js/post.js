/**
 * Simple (ugly) code to handle the nb participant
 */
var $container = $('.finance_project');

$container.find('button').on('click', function(e) {
  e.preventDefault();
//  alert('1');
    var $link = $(e.currentTarget);
    var $path = window.location.pathname;
    var $slug = $path.replace("/post/","");

   // alert($slug);

    $.ajax({
        cache: false,
        url: '/participant_project/'+$slug,
        method: 'POST',
        async: false,
    }).then(function(data) {
      //  alert(data.nb_Participant);
        // the key nb_Participant is find in PostController
       $container.find('.js-total-participant').html(data.nb_Participant);
    });
});