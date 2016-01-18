<?php

  namespace FormTests\Form\Element;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 9/17/14
   */
  class HtmlTest extends \FormTests\Main {

    public function testAddClass() {
      $htmlElement = new \Fiv\Form\Element\Html();

      $currentClassName = $htmlElement->getAttribute('class');

      $this->assertEmpty($currentClassName);

      $htmlElement->addClass("test");
      $this->assertEquals("test", $htmlElement->getAttribute('class'));

      $htmlElement->setClass("");
      $this->assertEmpty($htmlElement->getAttribute('class'));

      $htmlElement->setClass("custom_class");
      $this->assertEquals("custom_class", $htmlElement->getAttribute('class'));

      $htmlElement->addClass("other_class");
      $this->assertEquals("custom_class other_class", $htmlElement->getAttribute('class'));
    }

    public function testTag() {

      $imgHtml = \Fiv\Form\Element\Html::tag('img', array(
        'src' => "/images/logo.png",
        'title' => "logo"
      ));

      $this->assertTrue((boolean)preg_match("!/>!", $imgHtml));
      $this->assertTrue((boolean)preg_match("!title=!", $imgHtml));
      $this->assertTrue((boolean)preg_match("!src=!", $imgHtml));
    }

  }
 