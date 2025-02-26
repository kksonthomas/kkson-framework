<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\FieldType\FieldType;

class PasswordWithConfirm extends FieldType
{

    protected $type = "password";

    /**
     * @var callback
     */
    private $encryptionClosure = null;

    public function __construct()
    {

        // Use MD5 by default
        $this->encryptionClosure = function ($v) {
            return password_hash($v, PASSWORD_DEFAULT);
        };

    }

    /**
     * Render Field for Create/Edit
     * @param bool|true $echo
     * @return string
     */
    public function render($echo = false)
    {
        $name = $this->field->getName();
        $display = $this->field->getDisplayName();
        $readOnly = $this->getReadOnlyString();
        $required = $this->getRequiredString();
        $type = $this->type;
        $crud = $this->field->getCRUD();
        $isEditing = !!$crud->getBean();
        $value = $isEditing ? "" : $this->getValue();
        $editingText = $isEditing ? "(如非更改請留空白)" : "";

        $html = <<< HTML
        <div class="form-group">
            <label for="field-$name">$display $editingText</label> <input id="field-$name" class="form-control"  type="$type" name="$name" value="$value" $readOnly $required autocomplete="new-password"/>
        </div>

         <div class="form-group">
            <label for="field-$name-confirm">確認 $display $editingText</label> <input id="field-$name-confirm" class="form-control"  type="$type" value="$value" $readOnly $required />
        </div>

HTML;

        $crud->addBodyEndHTML(<<< HTML
<script>
    $(document).ready(function () {
        crud.addValidator(function (data) {
            if ($("#field-$name-confirm").val() != $("#field-$name").val()) {
                crud.addErrorMsg("確認的密碼不相同");
                return false;
            } 
            
            if ($("#field-$name").val() > 0 && $("#field-$name").val().length < 6) {
                crud.addErrorMsg("密碼長度太短.");
                return false;
            } 
            
            return true;
        });
    });
</script>
HTML
        );

        if ($echo) {
            echo $html;
        }

        return $html;
    }

    /**
     * @param callback $c function ($value) { return md5($value); }
     */
    public function setEncryptionClosure($c)
    {
        $this->encryptionClosure = $c;
    }

    public function beforeStoreValue($valueFromUser)
    {
        if ($valueFromUser == "") {
            $bean = $this->field->getBean();
            if($bean) {
                return $bean->{$this->field->getName()};
            } else {
                return null;
            }
        }

        $c = $this->encryptionClosure;
        return $c($valueFromUser);
    }

    public function renderCell($value)
    {
        return "***";
    }


}