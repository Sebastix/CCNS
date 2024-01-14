<?php

namespace Drupal\nostr_ndk;

use GuzzleHttp\Client;

/**
 * AssetManager class for the Nostr Dev Package.
 */
class AssetManager {
  /**
   * Drupal\nostr_ndk\libraryVersion definition.
   *
   * @var string
   */
  protected static $packageVersion = '2.3.x';

  /**
   * Drupal\nostr_ndk\packageName definition.
   *
   * @var string
   */
  protected static $packageName = '@nostr-dev-kit/ndk';

  /**
   * @return string
   */
  public static function getPackageVersion(): string {
    return self::$packageVersion;
  }

  public static function getPackageName(): string {
    return self::$packageName;
  }
  /**
   * Retrieve the URL of the source package metadata.
   *
   * @return string
   *   The absolute URL to the source package metadata.
   */
  // @codingStandardsIgnoreLine
  public static function getNPMRegistryPackageUrl(): string {
    return 'https://registry.npmjs.org/' . self::$packageName . '/' . self::$packageVersion;
  }

  /**
   * Gets package dist url from NPM registry.
   *
   * @param string $url
   *   The full URL to the npm package.
   *
   * @return string
   *   The absolute URL to the downloadable archive.
   */
  public static function getNPMRegistryDistUrl($url) {
    $httpClient = new Client();
    $response = $httpClient->get($url);
    $parsed = json_decode($response->getBody());
    return $parsed->dist->tarball;
  }

}
