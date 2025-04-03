(function (Drupal, drupalSettings) {
  "use strict";

  /**
   * Init function
   * Called when Drupal.Ndk.store is set (see the attach function in Drupal.behaviors.ccns)
   * @returns {Promise<void>}
   */
  const init = async () => {
    // Set event listener here
    console.log('submit-kind-1111')
  }
  Drupal.behaviors.kind_1111 = {
    attach: async function (context, settings) {
      await init()
    }
  }
}) (Drupal, drupalSettings);
