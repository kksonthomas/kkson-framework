// @ts-nocheck
/**
 * Created by Louis Lam on 8/15/2015.
 *
 */
/// <reference path="../../../vendor/almasaeed2010/adminlte/plugins/jquery/jquery.min.js" />
/// <reference path="../../../vendor/almasaeed2010/adminlte/plugins/sweetalert2/sweetalert2.all.min.js" />
var KKsonCRUD = /** @class */ (function () {
    function KKsonCRUD() {
        this.validateFunctions = [];
        this.errorMsgs = [];
        this.isUploading = false;
        var self = this;
        $(document).ready(function () {
            // Init Select2 !
            $(".select2").select2();
            // To style only <select>s with the selectpicker class
            // $('.selectpicker').selectpicker();
            // Disable Datatables' alert!
            // $.fn.dataTableExt.sErrMode = 'throw';
            $(document).on('click', '[data-toggle="lightbox"]', function (event) {
                event.preventDefault();
                $(this).ekkoLightbox();
            });
            // Show confirmation before leaving page with unsaved changes
            var formChanged = false;
            // Track form changes
            $("#kkson-form :input").on("change", function () {
                formChanged = true;
            });
            // Handle form submission
            $("#kkson-form").on("submit", function () {
                formChanged = false;
            });
            // Show confirmation dialog when leaving with unsaved changes
            window.addEventListener("beforeunload", function (e) {
                if (formChanged) {
                    e.preventDefault();
                    e.returnValue = "您有未儲存的變更，確定要離開此頁面嗎？";
                    return e.returnValue;
                }
            });
            // Ajax Submit Form
            $("form.ajax").submit(function (e) {
                e.preventDefault();
                if (self.isUploading) {
                    alert2("Uploading image(s), please wait.");
                    return;
                }
                // Clear all msgs
                self.errorMsgs = [];
                var ok = true;
                var data = {};
                var serialArray = $("#kkson-form").serializeArray();
                $.each(serialArray, function () {
                    data[this.name] = this.value;
                });
                // Validate
                for (var i = 0; i < self.validateFunctions.length; i++) {
                    if (self.validateFunctions[i](data) === false) {
                        ok = false;
                    }
                }
                if (!ok) {
                    var str = "";
                    for (var i = 0; i < self.errorMsgs.length; i++) {
                        str += self.errorMsgs[i] + "\n";
                    }
                    AlertUtils.showError("錯誤", str);
                    return false;
                }
                // Create Form Data from the form.
                // if ($(this).attr("enctype") !== "undefined") {
                //data = new FormData($(this)[0]);
                var reqData = $(this).serialize();
                reqData["csrf_token"] = (csrfToken) ? csrfToken : "";
                $.ajax({
                    url: $(this).attr("action"),
                    type: $(this).data("method"),
                    data: reqData
                }).done(function (result) {
                    if (self.ajaxFormCallback != null) {
                        self.ajaxFormCallback(result);
                    }
                });
                return false;
            });
            var pathnameWithGet = decodeURI(location.pathname + location.search);
            // console.log(pathnameWithGet);
            $(".main-sidebar .nav-item").removeClass("menu-open");
            $(".main-sidebar .nav-link").removeClass("active").each(function () {
                var altHrefs = $(this).data("alt-hrefs");
                var altOptions = $(this).data("alt-options");
                var hrefs = [$(this).attr("href")].concat(altHrefs || []);
                var options = [$(this).attr('class').split(/\s+/)].concat(altOptions || []);
                var isActive = false;
                for (var i = 0; i < hrefs.length; i++) {
                    var href = hrefs[i];
                    var option = options[i];
                    var isMatchGet = option.indexOf("match_get") >= 0;
                    var isCrud = option.indexOf("crud") >= 0;
                    var baseHref = href.split('?')[0];
                    var queryString = href.split('?')[1];
                    if (isCrud) {
                        var baseCrudHref = baseHref.replace(/(.*)(\/create|\/edit|\/list)(.*)$/, '$1{{crud}}$3');
                        var createCrudHref = baseCrudHref.replace('{{crud}}', '/create');
                        var editCrudHref = baseCrudHref.replace('{{crud}}', '/edit');
                        var listCrudHref = baseCrudHref.replace('{{crud}}', '/list');
                        isActive = location.pathname.indexOf(createCrudHref) >= 0 || location.pathname.indexOf(editCrudHref) >= 0 || location.pathname.indexOf(listCrudHref) >= 0;
                        if (isMatchGet) {
                            isActive &= location.search === "?" + queryString;
                        }
                    }
                    else {
                        if (isMatchGet) {
                            isActive = pathnameWithGet === decodeURI(href);
                        }
                        else {
                            isActive = location.pathname.indexOf(href) >= 0;
                        }
                    }
                    if (isActive) {
                        $(this).addClass("active").parents(".nav-treeview").show().siblings("a.nav-link").addClass("active").closest(".nav-item").addClass("menu-open menu-is-opening");
                        // Stop propagation to prevent parent menu items from being activated
                        return false;
                    }
                }
            });
            self.refresh();
        });
    }
    KKsonCRUD.prototype.setUploading = function (val) {
        this.isUploading = val;
    };
    KKsonCRUD.prototype.addValidator = function (func) {
        this.validateFunctions.push(func);
    };
    KKsonCRUD.prototype.addErrorMsg = function (msg) {
        this.errorMsgs.push(msg);
    };
    KKsonCRUD.prototype.getDataTable = function () {
        return this.table;
    };
    KKsonCRUD.prototype.mergeObject = function (obj1, obj2) {
        var obj3 = {};
        for (var attrname in obj1) {
            obj3[attrname] = obj1[attrname];
        }
        for (var attrname in obj2) {
            obj3[attrname] = obj2[attrname];
        }
        return obj3;
    };
    /**
     *
     * @param isAjax
     * @param tableURL
     * @param enableSearch
     * @param enableSorting
     * @param {} customData
     */
    KKsonCRUD.prototype.initListView = function (ajaxOptions, tableURL, enableSearch, enableSorting, customData) {
        var _this = this;
        if (enableSearch === void 0) { enableSearch = true; }
        if (enableSorting === void 0) { enableSorting = true; }
        if (customData === void 0) { customData = null; }
        var self = this;
        var data = {
            "pageLength": 25,
            "paging": true,
            "ordering": enableSorting,
            "autoWidth": false,
            "searching": enableSearch,
            "info": true,
            "drawCallback": function (settings) {
                self.refresh();
            },
            "bStateSave": true,
            "fnStateSave": function (oSettings, oData) {
                localStorage.setItem('DataTables_' + window.location.pathname, JSON.stringify(oData));
            },
            "fnStateLoad": function (oSettings) {
                return JSON.parse(localStorage.getItem('DataTables_' + window.location.pathname));
            }
        };
        if (customData != null) {
            data = this.mergeObject(data, customData);
        }
        if (!!ajaxOptions) {
            data.serverSide = true;
            data.processing = true;
            //data.searching = true;
            data.ajax = this.mergeObject({
                url: tableURL,
                type: "POST",
                data: {
                    "csrf_token": (csrfToken) ? csrfToken : "",
                }
            }, ajaxOptions);
        }
        $(document).ready(function () {
            _this.table = $('#kkson-crud-table').DataTable(data);
            // Go to the first page if out of range after searching
            _this.table.on("xhr", function (e, settings, json, xhr) {
                var info = _this.table.page.info();
                if (info.pages < info.page) {
                    _this.table.page(1).draw(1);
                }
            });
            // Column Filter
            _this.columnFilter();
        });
    };
    KKsonCRUD.prototype.columnFilter = function () {
        var self = this;
        $(".column-filter a").click(function (e) {
            e.stopPropagation();
        });
        $(".column-filter [type=checkbox]").change(function (e) {
            e.preventDefault();
            var checked = $(this).is(":checked");
            var column = self.table.column($(this).data('column'));
            column.visible(checked);
        });
    };
    KKsonCRUD.prototype.setAjaxFormCallback = function (callback) {
        this.ajaxFormCallback = callback;
    };
    KKsonCRUD.prototype.refresh = function () {
        // Delete Button
        $(".btn-delete:not(.ok)").click(function () {
            var _this = this;
            AlertUtils.showWarning("刪除", "確定要刪除此記錄?").then(function (v) {
                if (v.isConfirmed) {
                    var btn = $(_this);
                    var deleteLink = $(_this).data("url");
                    var reqData = {};
                    reqData["csrf_token"] = (csrfToken) ? csrfToken : "";
                    $.ajax({
                        url: deleteLink,
                        type: "DELETE",
                        data: reqData,
                        dataType: 'json',
                    }).done(function (data) {
                        //btn.parents('tr').remove();
                        if (data.status == 'succ') {
                            AlertUtils.showSuccess("刪除記錄成功");
                            crud.getDataTable().ajax.reload();
                        }
                        else {
                            AlertUtils.showError("錯誤", "刪除記錄失敗: <br>" + data.error);
                        }
                    }).fail(function (data) {
                        AlertUtils.showError("錯誤", "刪除記錄失敗" + data);
                    });
                }
            });
        }).addClass("ok");
        // Confirm Button
        $(".btn-confirm").click(function (e) {
            e.preventDefault();
            var result = window.confirm($(this).data("msg"));
            if (result) {
                location.href = $(this).attr("href");
            }
        });
    };
    KKsonCRUD.prototype.field = function (name) {
        return $("#field-" + name);
    };
    return KKsonCRUD;
}());
