<?php
/**
 * Base project form.
 *
 * @package    feel2
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: BaseForm.class.php 20147 2009-07-13 11:46:57Z FabianLange $
 */
class mmReadOnlyBaseForm extends sfFormSymfony
{
  protected
    $readOnlyFields = array()
  , $notVisibleFields = array()
  ;

  /**
   * Constructor.
   *
   * @param array  $defaults    An array of field default values
   * @param array  $options     An array of options
   * @param string $CSRFSecret  A CSRF secret
   */
  public function __construct($defaults = array(), $options = array(), $CSRFSecret = null)
  {
    $this->setDefaults($defaults);
    $this->options = $options;
    $this->localCSRFSecret = $CSRFSecret;

    $this->validatorSchema = new sfValidatorSchema();
    $this->widgetSchema    = new sfWidgetFormSchema();
    $this->errorSchema     = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setup();
    $this->configure();
    $this->postConfigure();

    $this->addCSRFProtection($this->localCSRFSecret);
    $this->resetFormFields();
  }

  /**
   *
   * postConfigure handles the readOnly Fields and the NonVisibleFields
   */
  protected function postConfigure()
  {
    $this->handleNotVisibleFields();
    $this->handleReadOnlyFields();
  }
  /**
   *
   * Unset the widget and its validator
   */
  protected  function handleNotVisibleFields()
  {
    $notVisible = $this->getNotVisibleFields();
    foreach($notVisible as $widget)
    {
      if($this->getWidgetSchema()->offsetExists($widget))
      {
        $this->getWidgetSchema()->offsetUnset($widget);
      }
      if($this->getValidatorSchema()->offsetExists($widget))
      {
        $this->getValidatorSchema()->offsetUnset($widget);
      }
    }
  }
  /**
   *
   * Mark the widgets as readonly
   */
  protected function handleReadOnlyFields()
  {
    $readOnly = $this->getReadOnlyFields();
    foreach($readOnly as $widget)
    {
      if($this->getWidgetSchema()->offsetExists($widget))
      {
        $this->setWidgetReadOnly($widget);
      }
    }
  }
  /**
   *
   * Gets an array with the name of all the widgets that will be unset
   * Overload this method to unset returned widgets
   *
   * @return array
   */
  protected function getNotVisibleFields()
  {
    return $this->notVisibleFields;
  }
  /**
   *
   * Gets an array with the name of all the widgets that will be set as readonly
   * Overload this method to make returned widgets readonly
   *
   * @return array
   */
  protected function getReadOnlyFields()
  {
    return $this->readOnlyFields;
  }

  /**
   * Returns true if the form is valid.
   *
   * It returns false if the form is not bound.
   *
   * @return Boolean true if the form is valid, false otherwise
   */
  public function isValid()
  {
    return parent::isValid() && $this->_isReadOnlyModified();
  }

  /**
   * Checks if readonly fields have been changed and if so, put them as they were
   *
   *
   * @return true so && above never fails because of ths method
   */
  protected function _isReadOnlyModified()
  {
    foreach($this->getReadOnlyFields() as $name)
    {
      try {
        $originalValue = $this->getObject()->$name;
      } catch(Doctrine_Record_UnknownPropertyException $e) {
        continue; //It's a "virtual" field
      } catch(Exception $e) {
        throw new Exception(__FILE__.':'.__LINE__.' encountered an exception: ' . $e->getMessage());
      }
      if($this->values[$name] != $this->getObject()->$name)
      {
        $this->values[$name] = $this->getObject()->$name;
      }
    }
    return true;
  }

  /**
   * Sets a widget to readonly.
   * This should really be a method from the widget class itself, but there's no BaseWidget class we can change...
   *
   * @param string $name The widget name
   *
   * @return void
   **/
  public function setWidgetReadOnly($name)
  {
    if($this->widgetSchema[$name] instanceof sfWidgetFormChoice)
    {
      $dummy = new mmWidgetFormChoiceReadOnly(array('choices' => array()));
      $avaialbleOptions = array_merge($dummy->getRequiredOptions(), array_keys($dummy->getOptions()));
      unset($dummy);
      $options = $this->widgetSchema[$name]->getOptions();
      foreach($options as $k => $v) //remove extra options not supported by mmWidgetFormChoiceReadOnly
      {
        if(! in_array($k, $avaialbleOptions))
        {
          unset($options[$k]);
        }
      }
      $options['choices'] = $this->widgetSchema[$name]->getChoices();
      $this->widgetSchema[$name] = new mmWidgetFormChoiceReadOnly($options, $this->widgetSchema[$name]->getAttributes());
    }
    elseif($this->widgetSchema[$name] instanceof sfWidgetFormInputCheckbox)
    {
      $this->widgetSchema[$name] = new mmWidgetFormInputCheckboxReadOnly($this->widgetSchema[$name]->getOptions(), $this->widgetSchema[$name]->getAttributes());
    }
    elseif($this->widgetSchema[$name] instanceof sfWidgetFormDate)
    {
      $this->widgetSchema[$name] = new mmWidgetFormInputReadOnly();
    }
    else
    {
      $this->widgetSchema[$name]->setAttribute('readonly', 'readonly');
    }
  }
  /**
   * Sets widgets from a form to readonly.
   * If no widget names are passed, all form is made readonly
   *
   * @param array $fields The widget name
   *
   * @return void
  **/

  public function setReadOnly(array $fields = array())
  {
    if(count($fields) == 0) //use all form fields
    {
      $this->readOnlyFields = $this->widgetSchema->getFields();
    }

    //TODO: Somehow this doesn't work
    foreach($this->getEmbeddedForms() as $f)
    {
      $f->readOnlyFields = $f->widgetSchema->getFields();
    }
  }
}
