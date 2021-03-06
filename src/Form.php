<?php

  namespace Fiv\Form;

  use Fiv\Form\Element;
  use Fiv\Form\Element\Checkbox;
  use Fiv\Form\Element\CheckboxList;
  use Fiv\Form\Element\ElementInterface;
  use Fiv\Form\Element\RadioList;
  use Fiv\Form\Element\Select;
  use Fiv\Form\Element\Submit;
  use Fiv\Form\Element\TextArea;
  use Fiv\Form\Elements\DataElementInterface;
  use Fiv\Form\Elements\ValidatableElementInterface;

  /**
   * @author Ivan Shcherbak <dev@funivan.com>
   */
  class Form extends Element\Html {

    /**
     * @var null|string
     */
    protected $uid = null;

    /**
     * @var boolean|null
     */
    protected $validationResult = null;

    /**
     * @var array
     */
    protected $errorList = [];

    /**
     * @var bool
     */
    protected $isSubmitted = false;

    /**
     * @var DataElementInterface[]
     */
    protected $elements = [];

    /**
     * Default form attributes
     *
     * @var array
     */
    protected $attributes = [
      'method' => 'post',
    ];


    /**
     * @param $name
     * @return $this
     */
    public function setName($name) {
      $this->uid = $name;
      $this->attributes['name'] = $name;
      return $this;
    }


    /**
     * @param FormData $data
     * @return $this
     */
    public function handle(FormData $data) {
      $this->cleanValidationFlag();

      $this->isSubmitted = false;
      if ($data->isMethod($this->getMethod()) and $data->has($this->getUid())) {
        $this->isSubmitted = true;
        foreach ($this->getElements() as $element) {
          $element->handle($data);
        }

      }

      return $this;
    }


    /**
     * @return string
     */
    public function getMethod() {
      if (!empty($this->attributes['method'])) {
        return strtolower($this->attributes['method']);
      }

      return null;
    }


    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method) {
      $this->attributes['method'] = $method;

      return $this;
    }


    /**
     *
     */
    protected function cleanValidationFlag() {
      $this->errorList = [];
      $this->validationResult = null;
    }


    /**
     * Check if form is submitted and all elements are valid
     *
     * @return boolean
     */
    public function isValid() {
      if ($this->validationResult !== null) {
        return $this->validationResult;
      }

      if (!$this->isSubmitted()) {
        return false;
      }

      $this->validationResult = true;

      foreach ($this->getElements() as $element) {
        if ($element instanceof ValidatableElementInterface) {
          $validationResult = $element->validate();
          foreach ($validationResult->getErrors() as $errorMessage) {
            $this->addError($errorMessage);
          }
        }

        if (!$element instanceof ElementInterface) {
          continue;
        }

        if ($element->isValid()) {
          continue;
        }

        foreach ($element->getValidatorsErrors() as $errorMessage) {
          $this->addError($errorMessage);
        }
      }

      return $this->validationResult;
    }


    /**
     * @return array
     */
    public function getErrors() {
      return $this->errorList;
    }


    /**
     * @param string $error
     * @return $this
     */
    protected function addError($error) {
      if (!is_string($error)) {
        throw new \InvalidArgumentException('Error should be a string, ' . gettype($error) . ' given.');
      }
      $this->validationResult = false;
      $this->errorList[] = $error;
      return $this;
    }


    /**
     * Check if form is submitted
     *
     * @return bool
     */
    public function isSubmitted() {
      return $this->isSubmitted;
    }


    /**
     * Return unique id of form
     *
     * @return string
     */
    public function getUid() {
      if (empty($this->uid)) {
        $this->uid = md5(get_called_class());
      }

      return $this->uid;
    }


    /**
     * @return DataElementInterface[]
     */
    public function getElements() {
      return $this->elements;
    }


    /**
     * @param string $name
     * @return DataElementInterface
     * @throws \InvalidArgumentException
     */
    public function getElement($name) {
      if (empty($this->elements[$name])) {
        throw new \InvalidArgumentException('Element with name "' . $name . '" not found');
      }
      return $this->elements[$name];
    }


    /**
     * Attach element to this form. Overwrite element with same name
     *
     * @param DataElementInterface $element
     * @return $this
     */
    public function setElement(DataElementInterface $element) {
      $this->cleanValidationFlag();
      $this->elements[$element->getName()] = $element;
      return $this;
    }


    /**
     * @param DataElementInterface $element
     * @return $this
     * @throws \Exception
     */
    public function addElement(DataElementInterface $element) {
      if (isset($this->elements[$element->getName()])) {
        throw new \Exception('Element with name ' . $element->getName() . ' is already added. Use setElement to overwrite it or change name');
      }

      $this->cleanValidationFlag();
      $this->elements[$element->getName()] = $element;
      return $this;
    }


    /**
     * @param string $name
     * @param string|null $text
     * @return \Fiv\Form\Element\Input
     */
    public function input($name, $text = null) {
      $input = new Element\Input();
      $input->setName($name);
      $input->setText($text);
      $this->addElement($input);
      return $input;
    }


    /**
     * @param string $name
     * @param string|null $text
     * @return \Fiv\Form\Element\Input
     */
    public function password($name, $text = null) {
      $input = new Element\Password();
      $input->setName($name);
      $input->setText($text);
      $this->addElement($input);
      return $input;
    }


    /**
     * @param string $name
     * @param null $text
     * @return Select
     */
    public function select($name, $text = null) {
      $select = new Select();
      $select->setName($name);
      $select->setText($text);
      $this->addElement($select);
      return $select;
    }


    /**
     * @param string $name
     * @param string $text
     * @return RadioList
     */
    public function radioList($name, $text = null) {
      $radio = new RadioList();
      $radio->setName($name);
      $radio->setText($text);
      $this->addElement($radio);
      return $radio;
    }


    /**
     * @deprecated
     * @see TextArea
     *
     * @param string $name
     * @param null $text
     * @return TextArea
     */
    public function textarea($name, $text = null) {
      trigger_error('Deprecated. Create new element and add it to the form manually', E_USER_DEPRECATED);
      $input = new TextArea($name);
      if (!empty($text)) {
        $input->setText((string) $text);
      }
      $this->addElement($input);
      return $input;
    }
    

    /**
     * ```
     * $form->submit('register', 'зареєструватись');
     * ```
     * @param string $name
     * @param null $value
     * @return Submit
     */
    public function submit($name, $value = null) {
      $input = new Submit();
      $input->setName($name);
      $input->setValue($value);
      $this->addElement($input);
      return $input;
    }


    /**
     * ```
     * $form->checkbox('subscribe', 'Підписка на новини');
     * ```
     * @param string $name
     * @param string|null $label
     * @return Checkbox
     */
    public function checkbox($name, $label = null) {
      trigger_error('Deprecated', E_USER_DEPRECATED);
      $checkbox = new Checkbox($name, $label);
      $this->addElement($checkbox);
      return $checkbox;
    }


    /**
     * @param string $name
     * @param null $text
     * @return CheckboxList
     */
    public function checkboxList($name, $text = null) {
      $checkbox = new CheckboxList();
      $checkbox->setName($name);
      $checkbox->setText($text);
      $this->addElement($checkbox);
      return $checkbox;
    }


    /**
     * Render full form
     *
     * @return string
     */
    public function render() {
      return $this->renderStart() . $this->renderElements() . $this->renderEnd();
    }


    /**
     * You can easy rewrite this method for custom design of your forms
     *
     * @return string
     */
    protected function renderElements() {
      $formHtml = '<dl>';

      foreach ($this->getElements() as $element) {
        # skip hidden element
        if ($element instanceof Element\Hidden) {
          continue;
        }

        if ($element instanceof Element\Input and $element->getType() === 'hidden') {
          continue;
        }

        $elementText = '';

        if ($element instanceof Element\BaseElement) {
          $elementText = $element->getText();
        }

        $formHtml .=
          '<dt>' . $elementText . '</dt>' .
          '<dd>' . $element->render() . '</dd>';
      }

      $formHtml .= '</dl>';
      return $formHtml;
    }


    /**
     * @return string
     */
    public function renderStart() {
      $hidden = new Element\Input();
      $hidden->setType('hidden');
      $hidden->addAttributes([
        'name' => $this->getUid(),
      ]);
      $hidden->setValue(1);


      # get default attribute
      $method = $this->getMethod();
      $this->setAttribute('method', $method);

      $html = '<form ' . Element\Html::renderAttributes($this->getAttributes()) . '>';
      $html .= $hidden->render();

      # render hidden element
      foreach ($this->getElements() as $element) {
        if ($element instanceof Element\Input and $element->getType() === 'hidden') {
          $html .= $element->render();
        }

        if ($element instanceof Element\Hidden) {
          $html .= $element->render();
        }

      }


      return $html;
    }


    /**
     * @return string
     */
    public function renderEnd() {
      return '</form>';
    }
  }