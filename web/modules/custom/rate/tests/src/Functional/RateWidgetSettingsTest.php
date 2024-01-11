<?php

namespace Drupal\Tests\rate\Functional;

use Drupal\Core\Form\FormState;
use Drupal\Core\Render\AttachmentsTrait;
use Drupal\rate\Form\RateSettingsForm;

/**
 * Tests the SettingsForm form.
 *
 * @group rate
 *
 * @package Drupal\Tests\rate\Functional
 */
class RateWidgetSettingsTest extends RateWidgetTestBase {

  use AttachmentsTrait;

  /**
   * Settings configuration variable.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $settingsConfig;

  /**
   * Form builder object variable.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * A saved rate widget entity.
   *
   * @var \Drupal\rate\RateWidgetInterface
   */
  protected $rateWidget;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->settingsConfig = $this->config('rate.settings');
    $this->formBuilder = $this->container->get('form_builder');

    $options = [
      ['value' => 1, 'label' => 'Star 1'],
      ['value' => 2, 'label' => 'Star 2'],
      ['value' => 3, 'label' => 'Star 3'],
      ['value' => 4, 'label' => 'Star 4'],
      ['value' => 5, 'label' => 'Star 5'],
    ];
    $this->rateWidget = $this->createRateWidget('fivestar', 'Fivestar', 'fivestar', $options, ['node.article']);
  }

  /**
   * Tests the disable log settings.
   */
  public function testSettingsFormDisableLog() {
    $form_state = (new FormState())
      ->setValues([
        'disable_log' => FALSE,
      ]);
    $this->formBuilder->submitForm(RateSettingsForm::class, $form_state);
    $this->settingsConfig = $this->config('rate.settings');
    $this->assertCount(0, $form_state->getErrors());
    $this->assertEmpty($this->settingsConfig->get('disable_log'));

    $this->drupalGet('node/1');

    $form_state = (new FormState())
      ->setValues([
        'disable_log' => TRUE,
      ]);
    $this->formBuilder->submitForm(RateSettingsForm::class, $form_state);
    $this->settingsConfig = $this->config('rate.settings');
    $this->assertCount(0, $form_state->getErrors());
    $this->assertTrue($this->settingsConfig->get('disable_log'));

    $this->drupalGet('node/2');
  }

  /**
   * Tests the disable fontawesome settings.
   */
  public function testSettingsFormDisableFontawesome() {
    $form_state = (new FormState())
      ->setValues([
        'disable_fontawesome' => FALSE,
      ]);
    $this->formBuilder->submitForm(RateSettingsForm::class, $form_state);
    $this->settingsConfig = $this->config('rate.settings');
    $this->assertCount(0, $form_state->getErrors());
    $this->assertEmpty($this->settingsConfig->get('disable_fontawesome'));

    $this->drupalGet('node/1');

    $form_state = (new FormState())
      ->setValues([
        'disable_fontawesome' => TRUE,
      ]);
    $this->formBuilder->submitForm(RateSettingsForm::class, $form_state);
    $this->settingsConfig = $this->config('rate.settings');
    $this->assertCount(0, $form_state->getErrors());
    $this->assertTrue($this->settingsConfig->get('disable_fontawesome'));

    $this->drupalGet('node/2');
  }

}
