<?php

namespace Drupal\Tests\rate\Kernel;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\RoleInterface;

/**
 * Tests access control for vote_types defined in the Rate module.
 *
 * @group rate
 */
class RateTypeAccessTest extends KernelTestBase {

  use UserCreationTrait {
    createUser as drupalCreateUser;
    createRole as drupalCreateRole;
    createAdminRole as drupalCreateAdminRole;
  }

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'comment',
    'user',
    'system',
    'votingapi',
    'rate',
  ];
  /**
   * An array of config object names that are excluded from schema checking.
   *
   * @var string[]
   */
  protected static $configSchemaCheckerExclusions = [
    // Following are used to test lack of or partial schema. Where partial
    // schema is provided, that is explicitly tested in specific tests.
    'views.view.rate_results',
    'views.view.rate_widgets_results',
    'field.storage.node.field_rate_vote_deadline',
  ];

  /**
   * Access handler.
   *
   * @var \Drupal\Core\Entity\EntityAccessControlHandlerInterface
   */
  protected $accessHandler;

  /**
   * A simple user with basic node and vote permissions.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $loggedInUser;

  /**
   * A simple user vote permission.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $anonymousUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('system', 'sequences');
    $this->installEntitySchema('user');
    $this->installConfig('rate');
    $this->accessHandler = $this->container->get('entity_type.manager')
      ->getAccessControlHandler('vote_type');
    // Clear permissions for authenticated users.
    $this->config('user.role.' . RoleInterface::AUTHENTICATED_ID)
      ->set('permissions', [])
      ->save();

    // Create user 1 who has special permissions.
    $this->drupalCreateUser();

    $this->loggedInUser = $this->drupalCreateUser([
      'view rate results page',
    ]);
    $this->anonymousUser = $this->drupalCreateUser([]);

  }

  /**
   * Tests access handling for different rate types.
   */
  public function testRateTypeAccess() {
    // The following rate types are defined by the Rate module.
    // In rate_vote_type_access(), permission to view vote types with these
    // IDs is granted for users with the 'view rate results page' permission.
    $rate_types = [
      'updown',
      'fivestar',
    ];

    $vote_type_storage = $this->container->get('entity_type.manager')->getStorage('vote_type');

    // Create fake vote type.
    $vote_type_storage->create([
      'id' => 'fake',
      'label' => 'Fake vote type',
      'value_type' => 'points',
      'description' => 'A fake vote type for testing purposes.',
    ])->save();

    // Test each of the vote types that are defined by the Rate module.
    foreach ($rate_types as $rate_type) {
      $vote_type = $vote_type_storage->load($rate_type);

      // Confirm that the logged_user can access the vote type info.
      $this->assertTrue(
        $this->accessHandler->access($vote_type, 'view', $this->loggedInUser),
        'Logged in user can see vote of type ' . $rate_type
      );

      // Confirm that the anonymous_user cannot access the vote type info.
      $this->assertFalse(
        $this->accessHandler->access($vote_type, 'view', $this->anonymousUser),
        'Anonymous user cannot see vote of type ' . $rate_type
      );
    }

    // Confirm that neither user may access the fake vote type,
    // which is not in the list of vote types provided by the Rate module.
    $fake_vote_type = $vote_type_storage->load('fake');

    $this->assertFalse(
      $this->accessHandler->access($fake_vote_type, 'view', $this->loggedInUser),
      'Logged in user cannot see vote of type ' . $fake_vote_type->id()
    );

    $this->assertFalse(
      $this->accessHandler->access($fake_vote_type, 'view', $this->anonymousUser),
      'Anonymous user cannot see vote of type ' . $fake_vote_type->id()
    );
  }

}
