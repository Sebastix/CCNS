![](https://ccns.sebastix.dev/themes/custom/ccns_theme/images/ccns_logo_color.webp)

# Community Curated Nostr Stuff (CCNS)

CCNS is a Nostr-focused community platform centered around link aggregation and discussion topics. Anyone with a Nostr account is able to post links and start discussions. Those are curated with zaps with a user defined number of sats.

> **Problem**
>
> Activity on https://stacker.news/~nostr has decreased significant since the introduction of territories.

This is a Drupal & Nostr powered project inspired by HackerNews, [Kbin.pub](http://Kbin.pub), [Lobste.rs](http://Lobste.rs) and [Stacker.news](http://Stacker.news).

![](https://ccns.sebastix.dev/themes/custom/ccns_theme/images/screenshots/202402/01.png)
![](https://ccns.sebastix.dev/themes/custom/ccns_theme/images/screenshots/202402/02.png)
![](https://ccns.sebastix.dev/themes/custom/ccns_theme/images/screenshots/202402/03.png)

# Roadmap

---

- [ ] Create content model
  - [ ] Topic entity
  - [x] User entity
  - [x] Link entity
  - [ ] Comment entity
  - [x] CCNS member role and permissions
- [ ] Add content entities and fields to Drupal
  - [x] Link
  - [x] User
  - [ ] Comment
  - [ ] Topic
- [x] Add NDK for Nostr integration for authenticating users
- [x] Integrate Nostr key authentication with the Drupal user management system
- [x] Add 'crosspost to Nostr' option to broadcast an event with your submitted link (NIP-01)
- [ ] Add 'crosspost to Nostr' option to broadcast an event with your submitted topic within the CCNS community (NIP-72)
- [ ] Add threaded comments below links and topics
- [ ] Integrate zaps (NIP-57)
  - [ ] For links
  - [ ] For comments
- [ ] Setup e-mail notifications with [DANSE](https://www.drupal.org/project/danse)
- [ ] Add a notifications center with [DANSE](https://www.drupal.org/project/danse)
- [ ] Integrate tags provided by a taxonomy managed by Drupal
- [ ] Add a news/blog section
  - [ ] Add options to crosspost these items to Nostr on behalf of the CCNS account
- [ ] Add login method with nsecbunker
- [ ] Add login method with nsec key
- [ ] Add a way to register a new Nostr account

To see all items on a Kanban project board, please have a look at https://github.com/users/Sebastix/projects/2.

# Issues and contributions

---

All contributions and issues are handled in the Github repo.
The issue queue: https://github.com/Sebastix/CCNS/issues.

# Telegram group

---

We have a Telegram group you can join: https://t.me/ccns_community

# Support

---

Contact [Sebastian Hagens](https://gitlab.com/Sebastix)

Sebastian Hagens is a self-employed creative technologist working as a Drupal & fullstack webdeveloper and tech consultant from The Netherlands.

**Follow Sebastian on Nostr**

Pubkey: `npub1qe3e5wrvnsgpggtkytxteaqfprz0rgxr8c3l34kk3a9t7e2l3acslezefe`

Handle: `sebastian@sebastix.dev`

**Follow CCNS on Nostr**

Pubkey: `npub12pj5g0370uz3f49flpz8scs85p8s9c7l6ueutjhkyc3jnggrlafsmmljva`

Handle: `ccns@ccns.sebastix.dev`

# CI/CD with GitLab

---

See `.gitlab-ci.yml` and https://gitlab.com/sebastix-group/nostr/CCNS/-/pipelines

# Build with Drupal

---

@TODO

## Progressive Web Application

@TODO

## Used Drupal contrib modules

* Config split
* Drush
* Raven
* Backup Migrate
* Symfony Mailer
* Admin Toolbar
* Gin
* Pathauto
* Metatag
* Masquerade
* Ultimate Cron
* Advanced CSS/JS Aggregation
* Flood control
* Comment delete
* Comments order
* Voting API
* Twig Tweak
* CKEditor markdown
* Unique content field validation
* Config ignore
* Robots Txt
* Matomo
* Field permissions
* DANSE
* Honeypot

Modules for development only:
* Coder
* Devel
* Webprofiler
* Drupal Coder
* Drupal Rector

## Theme

All theme files are located in `web/themes/custom/ccns_theme`.

TailwindCSS + Daisy UI using Lofi as the base theme (see `web/themes/custom/ccns_theme/tailwind.config.js`).
The icons used are from https://heroicons.com/.

- Node version `>=16.9.0`

`cd web/themes/custom/ccns_theme`

Install all packages:

`npm install`

`npm run build`

Or run `npm run dev` while developing.

## Custom work

### Drupal core doesn't support Javascript ES6 for exporting / importing modules

Support for exporting / importing modules with Javascript ES6 is a work in progress for Drupal core. I've found another way to create a Javascript singleton instance which can be used by multiple Drupal modules.
Why would you use this? For using a store for example, so you can share different states and data between components and pages.
In our case we need a global accessible store with the Nostr Dev Kit instance provided by the NDK library.

### CCNS module

Custom module located at `web/modules/custom/ccns` and depends on the `nostr_ndk` module.

### Nostr NDK module

Custom module located at `web/modules/custom/nostr_ndk`.

### Theme hooks

@TODO

## Security checks

@TODO
- [ ] https://github.com/FriendsOfPHP/security-advisories
- [ ] https://github.com/fabpot/local-php-security-checker

## Code checks

@TODO
- [ ] https://www.drupal.org/project/coder

# License

---

`GPL-2.0` GNU General Public License v2.0


