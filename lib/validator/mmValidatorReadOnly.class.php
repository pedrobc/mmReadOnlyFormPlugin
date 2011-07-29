<?php

/**
 * mmValidatorReadOnly validates that the value has not been changed.
 *
 *
 */
class mmValidatorReadOnly extends sfValidatorBase
{
  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * value:      The value to be compared against (required)
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('value');
    $this->setOption('required', false);
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    //if($value != $this->getOption('value'))
    return $this->getOption('value');
  }
}