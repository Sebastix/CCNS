import NDK, { NDKNip07Signer } from "@nostr-dev-kit/ndk";

console.log('NDK loaded');

(function ($, Drupal, drupalSettings) {
  "use strict";
  Drupal.behaviors.nostr_ndk = {

    // This function is called when the document is ready.
    attach: async function(context, settings) {
      console.log('Ready for NDK init')
      const nip07signer = new NDKNip07Signer();
      const ndk = new NDK({
        explicitRelayUrls: ["wss://nostr.sebastix.dev"],
        enableOutboxModel: true,
        signer: nip07signer
      });

      await ndk.connect();

      nip07signer.user().then(async (user) => {
        if (!!user.npub) {
          console.log("Permission granted to read their public key:", user.npub);
        }
      });

      const instance = new NdkStore();
      instance.create(ndk);

      // @todo find a way to export this instance with the NdkStore

    }
  }
}) (jQuery, Drupal, drupalSettings);

export class NdkStore {
  constructor() {
    if(!NdkStore.instance) {
      this._data = [];
      NdkStore.instance = this;
    }
    return NdkStore.instance;
  }
  create(item){
    this._data.push(item)
  }
  read(id){
    return this._data.find(d => d.id === id);
  }
  update(id){

  }
  delete(id){

  }
}
