import $ from 'jquery';
// import Swiper JS
import Swiper from 'swiper';
// import Swiper styles
import 'swiper/swiper-bundle.css';

import '../css/components/_homepage.scss';



$(document).ready(function () {

    if ($(".preloader").length) {
      $(".preloader").fadeOut();
    }
    // swiper slider

    const swiperElm = document.querySelectorAll(".thm-swiper__slider");

    swiperElm.forEach(function (swiperelm) {
      let thmSwiperSlider = new Swiper(swiperelm, {
        "slidesPerView": 3, 
        "spaceBetween": 30,
        "breakpoints": {
            "0": {
                "slidesPerView": 1,
                "spaceBetween": 30
            },
            "375": {
                "slidesPerView": 1,
                "spaceBetween": 30
            },
            "575": {
                "slidesPerView": 1,
                "spaceBetween": 30
            },
            "768": {
                "slidesPerView": 1,
                "spaceBetween": 30
            },
            "991": {
                "slidesPerView": 2,
                "spaceBetween": 30
            },
            "1199": {
                "slidesPerView": 2,
                "spaceBetween": 30
            },
            "1200": {
                "slidesPerView": 3,
                "spaceBetween": 30
            }
        }
      });

    });

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
    var postURL = window.location.href + "/post/" + uniquekey;
    window.open('https://www.facebook.com/sharer/sharer.php?u=' + postURL,
    'facebook-share-dialog',
    'width=800,height=600'
    );
    return false;
  });