(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.login_options = {
    attach: function(context, settings) {
      if (document.getElementById('nostr-login-nip07') === null) {
        return
      }
      document.getElementById('nostr-login-nip07').addEventListener('click', async (e) => {
        try {
          const ndk = Drupal.Ndk.store.get('ndk')
          ndk.addExplicitRelay('wss://nostr.sebastix.dev')
          ndk.enableOutboxModel = true
          const nip07signer = Drupal.Ndk.store.get('nip07signer')
          ndk.signer = nip07signer
          await ndk.connect()
          const n = await nip07signer.user()
          Drupal.Ndk.store.set('npub', n.npub)
          const user = ndk.getUser({
            npub: n.npub
          })
          const profile = await user.fetchProfile()
          // Create user entity.
          const postData = {
            npub: n.npub,
            pubkey: user._pubkey,
            profile: profile
          }
          const created_user = await fetch('/create-user', {
            method: 'post',
            body: JSON.stringify(postData),
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            }
          })
          document.getElementById('nostr-auth').classList.remove('use-ajax');
          document.getElementById('nostr-auth').href = '/user/2';
          document.getElementById('nostr-auth').innerHTML = profile.name;
          // Close modal
          const $dialog = $('#drupal-off-canvas');
          if ($dialog.length) {
            await Drupal.dialog($dialog.get(0)).close();
            await $dialog.remove();
          }
          // Unbind dialogButtonsChange.
          $dialog.off('dialogButtonsChange');
          // Refresh page.
          location.reload();
        } catch (e) {
          console.log(e)
          alert(e)
        }
      })
    }
  }
})(jQuery, Drupal, drupalSettings);
