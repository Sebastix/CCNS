(function (Drupal, drupalSettings) {
  "use strict";

  /**
   * Init function
   * Called when Drupal.Ndk.store is set (see the attach function in Drupal.behaviors.ccns)
   * @returns {Promise<void>}
   */
  const init = async () => {
    // TODO Set event listener here when a link is posted.

    // If global feed, fetch events.
    try {
      if (Drupal.Ndk.store === undefined) {
        throw 'Ndk store is not set'
      }
      if (Drupal.Ndk.store.get('ndk') === undefined) {
        throw 'Ndk object in Ndk store is not set'
      }
      await fetchEvents()
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
    // TODO limit results to ~50
    const sub = ndk.subscribe({
      kinds: [39700, 397001],
      limit: 50 // this limit applies for each connected relay
    }, {})
    sub.on("event", (event) => {
      renderEvent(event)
    })
    // TODO keep websocket connection active to process new events?
  }

  const renderEvent =  async (event) => {
    // Copy element last card element
    const cards = document.getElementsByClassName('card');
    const last_card = cards[cards.length-1]
    const new_card = last_card.cloneNode(true);
    const feed = document.getElementsByClassName('feed')[0]
    // Add to DOM
    feed.append(new_card)
    new_card.classList.remove('hidden')
    const card_body = new_card.getElementsByClassName('card-body')[0]
    const metadata = card_body.getElementsByClassName('metadata')[0]
    // Replace contents
    const dTag = getTag(event, 'd')
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
    // Remove skeleton classes
    card_body.getElementsByTagName('a')[0].classList.remove('skeleton')
    card_body.getElementsByClassName('content')[0].classList.remove('skeleton')
    // Fetch profile data
    const profile = await event.author.fetchProfile()
    metadata.getElementsByClassName('pubkey')[0].innerHTML = profile.name
    const created_at_date = new Date();
    created_at_date.setTime(event.created_at*1000);
    metadata.getElementsByClassName('created-at')[0].innerHTML = ' saved on ' + created_at_date.toUTCString()
    const published_at = getTag(event, 'published_at')
    if (published_at) {
      metadata.getElementsByClassName('published-at')[0].innerHTML = ', updated at: ' + published_at.toUTCString()
    }
    metadata.classList.remove('skeleton')
    const tags = card_body.getElementsByClassName('tags')[0]
    for (const tag of event.tags) {
      if (tag[0] === 't') {
        // Add hashtag to card.
        const tagBadge = document.createElement('div')
        tagBadge.classList.add('badge', 'text-xs')
        tagBadge.innerHTML = '#'+tag[1]
        tags.appendChild(tagBadge)
      }
    }
    card_body.getElementsByClassName('event-kind')[0].innerHTML = 'kind: <code>' + event.kind + '</code>'
    card_body.getElementsByClassName('event-id')[0].innerHTML = 'id: <a href="https://njump.me/'+event.id+'" target="_blank">'+event.id+'</a>'
    // TODO fetch reactions
    // TODO fetch comments
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
            await init();
          }
        }, 100);
      }
      checkNdkStore()
    }
  }
}) (Drupal, drupalSettings);
