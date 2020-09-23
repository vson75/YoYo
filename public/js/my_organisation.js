       // déclarer object DeleteDocumentApp
       var DeleteDocumentApp = {
        initialize: function(divDoc){
            this.divDoc = divDoc;

            this.divDoc.find('.js-delete-document').on(
                'click',
                this.handleDeleteDocument
                );

            this.divDoc.find('.col-md-6').on(
                'click',
                this.handleColMd6Click
                );
        },
               
        handleDeleteDocument: function(event) {
            event.preventDefault();
            $(this).addClass('text-danger');
            $(this).find('.fa')
                    .removeClass('fa-trash')
                    .addClass('fa-spinner')
                    .addClass('fa-spin');

            var deleteUrl = $(this).data('url');
            // find the closest class= "row"
            var div = $(this).closest('.row');

            $.ajax({
                url: deleteUrl,
                method: "POST",
                success: function(){
                    div.fadeOut('normal', function(){
                        div.remove();
                    });
                }
            });
        },

        handleColMd6Click : function(){
            console.log("click into div");
        },


    };


