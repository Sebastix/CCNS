import NDK, { NDKNip07Signer } from "@nostr-dev-kit/ndk";

const nip07signer = new NDKNip07Signer();
const ndk = new NDK({
  explicitRelayUrls: ["wss://nostr.sebastix,dev"],
  signer: nip07signer
});

await ndk.connect();

nip07signer.user().then(async (user) => {
  if (!user.npub) {
    console.log("Permission granted to read their public key:", user.npub);
  }
});


