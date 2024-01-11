<?php

namespace Drupal\Tests\rate\Functional;

use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Test translating rate widgets.
 *
 * @group rate
 */
class RateWidgetTranslationTest extends RateWidgetTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'language',
    'config_translation',
  ];

  /**
   * The NL language entity.
   *
   * @var \Drupal\Core\Language\LanguageInterface
   */
  protected $nlLanguage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalLogin($this->rootUser);

    $this->nlLanguage = ConfigurableLanguage::createFromLangcode('nl');
    $this->nlLanguage->save();

    // Set the language path prefixes.
    $this->config('language.negotiation')->set('url', [
      'source' => 'path_prefix',
      'prefixes' => [
        'en' => 'en',
        'nl' => 'nl',
      ],
    ])->save();
  }

  /**
   * Confirm that translating the rate widget works.
   */
  public function testRateWidgetTranslation() {

    // Create the rate widget.
    $options = [
      [
        'value' => 1,
        'label' => 'Option 1 EN',
      ],
      [
        'value' => 2,
        'label' => 'Option 2 EN',
      ],
      [
        'value' => 3,
        'label' => 'Option 3 EN',
      ],
    ];
    $entity_types = ['node.article'];
    $comment_types = [];
    $voting = ['use_deadline' => 0];
    $display = [
      'display_label' => 'Display label EN',
      'label_position' => 'above',
      'description' => 'Description EN',
      'description_position' => 'below',
    ];
    $results = [
      'result_position' => 'hidden',
    ];

    $this->createRateWidget('dummy_rate_widget', 'Dummy rate widget EN', 'custom', $options, $entity_types, $comment_types, $voting, $display, $results);

    // Reset any static cache.
    drupal_static_reset();

    $this->drupalGet('admin/structure/rate_widgets');

    $this->drupalGet('admin/structure/rate/dummy_rate_widget/edit/translate/nl/add');
    $this->assertSession()->statusCodeEquals(200);

    $this->submitForm([
      'translation[config_names][rate.rate_widget.dummy_rate_widget][options][0][label]' => 'Option 1 NL',
      'translation[config_names][rate.rate_widget.dummy_rate_widget][options][1][label]' => 'Option 2 NL',
      'translation[config_names][rate.rate_widget.dummy_rate_widget][options][2][label]' => 'Option 3 NL',
      'translation[config_names][rate.rate_widget.dummy_rate_widget][display][display_label]' => 'Display label NL',
      'translation[config_names][rate.rate_widget.dummy_rate_widget][display][description]' => 'Description NL',
    ], 'Save translation');

    drupal_flush_all_caches();

    $this->drupalGet('node/1');
    $this->assertSession()->elementTextContains('css', '.rate-widget', 'Display label EN');
    $this->assertSession()->elementTextContains('css', '.rate-widget', 'Option 1 EN');
    $this->assertSession()->elementTextContains('css', '.rate-widget', 'Option 2 EN');
    $this->assertSession()->elementTextContains('css', '.rate-widget', 'Option 3 EN');
    $this->assertSession()->elementTextContains('css', '.rate-widget', 'Description EN');

    $this->assertSession()->elementTextNotContains('css', '.rate-widget', 'Display label NL');
    $this->assertSession()->elementTextNotContains('css', '.rate-widget', 'Option 1 NL');
    $this->assertSession()->elementTextNotContains('css', '.rate-widget', 'Option 2 NL');
    $this->assertSession()->elementTextNotContains('css', '.rate-widget', 'Option 3 NL');
    $this->assertSession()->elementTextNotContains('css', '.rate-widget', 'Description NL');

    $this->drupalGet('node/1', ['language' => $this->nlLanguage]);
    $this->assertSession()->elementTextContains('css', '.rate-widget', 'Display label NL');
    $this->assertSession()->elementTextContains('css', '.rate-widget', 'Option 1 NL');
    $this->assertSession()->elementTextContains('css', '.rate-widget', 'Option 2 NL');
    $this->assertSession()->elementTextContains('css', '.rate-widget', 'Option 3 NL');
    $this->assertSession()->elementTextContains('css', '.rate-widget', 'Description NL');

    $this->assertSession()->elementTextNotContains('css', '.rate-widget', 'Display label EN');
    $this->assertSession()->elementTextNotContains('css', '.rate-widget', 'Option 1 EN');
    $this->assertSession()->elementTextNotContains('css', '.rate-widget', 'Option 2 EN');
    $this->assertSession()->elementTextNotContains('css', '.rate-widget', 'Option 3 EN');
    $this->assertSession()->elementTextNotContains('css', '.rate-widget', 'Description EN');
  }

}
