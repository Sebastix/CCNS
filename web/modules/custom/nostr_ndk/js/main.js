import NDK, { NDKNip07Signer } from "@nostr-dev-kit/ndk";

(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.Ndk = Drupal.Ndk || {};

  Drupal.behaviors.nostr_ndk = {

    // This function is called when the document is ready.
    attach: async function(context, settings) {
      /**
       * Create NdkStore
       * @type {NdkStore}
       */
      const store = new NdkStore();
      store.get('ndk', NDK);
      const nip07signer = new NDKNip07Signer();
      store.get('nip07signer', nip07signer);
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
