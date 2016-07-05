function update_simple_ordering_callback(response) {
  alert(response);

  console.log( response );

  if ( 'children' === response ) {
    window.location.reload();
    return;
  }

  // Object returned from the PHP script
  var changes = jQuery.parseJSON( response );

  var new_pos = changes.new_pos;
  for ( var key in new_pos ) {
    if ( 'next' === key ) {
      continue;
    }

    var inline_key = document.getElementById('inline_' + key);
    if ( null !== inline_key && new_pos.hasOwnProperty(key) ) {
      var dom_menu_order = inline_key.querySelector('.menu_order');

      if ( undefined !== new_pos[key]['menu_order'] ) {
        if ( null !== dom_menu_order ) {
          dom_menu_order.innerHTML = new_pos[key]['menu_order'];
        }

        var dom_post_parent = inline_key.querySelector('.post_parent');
        if ( null !== dom_post_parent ) {
          dom_post_parent.innerHTML = new_pos[key]['post_parent'];
        }

        var post_title = null;
        var dom_post_title = inline_key.querySelector('.post_title');
        if ( null !== dom_post_title ) {
          post_title = dom_post_title.innerHTML;
        }

        var dashes = 0;
        while ( dashes < new_pos[key]['depth'] ) {
          post_title = '&mdash; ' + post_title;
          dashes++;
        }
        var dom_row_title = inline_key.parentNode.querySelector('.row-title');
        if ( null !== dom_row_title && null !== post_title ) {
          dom_row_title.innerHTML = post_title;
        }
      } else if ( null !== dom_menu_order ) {
        dom_menu_order.innerHTML = new_pos[key];
      }
    }
  }

  if ( changes.next ) {

    jQuery.post( ajaxurl, {
      action: 'organise_posts_term_screen',
      id: changes.next['id'],
      previd: changes.next['previd'],
      nextid: changes.next['nextid'],
      start: changes.next['start'],
      excluded: changes.next['excluded']
    }, update_simple_ordering_callback );

  } else {

    jQuery('.spo-updating-row').removeClass('spo-updating-row');
    sortable_post_table.removeClass('spo-updating').sortable('enable');

  }

}

var sortable_post_table = jQuery(".wp-list-table tbody");
sortable_post_table.sortable({

  items: '> tr',                // The sortable elements.
  cursor: 'move',               // The cursor shown when sorting.
  axis: 'y',                    // Limit dragging to vertical only.
  containment: 'table.widefat', // Defines a bounding box that sortable elements are constrained to - e.g. 'element.selector'.
  cancel: '.inline-edit-row',   // Prevents sorting if you start on elements matching the selector.
  distance: 2,                  // Sorting will not start until mouse is dragged beyond distance (px).
  opacity: .8,                  // Opacity of helper while sorting.
  tolerance: 'pointer',         // mode to use for testing whether the item being moved is hovering over another item.
  start: function(e, ui) {      // start is an event trigerred when sorting starts.

    if ( typeof(inlineEditPost) !== 'undefined' ) { // This closes the open quick edit view.
      inlineEditPost.revert();
    }
    ui.placeholder.height(ui.item.height());

  },
  helper: function(e, ui) {     // Allows a helper element to be used for dragging display.

    var children = ui.children();
    for ( var i=0; i<children.length; i++ ) {

      var selector = jQuery(children[i]);
      selector.width( selector.width() );

    };

    return ui;

  },
  stop: function(e, ui) {       // Event trigerred when sorting has stopped.
    // remove fixed widths
    ui.item.children().css('width','');
  },
  update: function(e, ui) {     // Event triggered when the user has stopped sorting and the DOM position has changed.

    sortable_post_table.sortable('disable').addClass('spo-updating'); // Disables the sortable, adds a class
    ui.item.addClass('spo-updating-row');

    var postid = ui.item[0].id.substr(5);         // Moved post id
    var prevpostid = false;                       // previous post id defaults to false (e.g. first item)
    var prevpost = ui.item.prev();                // previous post row of the MOVED item
    if ( prevpost.length > 0 ) {                  // If there is a previous post, set prevpostid
      prevpostid = prevpost.attr('id').substr(5);
    }
    var nextpostid = false;                       // Next post ID defaults to false (e.g. last item)
    var nextpost = ui.item.next();                // If there is a next item, set nextpostid
    if ( nextpost.length > 0 ) {
      nextpostid = nextpost.attr('id').substr(5);

    }
    //nextpostid = ( nextpost.length > 0 ) ? nextpost.attr('id').substr(5) : NULL;

    // go do the sorting stuff via ajax
    jQuery.post( ajaxurl, {
      action: 'organise_posts_term_screen',
      id: postid,
      previd: prevpostid,
      nextid: nextpostid
    }, update_simple_ordering_callback );

    // fix cell colors
    var table_rows = document.querySelectorAll('tr.iedit'),
      table_row_count = table_rows.length;
    while( table_row_count-- ) {
      if ( 0 === table_row_count%2 ) {
        jQuery(table_rows[table_row_count]).addClass('alternate');
      } else {
        jQuery(table_rows[table_row_count]).removeClass('alternate');
      }
    }
  }
});
