jQuery(function() {
    function messaging_split( val ) {
      return val.split( /,\s*/ );
    }
    function messaging_extractLast( term ) {
      return messaging_split( term ).pop();
    }

    jQuery( ".messaging-suggest-user" )
      // don't navigate away from the field on tab when selecting an item
      .bind( "keydown", function( event ) {
        if ( event.keyCode === jQuery.ui.keyCode.TAB &&
            jQuery( this ).data( "ui-autocomplete" ).menu.active ) {
          event.preventDefault();
        }
      })
      .autocomplete({
        source: function( request, response ) {
          jQuery.getJSON(ajaxurl, {
            action: 'messaging_suggest_user',
            user: messaging_extractLast( request.term )
          }, response );
        },
        search: function() {
          // custom minLength
          var term = messaging_extractLast( this.value );
          if ( term.length < 2 ) {
            return false;
          }
        },
        focus: function() {
          // prevent value inserted on focus
          return false;
        },
        select: function( event, ui ) {
          var terms = messaging_split( this.value );
          // remove the current input
          terms.pop();
          // add the selected item
          terms.push( ui.item.value );
          // add placeholder to get the comma-and-space at the end
          terms.push( "" );
          this.value = terms.join( ", " );
          return false;
        }
      });
});