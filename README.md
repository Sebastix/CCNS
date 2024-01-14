# CCNS

Community Curated Nostr Stuff

> **Problem**
>
> Activity on https://stacker.news/~nostr has decreased significant since the introduction of territories.

A Drupal & Nostr powered project inspired by HackerNews, [Kbin.pub](http://Kbin.pub), [Lobste.rs](http://Lobste.rs) and [Stacker.news](http://Stacker.news).

## Roadmap

- [ ] Create content model
- [ ] Add content entities and fields
- [ ] Add NDK for Nostr integration for authenticating users
- [ ] Integrate Nostr key authentication with the Drupal user management system
- [ ] Add crosspost to Nostr option to broadcast an event with your submitted link with [Nostr Simple Publish](https://www.drupal.org/project/nostr_simple_publish)

## Issues and contributions

https://gitlab.com/sebastix-group/cchs.social/-/issues

## Support

Contact [Sebastian Hagens - Sebastix](https://gitlab.com/Sebastix)

# CI/CD

@TODO
See `.gitlab-ci.yml`

# Build with Drupal

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
* Multiple registration
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
`npm install`
`npm run build`

Or run `npm run dev` while developing.

## Custom work

### CCNS module

@TODO

### Nostr NDK

When you enable this module, the Nostr Dev Kit package is downloaded to the `/web/libraries` directory. The package is loaded as a global assets on the website defined in `libraries.info.yml`.

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

@TODO
