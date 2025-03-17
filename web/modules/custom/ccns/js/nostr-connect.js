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
          let contentHtml = generate_nostr_note_preview(url, title, description)
          // Replace \n to <br />
          contentHtml = contentHtml.replace(/[?\n]/g, '<br />')
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
            if (document.getElementById('previewEventKind1')) {
              const title = submitLinkForm.elements['title[0][value]'].value
              const url = submitLinkForm.elements['field_url[0][uri]'].value
              const description = submitLinkForm.elements['body[0][value]'].value
              let contentHtml = generate_nostr_note_preview(url, title, description)
              // Replace \n to <br />
              contentHtml = contentHtml.replace(/[?\n]/g, '<br />')
              document.getElementById('previewEventKind1').innerHTML = contentHtml;
            }
          }
        })
        // Form submit listener.
        submitLinkForm.addEventListener('submit', async (e) => {
          e.preventDefault();
          // Show loading icon + text besides the submit button while transmitting the note to Nostr.
          document.getElementById('edit-submit').innerHTML = '<span class="loading loading-spinner loading-xs text-warning"></span> <code class="text-warning">transmitting</code>';
          const signer = Drupal.Ndk.store.get('nip07signer')
          if (signer === undefined) {
            throw 'signer in Ndk store is not set'
          }
          ndk.signer = signer
          // Create Nostr event kind 39700
          const nostrEventKind39700 = Drupal.Ndk.store.get('ndkEvent')
          nostrEventKind39700.ndk = ndk
          nostrEventKind39700.kind = 39700
          nostrEventKind39700.content = submitLinkForm.elements['field_url[0][uri]'].value;
          nostrEventKind39700.tags = [
            ['description', submitLinkForm.elements['body[0][value]'].value],
            ['d', 'ccns-' + string_to_slug(submitLinkForm.elements['title[0][value]'].value)],
            ['client', 'CCNS'],
            ['t', 'CCNS']
          ]
          const nUser = await signer.user()
          const n = await nostrEventKind39700.toNostrEvent(nUser.npub)
          const event39700PublishedToRelays = await nostrEventKind39700.publish()
          console.log(`The 39700 event is published to ${event39700PublishedToRelays.size} relays:`)
          // Set event id in form to be saved to the created entity.
          submitLinkForm.elements['field_nostr_event_id[0][value]'].value = nostrEventKind39700.id

          if (submitLinkForm.elements['crosspost_to_nostr'].checked === true) {
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
            const ndk = Drupal.Ndk.store.get('ndk')
            ndk.signer = signer
            // Create note event for Nostr
            const nostrEvent = Drupal.Ndk.store.get('ndkEvent')
            if (nostrEvent === undefined) {
              throw 'ndkEvent in Ndk store is not set'
            }
            nostrEvent.ndk = ndk
            nostrEvent.kind = 1
            nostrEvent.content = generate_nostr_note_preview(url, title, description)
            nostrEvent.tags = [
              ['client', 'CCNS']
            ]
            await nostrEvent.sign(signer)
            // const nUser = await signer.user()
            // const n = await nostrEvent.toNostrEvent(nUser.npub)
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
            console.log('ready to publish note kind 1')
            //console.log(n)
            // @todo debug this further and show to which relays the event is published
            // DO we have relays set where the event can be transmitted to?
            const eventPublishedToRelays = await nostrEvent.publish()
            console.log(`The event is published to ${eventPublishedToRelays.size} relays:`)
            // Loop over all relays
            /**
             * @var {NDKRelay} relay
             */
            for (const relay in nostrEvent.eventPublishedToRelays) {
              console.log(relay)
            }
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

  const string_to_slug = (str) => {
    return str
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '')
      .replace(/[\s_-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  const generate_nostr_note_preview = (url, title, description = '') => {
    let content = url + '\n\n' + title
    if (description !== '') {
      content += '\n'
      content += description
    }
    content += '\n\n'
    content += '🔂 cross-posted from https://ccns.nostrver.se'
    return content
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
