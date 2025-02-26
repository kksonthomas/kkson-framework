<?php

namespace KKsonFramework\CRUD\FieldType;

use KKsonFramework\Utils\DateUtils;

class DatePicker extends TextField
{

    const defaultOptions = [
        "system" => [
            "date_format" => "YYYY-MM-DD",
            "datetime_format" => "YYYY-MM-DD HH:mm:ss",
            "separator" => " - "
        ],
        "locale" => [
            "format" => "YYYY年M月D日",
            "separator" => " - ",
            "applyLabel" => "確定",
            "cancelLabel" => "清除",
            "fromLabel" => "由",
            "toLabel" => "至",
            "customRangeLabel" => "Custom",
            "weekLabel" => "W",
            "daysOfWeek" => [
                "日",
                "一",
                "二",
                "三",
                "四",
                "五",
                "六"
            ],
            "monthNames" => [
                "1月",
                "2月",
                "3月",
                "4月",
                "5月",
                "6月",
                "7月",
                "8月",
                "9月",
                "10月",
                "11月",
                "12月"
            ],
            "firstDay" => 1,
            "autoUpdateInput" => false
        ]
    ];
    /**
     * @var array
     */
    protected $options;

    public function __construct($options = [])
    {
        $classOptions = [
            "singleDatePicker" => true,
            "showDropdowns" => true,
        ];
        $this->options = array_merge(self::defaultOptions, $classOptions, $options);
    }

    public function beforeRenderValue($valueFromDatabase)
    {
        return DateUtils::toChineseFormatDate($valueFromDatabase);
    }

    public function beforeStoreValue($valueFromUser)
    {
        return DateUtils::createFromChineseFormatDate($valueFromUser);
    }

    public function renderCell($value)
    {
        if($value) {
            return DateUtils::toChineseFormatDate($value);
        } else {
            return "";
        }
    }


    public function render($echo = false)
    {
        $name = $this->field->getName();
        $display = $this->field->getDisplayName();
        $value = $this->getValue();
        $readOnly = $this->getReadOnlyString();
        $disabled = $this->getDisabledString();
        $required = $this->getRequiredString();
        $star = $this->getRequiredStar();
        $type = $this->type;

        $html  = <<< EOF
        <div class="form-group">
            <label>$star $display</label>
            <div class="input-group">
                <input type="text" class="form-control" id="field-$name"  name="$name" value="$value" $readOnly $required $disabled />
                <div class="input-group-append">
                    <div class="input-group-text">
                        <i class="fa fa-calendar"></i>
                    </div>
                </div>
            </div>
        </div>
EOF;

        if ($echo)
            echo $html;

        $options = json_encode($this->options);
        $this->field->getCRUD()->addBodyEndHTML(<<< HTML
           <script type="text/javascript">
            $(function () {
                let field = $('#field-$name');
                let initValue = field.val();
                field.daterangepicker($options, function(start, end, label) {
                    
                }).on('apply.daterangepicker', function(ev, picker) {
                    if(!picker.singleDatePicker) {
                        $(this).val(picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format));    
                    } else {
                        $(this).val(picker.startDate.format(picker.locale.format));
                    }
                }).on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                }).val(initValue);
            });
        </script>
HTML
        );
        return $html;
    }


}