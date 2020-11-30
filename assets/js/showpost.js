import $ from 'jquery';
import '../css/showPost.scss';

var fbButton = document.getElementById('fb-share-button');
var url = window.location.href;

var start_empty = document.getElementById("start_empty");
var start_checked = document.getElementById("start_checked");
var path = window.location.pathname;
var uniquekey = path.replace("/post/","");

if(fbButton != null){
    fbButton.addEventListener('click', function() {
        window.open('https://www.facebook.com/sharer/sharer.php?u=' + url,
            'facebook-share-dialog',
            'width=800,height=600'
        );
        return false;
    });
}

$('[id^="Add_Remove_favorite_"]').on('click', function(e){
    e.preventDefault();
    var uniquekey = this.id.replace("Add_Remove_favorite_","");
    var fav_section = document.getElementById('Favorive_icon');
    console.log(this.className);
    var url = "/ajax/add_favorite/"+uniquekey;

    var new_icon = document.createElement('i');
    var id_new_icon = "Add_Remove_favorite_" + uniquekey;
    var targetIcon = $(e.currentTarget);
    
    if(this.className === 'fa fa-star favorite'){
        targetIcon.removeClass('fa fa-star favorite')
                .addClass('far fa-star favorite')

        $.ajax(
            {
              url: url+"/0",
              method: 'POST'
            });
    }else{
        targetIcon.removeClass('far fa-star favorite')
                .addClass('fa fa-star favorite')

        $.ajax(
            {
              url: url+"/1",
              method: 'POST'
            });
    }

});





// Get the modal
var modal = document.getElementById("myModal");

// Get the image and insert it inside the modal - use its "alt" text as a caption
var img = document.getElementById("myImg");
var arrayAwards = document.querySelectorAll('*[id^="award"]');
var modalImg = document.getElementById("img01");

var ProofReceived = document.getElementById("ProofReceived");
var ArrayUpdateInfo = document.querySelectorAll('*[id^="updateInfo"]');

    if(ProofReceived != null){
        ProofReceived.onclick = function(){
            modal.style.display = "block";
            modalImg.src = this.src;
        }
    }

    if(img != null){
        img.onclick = function(){
            modal.style.display = "block";
            modalImg.src = this.src;
        }
    }

    if(arrayAwards.length !== 0){
        var a;
        var awards = [];
        for (a=1; a< arrayAwards.length+1; a++){
            awards[a-1] = document.getElementById("award"+a);
            awards[a-1].onclick = function(){
                modal.style.display = "block";
                modalImg.src = this.src;
            }
        }
    }

    if(ArrayUpdateInfo.length !== 0){
        var a;
        for (a=0; a< ArrayUpdateInfo.length; a++){

            ArrayUpdateInfo[a].onclick = function(){
                modal.style.display = "block";
                modalImg.src = this.src;
            }
        }
    }



// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}



// get the height of the left section
var section_left = document.getElementById("post_and_comment_section");

//Set max height for the timeline = max height of the left section
var timeline = document.getElementById('timeline_section');

if(timeline != null){
    timeline.style.maxHeight = section_left.offsetHeight+"px";
}
