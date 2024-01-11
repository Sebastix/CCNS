/**
 * @file
 * Modifies the Rate thumbsup rating.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.ThumbsUpRating = {
    attach: function (context, settings) {
      $('body').find('.thumbsup-rating-wrapper').each(function () {
        // If element is editable, enable submit click.
        var isEdit = $(this).attr('can-edit');
        if (isEdit === 'true') {
          $(this).find('label')
            .click(function (e) {
              $(this).find('input').prop('checked', true);
              $(this).closest('form').find('.thumbsup-rating-submit').trigger('click');
            });
        }
        else {
          $(this).find('label').css('cursor', 'default'); // Cursor to arrow.
        }
      });
    }
  };
})(jQuery, Drupal);
