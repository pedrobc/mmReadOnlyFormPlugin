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
  private
    $_readOnlyFields = array()
  , $_notVisibleFields = array()
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
   * Empty method so that readOnlyFields and notVisibleFields can be configured
   */
  protected function postConfigure()
  {
  }
  /**
   *
   * Unset the widget and its validator
   */
  private function _handleNotVisibleField($fieldName)
  {
    if($this->getWidgetSchema()->offsetExists($fieldName))
    {
      $this->getWidgetSchema()->offsetUnset($fieldName);
    }
    if($this->getValidatorSchema()->offsetExists($fieldName))
    {
      $this->getValidatorSchema()->offsetUnset($fieldName);
    }
  }
  /**
   *
   * Mark the widgets as readonly &&
   * creates a validator if appropriate
   */
  private function _handleReadOnlyField($fieldName)
  {
    if($this->getWidgetSchema()->offsetExists($fieldName))
    {
      $this->_setWidgetReadOnly($fieldName);
      try {
        $this->validatorSchema[$fieldName] = new mmValidatorReadOnly(array(
          'value' => $this->getObject()->$fieldName
        ));
      } catch(Doctrine_Record_UnknownPropertyException $e) {
        //It's a "virtual" field. No need to validate it since it's not going to be saved in db
      } catch(Exception $e) {
        throw new Exception(__FILE__.':'.__LINE__.' encountered an exception: ' . $e->getMessage());
      }
    }
  }

  /**
   *
   * Adds a field to the notVisibleFields array
   *
   * @return void
   */
  public function addNotVisibleField($fieldName)
  {
    if(!is_string($fieldName))
    {
      throw new InvalidArgumentException(__FUNCTION__ . ': Invalid Argument type. Expected String, got ' . gettype($fieldName));
    }
    $this->_handleNotVisibleField($fieldName);
  }

  /**
   *
   * Adds an array of fields to the notVisibleFields array
   *
   * @return void
   */
  public function addNotVisibleFields(array $fields)
  {
    foreach($fields as $fieldName)
    {
      $this->_handleNotVisibleField($fieldName);
    }
  }

  /**
   *
   * Adds a field to the readOnlyFields array
   *
   * @return void
   */
  public function addReadOnlyField($fieldName)
  {
    if(!is_string($fieldName))
    {
      throw new InvalidArgumentException(__FUNCTION__ . ': Invalid Argument type. Expected String, got ' . gettype($fieldName));
    }
    $this->_handleReadOnlyField($fieldName);
  }

  /**
   *
   * Adds an array of fields to the readOnlyFields array
   *
   * @return void
   */
  public function addReadOnlyFields(array $fields)
  {
    foreach($fields as $fieldName)
    {
      $this->_handleReadOnlyField($fieldName);
    }
  }

  /**
   * Sets a widget to readonly.
   * This should really be a method from the widget class itself, but there's no BaseWidget class we can change...
   *
   * @param string $fieldName The widget name
   *
   * @return void
   **/
  private function _setWidgetReadOnly($fieldName)
  {
    if(!is_string($fieldName))
    {
      throw new InvalidArgumentException(__FUNCTION__ . ': Invalid Argument type. Expected String, got ' . gettype($fieldName));
    }
    if($this->widgetSchema[$fieldName] instanceof sfWidgetFormChoice)
    {
      $dummy = new mmWidgetFormChoiceReadOnly(array('choices' => array()));
      $avaialbleOptions = array_merge($dummy->getRequiredOptions(), array_keys($dummy->getOptions()));
      unset($dummy);
      $options = $this->widgetSchema[$fieldName]->getOptions();
      foreach($options as $k => $v) //remove extra options not supported by mmWidgetFormChoiceReadOnly
      {
        if(! in_array($k, $avaialbleOptions))
        {
          unset($options[$k]);
        }
      }
      $options['choices'] = $this->widgetSchema[$fieldName]->getChoices();
      $this->widgetSchema[$fieldName] = new mmWidgetFormChoiceReadOnly($options, $this->widgetSchema[$fieldName]->getAttributes());
    }
    elseif($this->widgetSchema[$fieldName] instanceof sfWidgetFormInputCheckbox)
    {
      $this->widgetSchema[$fieldName] = new mmWidgetFormInputCheckboxReadOnly($this->widgetSchema[$fieldName]->getOptions(), $this->widgetSchema[$fieldName]->getAttributes());
    }
    elseif($this->widgetSchema[$fieldName] instanceof sfWidgetFormDate)
    {
      $this->widgetSchema[$fieldName] = new mmWidgetFormInputReadOnly();
    }
    else
    {
      $this->widgetSchema[$fieldName]->setAttribute('readonly', 'readonly');
    }
  }

  /**
   * Sets all or defined widgets from a form to readonly.
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
      foreach($this->widgetSchema->getFields() as $fieldName => $w)
      {
        $this->_handleReadOnlyField($fieldName);
      }
    }

    //TODO: Also set embedded forms read-only
    foreach($this->getEmbeddedForms() as $f)
    {
      $f->setReadOnly();
    }
  }
}
