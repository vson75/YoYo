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


$('[id^="fb-share-"]').on('click', function(){
    var uniquekey = this.id.replace("fb-share-","");
    //console.log(uniquekey);
    var postURL = window.location.origin + "/post/" + uniquekey;
    //console.log(postURL);
    window.open('https://www.facebook.com/sharer/sharer.php?u=' + postURL,
    'facebook-share-dialog',
    'width=800,height=600'
    );
    return false;
  });


$('[id^="addFav_"]').on('click', function(e){
  e.preventDefault();
  var uniquekey = this.id.replace("addFav_","");
  var target_node = document.getElementById("addFav_"+uniquekey);
  //console.log(target_node);
  var tagetDiv = $(e.currentTarget);
  var url = "ajax/add_favorite/"+uniquekey;
  ///console.log(x);
  // check if user already add favorite this post
  if(tagetDiv.attr('class') === 'fas fa-star'){

      var node_element = document.getElementById("remove_fav_section_"+ uniquekey);
      var old_div = document.getElementById("txt_remove_fav_"+uniquekey);
    // tagetDiv.closest(".txt_add_fav");
      old_div.remove();
      console.log(target_node);
      var id_new_div = "txt_add_fav_" + uniquekey;
      var new_div = document.createElement('div');
      new_div.setAttribute("id",id_new_div);
      new_div.setAttribute("class", "txt_add_fav");
      new_div.innerHTML = 'Thích dự án';
      node_element.insertBefore(new_div,target_node);
      node_element.setAttribute('id', "add_fav_section_"+ uniquekey);
      tagetDiv.removeClass('fas fa-star')
      .addClass('far fa-star');

      $.ajax(
      {
        url: url+"/0",
        method: 'POST'
      });
    }else{
      
      var node_element = document.getElementById("add_fav_section_"+ uniquekey);
      var old_div = document.getElementById("txt_add_fav_"+uniquekey);
      //console.log(parent_div);


    // tagetDiv.closest(".txt_add_fav");
      old_div.remove();
      console.log(target_node);
      var id_new_div = "txt_remove_fav_" + uniquekey;
      var new_div = document.createElement('div');
      new_div.setAttribute("id",id_new_div);
      new_div.setAttribute("class", "txt_add_fav");
      new_div.innerHTML = 'Huỷ thích dự án';
      node_element.insertBefore(new_div,target_node);
      node_element.setAttribute('id', "remove_fav_section_"+ uniquekey);

      tagetDiv.removeClass('far fa-star')
                        .addClass('fas fa-star');
      $.ajax(
      {
        url: url+"/1",
        method: 'POST'
      });
    }
});