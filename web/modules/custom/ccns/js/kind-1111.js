(function (Drupal, drupalSettings) {
  "use strict";

  /**
   * Init function
   * Called when Drupal.Ndk.store is set (see the attach function in Drupal.behaviors.ccns).
   *
   * @returns {Promise<void>}
   */
  const init = async () => {
    // TODO Set event listener here when a comment is posted.
    // Temp disable submit button of form
    const comment_form = document.getElementById('comment-form');
    if (comment_form) {
      const submit = comment_form.querySelector('[type="submit"]')
      submit.disabled = true
      //console.log(submit)
    }
  }
  Drupal.behaviors.kind_1111 = {
    attach: async function (context, settings) {
      await init()
    }
  }
}) (Drupal, drupalSettings);
