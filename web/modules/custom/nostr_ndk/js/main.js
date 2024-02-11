import NDK, {NDKNip07Signer, NDKEvent, NDKRelaySet, NDKRelay} from "@nostr-dev-kit/ndk";

(function ($, Drupal, drupalSettings) {
  "use strict";

  // Global namespace for the NdkStore which will be initialized.
  Drupal.Ndk = Drupal.Ndk || {};

  Drupal.behaviors.nostr_ndk = {

    // This function is called when the document is ready.
    attach: async function(context, settings) {
      /**
       * Create NdkStore.
       * @type {NdkStore}
       */
      const store = new NdkStore();
      store.set('ndk', new NDK());
      const nip07signer = new NDKNip07Signer();
      store.set('nip07signer', nip07signer);
      if (Drupal.Ndk.store === undefined && drupalSettings.user.uid === 0) {
        console.log('Ready to login with Nostr, LFG!')
      } else if (drupalSettings.user.uid !== 0) {
        // User is logged in but clientside Nostr auth is not active. Let's reconnect.
        const ndk = store.get('ndk');
        ndk.signer = nip07signer
        await ndk.connect();
        console.log('Reconnected to Nostr, LFG!')
      } else {
        // User is logged in and Nostr auth is set.
        console.log('Logged in and Nostr authenticated, LFG!')
      }
      store.set('ndkEvent', new NDKEvent())
      // Let's 'export' the store, so we can use it globally in other Javascript files loaded by Drupal.
      Drupal.Ndk.store = store;
      console.log('NdkStore initialized');
    }
  }
}) (jQuery, Drupal, drupalSettings);

/**
 * NdkStore singleton class for a key-value store.
 */
class NdkStore {
  /**
   * Constructor.
   *
   * @returns {NdkStore}
   */
  constructor() {
    if(!NdkStore.instance) {
      this._data = {};
      NdkStore.instance = this;
    }
    return NdkStore.instance;
  }

  /**
   * Setter.
   *
   * @param key
   * @param value
   */
  set(key, value){
    this._data[key] = value;
  }

  /**
   * Getter.
   *
   * @param key
   * @returns {*}
   */
  get(key){
    return this._data[key];
  }

  /**
   * Delete an entry.
   *
   * @param key
   */
  delete(key){
    delete this._data[key];
  }
  /**
   * Return all keys in an array.
   * @returns {*[]}
   */
  getAllKeys(){
    const keys = [];
    for (const key in this._data) {
      keys.push(key)
    }
    return keys;
  }
}
