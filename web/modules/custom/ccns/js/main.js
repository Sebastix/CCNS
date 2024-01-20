(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.ccns = {
    // This function is called when the document is ready.
    attach: function(context, settings) {
      console.log('ccns ready');
      // @todo create watcher when Drupal.Ndk.store is not null
      console.log(Drupal.Ndk.store)

      if (document.getElementById('nostr-login') === null) {
        return
      }
      document.getElementById('nostr-login').addEventListener('click', async (e) => {
        try {
          // @todo clean up this code
        } catch (e) {
          console.log(e)
          alert(e)
        }

      })
    }
  }
}) (jQuery, Drupal, drupalSettings);
