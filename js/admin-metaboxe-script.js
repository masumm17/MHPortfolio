(function($){
    $(document).ready(function(){
        $('.aymp-input').on('click', '.aymp-mediaupload', function(e){
            $thisB = $(this),
                $holder = $thisB.siblings( '.aymp-scthumbs' ).find('.aymp-sclist'),
                post_id = $( '#post_ID' ).val(),
                field_id = $thisB.attr( 'rel' ),
                multipleSelect = true
            
            if($thisB.data('multiple') == 'single'){
                multipleSelect = false
            }
            
            e.preventDefault();
            file_frame = wp.media.frames.file_frame = wp.media({
                title: 'Select File',
                button: {
                text: 'Use This File'
                },
                multiple: multipleSelect // Set to true to allow multiple files to be selected
            });
            // When an image is selected, run a callback.
            file_frame.on( 'select', function() {
                // We set multiple to true so can get mulitple image from the uploader
                attachments = file_frame.state().get('selection').toJSON();
                //$thisB.siblings('.aymp-media-field').val(attachment.url);
                if(attachments.length < 1){return;}
                for(var mi=0; mi<attachments.length; mi++){
                    html = '<li id="aympscid-' + attachments[mi].id + '" class="aymp-single-img">';
                    html += '<img src="' + attachments[mi].url + '" />';
                    html += '<div class="aymp-image-bar">';
                    html += '<a class="aymp-remove-image" href="#" data-field_id="' + field_id + '" data-attachment_id="' + attachments[mi].id + '">Remove</a>';
                    html += '</div>';
                    html += '<input type="hidden" name="' + field_id + '[]" value="' + attachments[mi].id + '" />';
                    html += '</li>';
                    if(multipleSelect){
                        $holder.append( $( html ) ).removeClass( 'hidden' );
                    }else{
                        $holder.html( $( html ) ).removeClass( 'hidden' );
                    }
                }
                
                //console.log(attachments);

                
            });
            file_frame.open();
            
            return false;
        });
        $('.aymp-input').on('click', '.aymp-remove-image', function(e){
            e.preventDefault();
            $(this).closest('li.aymp-single-img').fadeOut(300, function(e){
                $(this).remove();
            });
        });
        $('.aymp-shortable').sortable({ cursor: "move" });
    });
})(jQuery);