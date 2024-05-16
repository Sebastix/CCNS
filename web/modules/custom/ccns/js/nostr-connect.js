(function ($, Drupal, drupalSettings) {
  "use strict";

  /**
   * Init function
   * Called when Drupal.Ndk.store is set (see the attach function in Drupal.behaviors.ccns)
   * @returns {Promise<void>}
   */
  const init = async () => {
    try {
      if (Drupal.Ndk.store === undefined) {
        throw 'Ndk store is not set'
      }
      if (Drupal.Ndk.store.get('ndk') === undefined) {
        throw 'Ndk object in Ndk store is not set'
      }
      const ndk = Drupal.Ndk.store.get('ndk')
      // Crosspost link entity to Nostr
      let submitLinkForm = document.getElementById('node-link-form');
      if (submitLinkForm !== null && submitLinkForm.length) {
        // Preview checkbox listener.
        submitLinkForm.elements['crosspost_to_nostr'].addEventListener('click', (e) => {
          const title = submitLinkForm.elements['title[0][value]'].value
          const url = submitLinkForm.elements['field_url[0][uri]'].value
          const description = submitLinkForm.elements['body[0][value]'].value
          let contentHtml = title + '\n' + url
          if (description !== '') {
            contentHtml += '<br /><br />'
            contentHtml += description
          }
          contentHtml += '<br /><br />'
          contentHtml += '🔂 cross-posted from https://ccns.nostrver.se'
          // Generate a preview and insert this into the DOM.
          let preview = document.createElement('div')
          preview.id = 'previewEventKind1'
          preview.classList.add('my-2', 'p-2', 'bg-base-200')
          preview.insertAdjacentHTML('afterbegin', contentHtml)
          if (document.getElementById('previewEventKind1') && e.target.checked === false) {
            // Remove element.
            document.getElementById('previewEventKind1').remove()
          } else {
            // Add preview to DOM.
            document.getElementsByClassName('form-item-crosspost-to-nostr').item(0).after(preview)
          }
        })
        // Form input change listener.
        submitLinkForm.addEventListener('change', (e) => {
          if (e.target.name !== 'crosspost_to_nostr') {
            // Remove preview element.
            document.getElementById('previewEventKind1').remove()
            // Uncheck cross-post checkbox.
            submitLinkForm.elements['crosspost_to_nostr'].checked = false
          }
        })
        // Form submit listener.
        submitLinkForm.addEventListener('submit', async (e) => {
          e.preventDefault();
          if (submitLinkForm.elements['crosspost_to_nostr'].checked === true) {
            // Show loading icon + text besides the submit button while transmitting the note to Nostr.
            document.getElementById('edit-actions').insertAdjacentHTML('beforeend', '<span class="loading loading-spinner loading-xs text-warning"></span> <code class="text-warning">transmitting note</code>')
            // Get data for the content
            const title = submitLinkForm.elements['title[0][value]'].value
            if (title === '') {
              throw 'Title is empty'
            }
            const url = submitLinkForm.elements['field_url[0][uri]'].value
            if (url === '') {
              throw 'URL is empty'
            }
            const description = submitLinkForm.elements['body[0][value]'].value
            // Create event for Nostr
            const signer = Drupal.Ndk.store.get('nip07signer')
            if (signer === undefined) {
              throw 'signer in Ndk store is not set'
            }
            ndk.signer = signer;
            const nostrEvent = Drupal.Ndk.store.get('ndkEvent')
            if (nostrEvent === undefined) {
              throw 'ndkEvent in Ndk store is not set'
            }
            nostrEvent.ndk = ndk;
            nostrEvent.kind = 1;
            let content = title + '\n' + url
            if (description !== '') {
              content += '\n\n'
              content += description
            }
            content += '\n\n'
            content += '🔂 cross-posted from https://ccns.nostrver.se'
            nostrEvent.content = content
            nostrEvent.tags = [
              ['client', 'CCNS']
            ];
            const nUser = await signer.user()
            const n = await nostrEvent.toNostrEvent(nUser.npub);
            // @todo get user defined relays from user to post to (enable outbox model on ndk)
            // ndk.enableOutboxModel = true
            // @todo how could this work, publish an event to own set of relays...?
            //const relaySet = Drupal.Ndk.store.get('relaySet')
            //let relay = Drupal.Ndk.store.get('relay')
            //relay.url = 'wss://nostr.sebastix.dev'
            //relaySet.addRelay(relay)
            //console.log(relaySet)
            // @todo try publishing a new kind: 13003 (a replaceable event) to my own relay
            // ...
            console.log('ready to publish')
            console.log(n)
            // @todo debug this further and show to which relays the event is published
            const eventPublishedToRelays = await nostrEvent.publish()
            console.log(`The event is published to ${eventPublishedToRelays.size} relays:`)
            // Loop over all relays
            /**
             * @var {NDKRelay} relay
             */
            for (const relay in eventPublishedToRelays) {
              console.log(relay)
            }
            // @todo save this published event as a reference to the created link entity in Drupal
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
  }

  Drupal.behaviors.ccns = {
    // This function is called when the document is ready.
    attach: function(context, settings) {
      // @todo find a better solution than polling with a watcher / observer when Drupal.Ndk.store is defined
      function checkNdkStore() {
        const check = setInterval(async () => {
          console.log('CheckNdkStore...')
          if(Drupal.Ndk.store !== undefined) {
            // Clear this interval
            await clearInterval(check);
            // init
            await init();
          }
        }, 100);
      }
      checkNdkStore()
    },
  }

}) (jQuery, Drupal, drupalSettings);
