(function ($, Drupal, drupalSettings) {
  "use strict";

  const sleep = (s) =>
    new Promise((p) => setTimeout(p, (s * 1000) | 0))

  Drupal.behaviors.ccns = {
    // This function is called when the document is ready.
    attach: function(context, settings) {
      // Let's wrap this in a timeout here to make sure we get the Ndk.store...
      // @todo find a better solution with a watcher when Drupal.Ndk.store is defined
      setTimeout(  async () => {
        await sleep(2) // hacky...
        try {
          if (Drupal.Ndk.store === undefined) {
            throw 'Ndk store is not set'
          }
          // Crosspost link entity to Nostr
          let submitLinkForm = document.getElementById('node-link-form');
          if (submitLinkForm !== null && submitLinkForm.length) {
            submitLinkForm.addEventListener('submit', async (e) => {
              e.preventDefault();
              if (submitLinkForm.elements['crosspost_to_nostr'].checked === true) {
                // @todo show loading icon in an overlay while posting to Nostr
                // Get data for the content
                const title = submitLinkForm.elements['title[0][value]'].value;
                if (title === '') {
                  throw 'Title is empty'
                }
                const url = submitLinkForm.elements['field_url[0][uri]'].value;
                if (url === '') {
                  throw 'URL is empty'
                }
                const description = submitLinkForm.elements['body[0][value]'].value

                // Create event for Nostr
                const ndk = Drupal.Ndk.store.get('ndk')
                const signer = Drupal.Ndk.store.get('nip07signer')
                ndk.signer = signer;
                const nostrEvent = Drupal.Ndk.store.get('ndkEvent')
                nostrEvent.ndk = ndk;
                nostrEvent.kind = 1;
                let content = title + '\n' + url
                if (description !== '') {
                  content += '\n\n'
                  content += description
                }
                content += '\n\n'
                content += '- crossposted from https://ccns.sebastix.dev'
                nostrEvent.content = content
                nostrEvent.tags = [
                  ['client', 'CCNS']
                ];
                const n = await signer.user()
                await nostrEvent.toNostrEvent(n.npub);
                // @todo get user defined relays from user to post to
                // @todo how could this work, publish an event to own set of relay..?
                //const relaySet = Drupal.Ndk.store.get('relaySet')
                //let relay = Drupal.Ndk.store.get('relay')
                //relay.url = 'wss://nostr.sebastix.dev'
                //relaySet.addRelay(relay)
                //console.log(relaySet)
                // @todo try publishing a new kind: 13003 (a replaceable event) to my own relay
                const publishedEvents = await nostrEvent.publish()
                // @todo debug this further and show to which relays the event is published
                submitLinkForm.submit()
              } else {
                submitLinkForm.submit()
              }
            })
          }
        } catch (e) {
          console.log(e)
          alert(e)
        }
      }, 1000)
    }
  }
}) (jQuery, Drupal, drupalSettings);
