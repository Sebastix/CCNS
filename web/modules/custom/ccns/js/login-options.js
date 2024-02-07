(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.login_options = {
    attach: function(context, settings) {
      if (document.getElementById('nostr-login-nip07') === null) {
        return
      }
      document.getElementById('nostr-login-nip07').addEventListener('click', async (e) => {
        try {
          if (Drupal.Ndk.store === undefined) {
            throw 'Ndk store is not set'
          }
          const ndk = Drupal.Ndk.store.get('ndk')
          ndk.addExplicitRelay('wss://purplepage.es/')
          ndk.addExplicitRelay('wss://relay.nostr.band/')
          ndk.addExplicitRelay('wss://nostr.sebastix.dev/')
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
          });
          const created_user_response = await created_user.json();
          document.getElementById('nostr-auth').classList.remove('use-ajax');
          document.getElementById('nostr-auth').href = '/user/'+created_user_response.userid;
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
      document.getElementById('nostr-login-bunker').addEventListener('click', () => {
        alert('Not available yet')
      });
      document.getElementById('nostr-login-nsec').addEventListener('click', () => {
        alert('Sharing your privacy key (nsec) is not recommended. Not available yet.')
      });
    }
  }
})(jQuery, Drupal, drupalSettings);
