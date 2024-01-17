(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.ccns = {
    // This function is called when the document is ready.
    attach: function(context, settings) {
      console.log('ccns ready');
      // @todo create watcher when Drupal.Ndk.store is not null
      console.log(Drupal.Ndk.store)

      document.getElementById('nostr-login').addEventListener('click', async function (e) {
        const ndkInstance = Drupal.Ndk.store.get('ndk');
        const nip07signer = Drupal.Ndk.store.get('nip07signer');
        const ndk = new ndkInstance({
          explicitRelayUrls: ["wss://nostr.sebastix.dev"],
          enableOutboxModel: true,
        });
        ndk.signer = nip07signer;
        await ndk.connect();
        nip07signer.user().then(async (user) => {
          if (!!user.npub) {
            console.log("Permission granted to read their public key:", user.npub);
            Drupal.Ndk.store.set('npub', user.npub);
            // @todo create user entity
          }
        });
        console.log('open dialog...');
      })
    }
  }
}) (jQuery, Drupal, drupalSettings);
