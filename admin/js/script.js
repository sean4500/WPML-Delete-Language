(function($){
  // Handle submit event
  $('body').on('submit', '#wpml-cleanup-form', function(e){
    e.preventDefault();
    $(this).append('<div class="wpml-cleanup-progress"><div class="progress-bar"></div><span>0%</span></div>').children('button').html('Working...Please be patient <span class="spinner is-active"></span>').removeClass('button-primary').attr('disabled', 'disabled');
    var data = $(this).serialize();
    requestCleanup(1, data);
  });

  // Requests cleanup each interation
  function requestCleanup(step, data){
    $.ajax({
      type: 'POST',
		  url: ajaxurl,
      data: {
        step: step,
        form: data,
        action: 'wpml_cleanup_language'
      },
      dataType: 'json',
      success: function(response){
        if(response.success){
          if(response.data.step == 'complete'){
            $('.progress-bar').animate({
              width: '100%'
            }, 75, function(){
              updatePercentNumber('Complete 100');
              runComplete();
            });
          } else {

            $('.progress-bar').animate({
              width: response.data.progress + '%'
            }, 75);

            updatePercentNumber(response.data.progress);
            requestCleanup(parseInt(response.data.step), data);
          }
        }
      }
    });

    // Updates percentage
    function updatePercentNumber(percentage){
      $('.wpml-cleanup-progress span').text(percentage + '%');
    };

    // Show complete state of UI
    function runComplete(){
      $('#wpml-cleanup-form button').hide();
      $('#wpml-cleanup-form').after('<a id="wpml-cleanup-continue" class="button button-primary" href="'+ window.location.href +'">Continue</a>');
    };
  }
})(jQuery);