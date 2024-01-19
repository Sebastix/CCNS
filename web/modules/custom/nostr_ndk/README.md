## Introduction

The nostr_ndk module is a module to include the Javascript library Nostr Dev Kit (NDK) in your Drupal project.

### Objective

* Install and export the Nostr Dev Kit (NDK) library in a Drupal project. NDK will be available as a global store (singleton) in other Drupal provided Javascript files.

The by Vite bundled Javascript file is loaded as a library on every page with the hook `nostr_ndk_preprocess_page`.

## Requirements

* Node `>16`

## Installation

Install as you would normally install a contributed Drupal module.
See: https://www.drupal.org/node/895232 for further information.
After the module is enabled, the necessary packages needs to be installed.

`npm install`

After this installation step, the Javascript files needs to be built. This is done with Vite.

`npm build`

The built Javascript files will be saved in the directory `dist`.

## Maintainers

Current maintainers:

- Sebastian Hagens (Sebastix) - https://www.drupal.org/u/sebastian-hagens

