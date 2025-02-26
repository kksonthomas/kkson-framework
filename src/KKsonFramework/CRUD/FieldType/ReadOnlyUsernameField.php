<?php

namespace KKsonFramework\CRUD\FieldType;

use KKsonFramework\CRUD\FieldType\FieldType;
use KKsonFramework\RedBeanPHP\Model\User;

class ReadOnlyUsernameField extends FieldType
{

    protected $type = "text";
    protected $prefix = null;
    protected $postfix = null;

    /**
     * Render Field for Create/Edit
     * @param bool|true $echo
     * @return string
     */
    public function render($echo = false)
    {
        $name = $this->field->getName();
        $display = $this->field->getDisplayName();
        $value = $this->getValue();
//        $readOnly = $this->getReadOnlyString();
//        $disabled = $this->getDisabledString();
        $readOnly = "readonly";
        $disabled =  "disabled";
        $required = $this->getRequiredString();
        $star = $this->getRequiredStar();
        $type = $this->type;


        if ($this->prefix == null && $this->postfix == null) {
            $inputGroupOpenTag = "";
            $inputGroupEndTag = "";
        } else {
            $inputGroupOpenTag = "<div class=\"input-group\">";
            $inputGroupEndTag = "</div>";
        }


        if ($this->prefix != null) {
            $prefixHTML = " <span class=\"input-group-addon\" >$this->prefix</span>";
        } else {
            $prefixHTML = "";
        }

        if ($this->postfix != null) {
            $postfixHTML = " <span class=\"input-group-addon\" >$this->postfix</span>";
        } else {
            $postfixHTML = "";
        }


        $user = User::load($value, true);
        $username = @$user->username;

        $html  = <<< EOF
        <div class="form-group">
            <label for="field-$name">$star $display</label> 
             $inputGroupOpenTag
                $prefixHTML
                <input id="field-$name" class="form-control"  type="$type" name="$name" value="$username" $readOnly $required $disabled />
                $postfixHTML
            $inputGroupEndTag
        </div>
EOF;

        if ($echo)
            echo $html;

        return $html;
    }

    /**
     * @param null $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @param null $postfix
     */
    public function setPostfix($postfix)
    {
        $this->postfix = $postfix;
        return $this;
    }

    public function renderCell($value)
    {
        $user = User::load($value, true);
        $username = @$user->username;
        $isDeletedText = "";
        if(@$user->_deleted) {
            $isDeletedText = " (Deleted)";
        }
        return $username ? $username.$isDeletedText : $value;
    }


}