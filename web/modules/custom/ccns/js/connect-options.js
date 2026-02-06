(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.connect_options = {
    attach: async function(context, settings) {

      if (once('drupal-off-canvas', 'html').length) {
        $(window).on();
        console.log('off-canvas once called')
      }

      if (document.getElementById('nostr-login-nip07') === null) {
        return
      }

      if(isPWA() && isIOS()) {
        document.getElementById('nostr-login-nip07').classList.add('disabled');
        document.getElementById('nostr-login-nip07').style.cursor = '';
        document.getElementById('nostr-login-nip07').style.opacity = '.25';
        // Show Drupal login link
        document.getElementById('drupal-login').classList.remove('hidden')
      }

      document.getElementById('nostr-login-nip07').addEventListener('click', async (e) => {
        try {
          // @todo show loading icon in an overlay while connecting..
          if (Drupal.Ndk.store === undefined) {
            throw 'Ndk store is not set'
          }
          const ndk = Drupal.Ndk.store.get('ndk')
          // ndk.addExplicitRelay('wss://purplepage.es/')
          // ndk.addExplicitRelay('wss://nostr.sebastix.dev/')
          // ndk.enableOutboxModel = true // enabling this will make connecting much slower.
          const nip07signer = Drupal.Ndk.store.get('nip07signer')
          ndk.signer = nip07signer
          await ndk.connect()
          /** @var {NDKUser} user */
          const user = await nip07signer.user()
          // Fetch profile of user.
          /** @var {NDKUserProfile} profile */
          const profile = await user.fetchProfile()
          //const profile = await requestProfile(user.pubkey)
          // Create user entity.
          const postData = {
            npub: user.npub,
            pubkey: user.pubkey,
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

  /**
   * Request profile with NDKs fetchEvent.
   *
   * @param pubkey
   * @returns {Promise<unknown>}
   */
  function requestProfile(pubkey) {
    return new Promise( async (resolve, reject) => {
      const ndk = Drupal.Ndk.store.get('ndk')
      const filter = {
        kinds: [0],
        authors: [pubkey]
      }
      const profile = await ndk.fetchEvent(filter)
      resolve(profile)
    }).then((res) => {
      return res
    })
  }

  /**
   * Detect if page is opened as progressive web app.
   *
   * @returns {boolean}
   */
  const isPWA = () => {
    return ["fullscreen", "standalone", "minimal-ui"].some(
      (displayMode) => window.matchMedia('(display-mode: ' + displayMode + ')').matches
    );
  }

  const getUserAgent = () => {
    return navigator.userAgent;
  }

  // Detects if device is iOS
  const isIOS = () => {
    const userAgent = window.navigator.userAgent.toLowerCase();
    return /iphone|ipad|ipod/.test( userAgent );
  }

})(jQuery, Drupal, drupalSettings);
