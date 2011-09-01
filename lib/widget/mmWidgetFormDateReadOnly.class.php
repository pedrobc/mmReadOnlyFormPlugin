<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWidgetFormInput represents an HTML input tag.
 *
 * @package    symfony
 * @subpackage widget
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfWidgetFormInputCheckbox.class.php 21908 2009-09-11 12:06:21Z fabien $
 */
class mmWidgetFormDateReadOnly extends sfWidgetFormDate
{
  /**
   * Constructor.
   *
   * Available options:
   *
   *  * type: The widget type
   *
   * @param array $options     An array of options
   * @param array $attributes  An array of default HTML attributes
   *
   * @see sfWidgetForm
   */
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
    $this->addRequiredOption('type');

    // to maintain BC with symfony 1.2
    $this->setOption('type', 'text');
  }

  /**
   * Renders the widget.
   *
   * @param  string $name        The element name
   * @param  string $value       The value displayed in this widget
   * @param  array  $attributes  An array of HTML attributes to be merged with the default HTML attributes
   * @param  array  $errors      An array of errors for the field
   *
   * @return string An HTML tag string
   *
   * @see sfWidgetForm
   */
  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    if(is_array($value))
    {
      if(isset($value['year']) && isset($value['month']) && isset($value['day']))
      {

        $value = $value['year'] . '/' . $value['month'] . '/' . $value['day'];
      }
      else
      {
        throw new InvalidArgumentException('For array values please specify year, month and day keys');
      }
    }
    return parent::render($name . '_readonly', format_date($value), array_merge(array('disabled' => 'disabled'), $attributes), $errors) . $this->renderTag('input', array('type' => 'hidden', 'name' => $name, 'value' => $value, 'readonly' => 'readonly'));
  }
}
