<?php

namespace KKsonFramework\CRUD\FieldType;


use KKsonFramework\CRUD\Exception\DirectoryPermissionException;
use KKsonFramework\Utils\UrlUtils;
use Stringy\Stringy;

class FileType extends FieldType
{

    private $uploadPath;

    protected $inputType = "file";
    protected $additionalAttr = "";

    public function __construct($uploadPath = "upload/")
    {
        $this->setUploadPath($uploadPath);

        // Create a directory for Upload Path
        if (! file_exists($this->getUploadPath())) {
            mkdir($this->getUploadPath(), 0777, true);
        } else {
            //chmod($this->getUploadPath(), 0777);
        }

        // Check the directory permission
        if (! is_writable($this->getUploadPath())) {
            throw new DirectoryPermissionException($this->getUploadPath());
        }
    }

    public function getPreviewHTMLTemplate() {
        return '<a href="{fileURL}" target="_blank" class="btn btn-primary">Open ({fileURL})</a>';
    }

    public function render($echo = false)
    {
        $name = $this->field->getName();
        $display = $this->field->getDisplayName();
        $value = $this->getValue();
        $readOnly = $this->getReadOnlyString();
        $required = $this->getRequiredString();
        $crud = $this->field->getCRUD();
        $inputType = $this->inputType;
        $additionalAttr = $this->additionalAttr;

        $uploadURL = UrlUtils::url("kkson-crud/upload/json?fullpath=no", true, false);
        $previewTemplate = $this->getPreviewHTMLTemplate();
        $previewTemplateEncoded = json_encode($previewTemplate);

        if ($value != "" && $value != null) {
            $fileURL = UrlUtils::res($value);
            $previewHTML = Stringy::create($previewTemplate)->replace("{fileURL}", $fileURL);
            $hideRemoveButton = "";
        } else {
            $previewHTML = "";
            $hideRemoveButton = 'style="display: none"';
        }

        $html = <<< HTML
<div class="form-group">
    <label for="exampleInputFile">$display</label>
    <div class="input-group">
        <div class="custom-file">
            <input type="$inputType" class="custom-file-input" id="upload-$name" $readOnly data-required="$required" $additionalAttr />
            <input id="field-$name" type="hidden" name="$name" value="$value"  />
            <label class="custom-file-label" for="upload-$name">Choose File</label>
        </div>
    </div>
    
    <div id="image-preview-$name" class="image-preview my-1">
        $previewHTML
    </div>
        
    <button id="image-remove-$name" type="button" class="btn btn-danger" $hideRemoveButton>Remove File</button>
</div>

HTML;

        $uploadPath = $this->getUploadPath();
        $crud->addBodyEndHTML(<<< HTML
    <script>
        $("#image-remove-$name").click(function () {
            $("#image-preview-$name").html("");
            $("#field-$name").val("");
    
            let required = $("#upload-$name").data("required");
    
            if (required === "required") {
                $("#upload-$name").attr("required", true);
            }
    
            $(this).hide();
        });

        $("#upload-$name").change(function () {

            if ($(this).val() == "") {
                return;
            }
            
            var data = new FormData();
            
            jQuery.each($(this)[0].files, function(i, file) {
                data.append("upload", file);
            });
            
            //add custom path
            data.append("uploadpath", "$uploadPath");
            data.append("csrf_token", csrfToken);
            
            crud.setUploading(true);
            $.ajax({
                url: '$uploadURL',
                data: data,
                cache: false,
                contentType: false,
                processData: false,
                type: 'POST',
                success: function (data) {
                    crud.setUploading(false); 
                    let previewElement = $($previewTemplateEncoded.split("{fileURL}").join(RES_URL + data.url)); 
                
                    $("#image-preview-$name").html(previewElement);
                    $("#field-$name").val(data.url);
                    $("#image-remove-$name").show();
                
                    $("#upload-$name").removeAttr("required");
                }
            });
        });
    </script>

HTML
        );

        if ($echo)
            echo $html;

        return $html;
    }

    /**
     * @return string
     */
    public function getUploadPath()
    {
        return $this->uploadPath;
    }

    /**
     * @param string $uploadPath
     */
    public function setUploadPath($uploadPath)
    {
        // TODO: Append slash if no end slash
        $this->uploadPath = $uploadPath;
    }

    public function renderCell($value)
    {
        $imgURL = htmlspecialchars(UrlUtils::res($value));

        if ($value != null && $value != "") {
            return <<< HTML
<a target="_blank" href="$imgURL">Open File</a>
HTML;
        } else {
            return "";
        }


    }


}