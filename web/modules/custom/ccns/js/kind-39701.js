(function (Drupal, drupalSettings) {
  "use strict";

  /**
   * Init function
   * Called when Drupal.Ndk.store is set (see the attach function in Drupal.behaviors.ccns)
   * @returns {Promise<void>}
   */
  const init = async (context, settings) => {
    // TODO Set event listener here when a link is posted.

    try {
      if (Drupal.Ndk.store === undefined) {
        throw 'Ndk store is not set'
      }
      if (Drupal.Ndk.store.get('ndk') === undefined) {
        throw 'Ndk object in Ndk store is not set'
      }
      // If global feed, fetch events.
      if (settings.path.currentPath === 'global') {
        await fetchEvents()
      }
    } catch (e) {
      console.log(e)
      alert(e)
    }
  }

  const fetchEvents = async () => {
    // When all events are fetched, render them.
    const ndk = Drupal.Ndk.store.get('ndk')
    ndk.addExplicitRelay('wss://relay.damus.io/')
    ndk.addExplicitRelay('wss://relay.primal.net/')
    ndk.addExplicitRelay('wss://nos.lol/')
    await ndk.connect()
    // Limit results to ~50
    const kinds  = [39701]
    if (!drupalSettings.path.currentQuery) {
      kinds.push(39700)
    }
    const sub = ndk.subscribe({
      kinds: kinds,
      limit: 50 // this limit applies for each connected relay
    }, {})
    sub.on("event", (event) => {
      renderEvent(event)
    })
  }

  const renderEvent =  async (event) => {
    const ndk_feed = document.getElementsByClassName('feed')[0]
    // Copy element last card element
    const cards = ndk_feed.getElementsByClassName('card');
    let number_of_cards = cards.length
    let last_card = cards[number_of_cards-1]
    const new_card = last_card.cloneNode(true);
    new_card.dataset.createdAt = event.created_at
    // Add to DOM and apply sort on created_at value
    do {
      number_of_cards = number_of_cards -1
      last_card = cards[number_of_cards]
    } while (
      event.created_at > last_card.dataset.createdAt
    )
    // Insert new_card element after last_card element
    last_card.insertAdjacentElement('afterend', new_card)

    new_card.classList.remove('hidden')
    const card_body = new_card.getElementsByClassName('card-body')[0]
    const metadata = card_body.getElementsByClassName('metadata')[0]
    const dTag = getTag(event, 'd')
    // Kind 39700
    if (event.kind === 39700) {
      if (dTag[1].startsWith('http')) {
        card_body.getElementsByTagName('a')[0].href = dTag[1]
        card_body.getElementsByTagName('a')[0].innerHTML = dTag[1]
      } else {
        card_body.getElementsByTagName('a')[0].href = event.content
        card_body.getElementsByTagName('a')[0].innerHTML = event.content
      }
      if (dTag[1].startsWith('http')) {
        card_body.getElementsByClassName('content')[0].innerHTML = event.content
      } else {
        const description = getTag(event, 'description')
        if (description && description[1] !== '') {
          card_body.getElementsByClassName('content')[0].innerHTML = description[1]
        }
      }
    }
    // Kind 39701
    if (event.kind === 39701) {
      let scheme = (!dTag[1].startsWith('http')) ? 'https://' : ''
      const bookmarkUrl =  new URL(scheme +''+ dTag[1])
      card_body.getElementsByTagName('a')[0].href = bookmarkUrl.href
      card_body.getElementsByTagName('a')[0].innerHTML = scheme + dTag[1]
      card_body.getElementsByClassName('content')[0].innerHTML = event.content
    }
    // Remove skeleton classes
    card_body.getElementsByTagName('a')[0].classList.remove('skeleton')
    card_body.getElementsByClassName('content')[0].classList.remove('skeleton')
    // Fetch profile data
    const profile = await event.author.fetchProfile()
    metadata.getElementsByClassName('pubkey')[0].getElementsByClassName('value')[0].innerHTML = profile.name
    const pubkey = profile.pubkey !== undefined ? profile.pubkey : event.pubkey
    metadata.getElementsByClassName('pubkey')[0].getElementsByTagName('a')[0].setAttribute('href', 'https://npub.world/' + pubkey)
    metadata.getElementsByClassName('pubkey')[0].classList.remove('hidden')
    const created_at_date = new Date();
    created_at_date.setTime(event.created_at * 1000);
    let published_at = getTag(event, 'published_at')
    // When the bookmark event has been updated
    if (published_at && published_at[1] !== '') {
      if (event.created_at > published_at) {
        // Format timestamp string to timestamp int in milliseconds
        const published_at_date = new Date()
        published_at_date.setTime(published_at * 1000)
        metadata.getElementsByClassName('created-at')[0].getElementsByClassName('value')[0].innerHTML = published_at_date.toUTCString()
        metadata.getElementsByClassName('published-at')[0].innerHTML = ', updated at: ' + created_at_date.toUTCString()
      }
    } else {
      metadata.getElementsByClassName('created-at')[0].getElementsByClassName('value')[0].innerHTML = created_at_date.toUTCString()
    }
    metadata.getElementsByClassName('created-at')[0].classList.remove('hidden')
    metadata.getElementsByClassName('published-at')[0].classList.remove('hidden')
    metadata.classList.remove('skeleton')
    const tags = card_body.getElementsByClassName('tags')[0]
    for (const tag of event.tags) {
      if (tag[0] === 't') {
        // Add hashtag to card.
        const tagBadge = document.createElement('div')
        tagBadge.classList.add('badge', 'text-xs')
        tagBadge.innerHTML = '<a href="https://nostrarchives.com/search?q=%23' + tag[1] + '" target="_blank">#' + tag[1] + '</a>'
        tags.appendChild(tagBadge)
      }
      if (tag[0] === 'client') {
        metadata.getElementsByClassName('client-tag')[0].getElementsByClassName('value')[0].innerHTML = tag[1]
        metadata.getElementsByClassName('client-tag')[0].classList.remove('hidden')
      }
    }
    card_body.getElementsByClassName('event-kind')[0].innerHTML = 'kind: <code>' + event.kind + '</code>'
    card_body.getElementsByClassName('event-id')[0].innerHTML = 'id: <a href="https://nostrver.se/e/'+event.id+'" target="_blank">'+event.id+'</a>'
    // TODO fetch reactions

    // TODO fetch comments

    // TODO fetch zaps

  }

  /**
   *
   * @param event
   * @param tagKey
   * @returns {[string,*]|string[]|[string,*]|*|string|boolean|any}
   */
  const getTag = (event, tagKey) => {
    if (event.tags) {
      for (const tag of event.tags) {
        if (tag[0] === tagKey) {
          return tag
        }
      }
    }
  }

  Drupal.behaviors.kind_39700 = {
    attach: async function (context, settings) {
      function checkNdkStore() {
        const check = setInterval(async () => {
          console.log('CheckNdkStore in kind-39701.js')
          if(Drupal.Ndk.store !== undefined) {
            // Clear this interval
            await clearInterval(check);
            // init
            await init(context, settings);
          }
        }, 100);
      }
      checkNdkStore()
    }
  }
}) (Drupal, drupalSettings);
