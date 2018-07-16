if (typeof APP === 'undefined') {
    APP = {};
}

(function(window, $) {
APP.submitFeedbackForm = function() {
    var $form = $(this);
    var $contact = $form.find('[name="contact"]');
    var $message = $form.find('[name="message"]');
    var contact = $.trim($contact.val());
    var message = $.trim($message.val());
    var error = false;
    var $result_text = $form.find('.result-text');
    var $result_image = $form.find('.result-image');
    if (contact === '') {
        $contact.addClass('error');
        error = true;
    }
    if (message === '') {
        $message.addClass('error');
        error = true;
    }
    if (error) {
        return false;
    }
    var data = $form.serialize();
    $.ajax({
        type: 'POST',
        url: 'action.tremulous_feedback',
        data: data,
        success: function(response) {
            $result_text.html('<p>Благодарим. Лучшее выложим. С худшими свяжемся. Мысленно с вами. Sell The World.</p>')
                .removeClass('none').fadeIn();
            $result_image.removeClass('none').fadeIn();
        },
        error: function(jqXHR, textStatus, errorThrown) {            
            //$result_text.html('<div style="width: 100%; height: 100%;">'+ jqXHR.responseText +'</div>').removeClass('none').fadeIn();
        }
    }).fail(function() {
        
    });

    return false;
}

APP.fieldOnChange = function() {
    var $this = $(this);    
    if ($this.val() !== '') {
        $this.removeClass('error');
    }
}

function placeImagesCorrectly() {
    var width = $('body').width();
    if (width >= 616) {        
        $('.questions-1__image-1').detach().insertBefore($('.questions-1 .question:nth-child(1)'));
    }
    if (width >= 768) {
        $('.answers-1__image-2').detach().insertAfter($('.answers-1 .answer:nth-child(2)'));
    }
}

$(document).ready(function(){
    placeImagesCorrectly();
});


})(window, jQuery)