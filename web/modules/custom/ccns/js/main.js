(function ($, Drupal, drupalSettings) {
  "use strict";
  Drupal.behaviors.ccns = {

    // This function is called when the document is ready.
    attach: function(context, settings) {
      console.log('ccns ready');

      document.getElementById('nostr-login').addEventListener('click', function (e) {
        // @todo get NdkStore instance here...
        console.log('open dialog');

      })
    }
  }
}) (jQuery, Drupal, drupalSettings);
