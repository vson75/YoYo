import $ from 'jquery';
// import Swiper JS
import Swiper from 'swiper';
// import Swiper styles
//import 'swiper/swiper-bundle.css';

import '../css/components/_homepage.scss';

$(document).ready(function () {
    if ($(".preloader").length) {
      $(".preloader").fadeOut();
    }
 
    // dynamic radius
    const dynamicRadius = document.querySelectorAll(".dynamic-radius");
    dynamicRadius.forEach(function (btn) {
      let btnHeight = btn.offsetHeight;
      btn.style.borderBottomLeftRadius = btnHeight / 2 + "px";
      btn.style.borderTopLeftRadius = btnHeight / 2 + "px";
    });
  });

var shareFB = document.querySelector('[id^="fb-share-"]');

  shareFB.addEventListener('click', function(){
    var uniquekey = shareFB.id.replace("fb-share-","");
    console.log(uniquekey);
    var postURL = window.location.href + "post/" + uniquekey;
    window.open('https://www.facebook.com/sharer/sharer.php?u=' + postURL,
    'facebook-share-dialog',
    'width=800,height=600'
    );
    return false;
  });


$('[id^="addFav_"]').on('click', function(e){
  e.preventDefault();
  var uniquekey = this.id.replace("addFav_","");
  //console.log(e.currentTarget);
  var tagetDiv = $(e.currentTarget);
  var url = "ajax/add_favorite/"+uniquekey;

  // check if user already add favorite this post
  if(tagetDiv.attr('class') === 'fas fa-star'){
    $(e.currentTarget).removeClass('fas fa-star')
                      .addClass('far fa-star');
    $.ajax(
    {
      url: url+"/0",
      method: 'POST'
    });
  }else{

    $(e.currentTarget).removeClass('far fa-star')
                      .addClass('fas fa-star');
    $.ajax(
    {
      url: url+"/1",
      method: 'POST'
    });
  }
});