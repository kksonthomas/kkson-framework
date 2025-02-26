$.extend( $.fn.dataTable.defaults, {
    "language": {
        "url": "/vendor/kksonthomas/kkson-framework/lib/datatables/i18n/zh-HANT.json"
    }
});

(()=>{
    function AlertUtilsClass() {
        this.icon = {
            success: "success",
            error: "error",
            warning: "warning",
            info: "info",
            question : "question"
        };
        this.show = async function (title, content = null, options) {
            options = {...{
                title: title,
                html: content,
                confirmButtonText: "確定",
                cancelButtonText: "取消"
            } , ...options};
            return Swal.fire(options);
        };

        this.showError = function (title, content = null, options) {
            return this.show(title, content, {...{
                    icon: this.icon.error
                }, ...options
            });
        };

        this.showSuccess = function (title, content = null, options) {
            return this.show(title, content, {...{
                    icon: this.icon.success
                }, ...options
            });
        };

        this.showWarning = function (title, content = null, options) {
            return this.show(title, content, {...{
                    icon: this.icon.warning
                }, ...options
            });
        };

        this.showInfo = function (title, content = null, options) {
            return this.show(title, content, {...{
                    icon: this.icon.info
                }, ...options
            });
        };

        this.showConfirm = function (title, content = null, options) {
            return this.show(title, content, {...{
                    icon: this.icon.question,
                    showCancelButton: true,
                    focusCancel: true
                }, ...options
            });
        };

        this.showPrompt = function (title, content = null, options) {
            return this.show(title, content, {...{
                    icon: this.icon.question,
                    input: "text",
                    showCancelButton: true,
                    focusCancel: true
                }, ...options
            });
        };
    }
    window.AlertUtils = new AlertUtilsClass();

    function ToastUtilsClass () {
        toastr.options = {
            "closeButton": false,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        this.show = function(msg, type) {
            toastr[type](msg);
        };

        this.showSuccess = function(msg) {
            this.show(msg, "success");
        };
        this.showInfo = function(msg) {
            this.show(msg, "info");
        };
        this.showError = function(msg) {
            this.show(msg, "error");
        };
    }
    window.ToastUtils = new ToastUtilsClass();
    function StringUtilsClass() {
        let currencyFormatter = (new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        }));

        this.formatCurrency = function (value) {
            return currencyFormatter.format(value)
        };
    }
    window.StringUtils = new StringUtilsClass();
})();



$(function() {
    $("body").on("click", "#kkson-crud-table_ellipsis>a", function() {
        const info = crud.table.page.info();
        const d = {
            title: "跳至頁面",
            input: "number",
            inputAttributes: {
                min: 1,
                max: info.pages,
                step: 1
            },
            inputValue: info.page + 1,
            showCancelButton: true,
            cancelButtonText: "取消",
            confirmButtonText: "確定",
            // focusCancel: true
        };

        Swal.fire(d).then((v) => {
            if(v && v.value !== "") {
                crud.table.page(v.value-1).draw(false)
            }
        });
    });

    let formatNumber = function(n) {
        // format number 1000000 to 1,234,567
        return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
    };

    let formatCurrency = function(input, blur) {
        // appends $ to value, validates decimal side
        // and puts cursor back in right position.

        // get input value
        let input_val = input.val();

        // don't validate empty input
        if (input_val === "") { return; }

        const is_negative = input_val.indexOf("-") === 0;

        const negative_sign = is_negative ? '-' : '';

        // original length
        const original_len = input_val.length;

        // initial caret position
        let caret_pos = input.prop("selectionStart");

        // check for decimal
        if (input_val.indexOf(".") >= 0) {

            // get position of first decimal
            // this prevents multiple decimals from
            // being entered
            const decimal_pos = input_val.indexOf(".");

            // split number by decimal point
            let left_side = input_val.substring(0, decimal_pos);
            let right_side = input_val.substring(decimal_pos);

            // add commas to left side of number
            left_side = formatNumber(left_side);

            // validate right side
            right_side = formatNumber(right_side).replace(",", "");

            // On blur make sure 2 numbers after decimal
            if (blur === "blur") {
                right_side += "00";
            }

            // Limit decimal to only 2 digits
            right_side = right_side.substring(0, 2);

            // join number by .
            input_val = negative_sign + "$" + left_side + "." + right_side;

        } else {
            // no decimal entered
            // add commas to number
            // remove all non-digits
            input_val = formatNumber(input_val);
            input_val = negative_sign + "$" + input_val;

            // final formatting
            if (blur === "blur") {
                input_val += ".00";
            }
        }

        // send updated string to input
        input.val(input_val);

        // put caret back in the right position
        const updated_len = input_val.length;
        caret_pos = updated_len - original_len + caret_pos;
        input[0].setSelectionRange(caret_pos, caret_pos);
    };


    //curreny formatter codes
    $(document).on("keyup", "input[data-type='currency']", function () { formatCurrency($(this)); });
    $(document).on("blur", "input[data-type='currency']", function () { formatCurrency($(this), "blur"); });

    $('[data-toggle="switch"]').bootstrapSwitch();
});