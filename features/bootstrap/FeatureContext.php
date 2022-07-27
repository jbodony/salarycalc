<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{

  private $filename;

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct()
  {
  }

  /** @Given /^I have a file named "([^"]*)"$/ */
  public function iHaveAFileNamed($file)
  {
    if (!file_exists($file)) {
      throw new Exception(
        "Missing $file"
      );
    }
  }

  /** @When /^I run "([^"]*)"$/ */
  public function iRun($command)
  {
    exec($command, $output);
  }

  /** @Then /^I should get a file named "([^"]*)"$/ */
  public function iShouldGetAFileNamed($file)
  {
    if (!file_exists($file)) {
      throw new Exception(
        "Missing output $file"
      );
    } else {
      $this->filename = $file;
    }
  }

  /** @Then /^File should contain:$/ */
  public function fileShouldContain(PyStringNode $string)
  {
    $lines = file($this->filename);
    $output = trim(implode($lines));
    if ((string)$string !== $output) {
      throw new Exception(
        "Actual output is:\n" . $output
      );
    }
  }
}

