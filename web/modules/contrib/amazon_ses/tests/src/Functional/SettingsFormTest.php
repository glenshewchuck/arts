<?php

namespace Drupal\Tests\amazon_ses\Functional;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests the Amazon SES settings form.
 *
 * @group amazon_ses
 */
class SettingsFormTest extends FunctionalTestBase {

  /**
   * Tests that the settings form is protected.
   */
  public function testSettingsProtected() {
    $this->drupalGet(Url::fromRoute('amazon_ses.settings_form'));
    $this->assertSession()->statusCodeEquals(Response::HTTP_OK);

    $this->drupalLogout();
    $this->drupalGet(Url::fromRoute('amazon_ses.settings_form'));
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);

    $this->drupalLogin($this->basicUser);
    $this->drupalGet(Url::fromRoute('amazon_ses.settings_form'));
    $this->assertSession()->statusCodeEquals(Response::HTTP_FORBIDDEN);
  }

  /**
   * Tests the settings form.
   */
  public function testSettingsForm() {
    $this->drupalGet(Url::fromRoute('amazon_ses.settings_form'));

    $this->getSession()->getPage()->fillField('from_address', 'test@test.com');
    $this->getSession()->getPage()->fillField('queue', 1);

    $this->getSession()->getPage()->pressButton($this->t('Save configuration'));
    $this->assertSession()->pageTextContains('The settings have been saved.');
  }

  /**
   * Tests that an invalid email address shows an error.
   */
  public function testInvalidFromAddress() {
    $this->drupalGet(Url::fromRoute('amazon_ses.settings_form'));

    $this->getSession()->getPage()->fillField('from_address', 'test');

    $this->getSession()->getPage()->pressButton($this->t('Save configuration'));
    $this->assertSession()->pageTextContains('The email address test is not valid.');
  }

  /**
   * Tests that the From Address field is required.
   */
  public function testFromAddressRequired() {
    $this->drupalGet(Url::fromRoute('amazon_ses.settings_form'));

    $this->getSession()->getPage()->pressButton($this->t('Save configuration'));
    $this->assertSession()->pageTextContains('From Address field is required.');
  }

}
