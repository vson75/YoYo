import $ from 'jquery';


var addDoc = document.getElementById('Add_Document');
var removeDoc = document.getElementsByClassName('removeDocumentInput');

addDoc.addEventListener('click',
    function() {
    var i;
    for (i = 1; i < 10; i++) {
        var Id = 'awards_'+i;
        var last = i-1;
        var inputId = 'edit_document_organisation_Document'+i;
        var selectId = 'edit_document_organisation_DocType'+i;
        var x = document.getElementById(Id);
        if (x.style.display === "none") {
            x.style.display = "flex";
            document.getElementById(selectId).required = true;
            document.getElementById(inputId).required = true;
               
            break;
            }
        }
    }
);

var removeDoc = document.getElementById('Remove_Document');
removeDoc.addEventListener('click',
    function(){
        var i;
        for (i = 9; i > 0; i--) {
    
            var Id = 'awards_'+i;
            var last = i-1;
            var deleteId = 'delete'+last;
            var inputId = 'edit_document_organisation_Document'+i;
            var selectId = 'edit_document_organisation_DocType'+i;
            var x = document.getElementById(Id);
            if (x.style.display === "flex") {
    
                x.style.display = "none";
                x.value = '';
                document.getElementById(selectId).required = false;
                document.getElementById(inputId).required = false;
             //   x.remove();
                if(last !== 0){
                    document.getElementById(deleteId).style.display = "flex";
                }
    
                break;
            }
        }
    }
);


/*
removeDoc.addEventListener('click',
function(){
    var i;
    for (i = 9; i > 0; i--) {

        var Id = 'awards_'+i;
        var last = i-1;
        var deleteId = 'delete'+last;
        var inputId = 'edit_document_organisation_Document'+i;
        var selectId = 'edit_document_organisation_DocType'+i;
        var x = document.getElementById(Id);
        if (x.style.display === "flex") {

            x.style.display = "none";
            x.value = '';
            document.getElementById(selectId).required = false;
            document.getElementById(inputId).required = false;
         //   x.remove();
            if(last !== 0){
                document.getElementById(deleteId).style.display = "flex";
            }

            break;
        }
    }
}
);
*/
