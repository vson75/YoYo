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



function checkfavorite(){

    if(start_checked.style.display === "none"){
        start_checked.style.display = "flex";
        start_empty.style.display = "none";
    }
    $.ajax({
        cache: false,
        url: '/ajax/add_favorite/'+uniquekey+'/1',
        method: 'POST',
    })

}

function uncheckFavorite(){
    if(start_checked.style.display === "flex"){
        start_checked.style.display = "none";
        start_empty.style.display = "flex";
    }
    $.ajax({
        cache: false,
        url: '/ajax/add_favorite/'+uniquekey+'/0',
        method: 'POST',
    })
}


// Get the modal
var modal = document.getElementById("myModal");

// Get the image and insert it inside the modal - use its "alt" text as a caption
var img = document.getElementById("myImg");
var arrayAwards = document.querySelectorAll('*[id^="award"]');
var modalImg = document.getElementById("img01");

var ProofReceived = document.getElementById("ProofReceived");

    if(ProofReceived != null){
        ProofReceived.onclick = function(){
            modal.style.display = "block";
            modalImg.src = this.src;
        }
    }

    if(img != null){
        img.onclick = function(){
            console.log("2323");
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



// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}
