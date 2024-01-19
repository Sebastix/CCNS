(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.ccns = {
    // This function is called when the document is ready.
    attach: function(context, settings) {
      console.log('ccns ready');
      // @todo create watcher when Drupal.Ndk.store is not null
      console.log(Drupal.Ndk.store)

      document.getElementById('nostr-login').addEventListener('click', async function (e) {
        try {
          const ndk = Drupal.Ndk.store.get('ndk')
          ndk.addExplicitRelay('wss://nostr.sebastix.dev')
          ndk.enableOutboxModel = true
          const nip07signer = Drupal.Ndk.store.get('nip07signer')
          ndk.signer = nip07signer
          await ndk.connect()
          nip07signer.user().then(async (user) => {
            if (!!user.npub) {
              console.log("Permission granted to read their public key:", user.npub)
              Drupal.Ndk.store.set('npub', user.npub)
              const u = ndk.getUser({
                npub: user.npub
              })
              await u.fetchProfile()
              console.log(u.profile)
              alert('Welcome ' + u.profile.name + '!')
              // @todo create user entity
            }
          });
        } catch (e) {
          console.log(e)
          alert(e)
        }
      })
    }
  }
}) (jQuery, Drupal, drupalSettings);
