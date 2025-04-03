(function (Drupal, drupalSettings) {
  "use strict";

  /**
   * Init function
   * Called when Drupal.Ndk.store is set (see the attach function in Drupal.behaviors.ccns)
   * @returns {Promise<void>}
   */
  const init = async () => {
    // Set event listener here
    console.log('submit-kind-39700')
  }
  Drupal.behaviors.kind_39700 = {
    attach: function (context, settings) {
      console.log('submit-kind-39700 attach here')
      //await init()
    }
  }
}) (Drupal, drupalSettings);
