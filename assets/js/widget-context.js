jQuery(document).ready(function($) {

	$('.widgets-sortables').on('click', '.expand-widget-context', function( event ) {

      event.preventDefault();

      $(this).toggleClass('expanded');
      var txt = $(this).hasClass('expanded') ? 'Hide' : 'Show';
      $(this).text( txt );
      var el = $(this).data('show');
      $(this).parent().parent().find( '.' + el ).slideToggle();

  });

});