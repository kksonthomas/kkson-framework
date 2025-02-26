// @ts-nocheck
/**
 * Created by Louis Lam on 8/15/2015.
 *
 */
/// <reference path="../../../vendor/almasaeed2010/adminlte/plugins/jquery/jquery.min.js" />
/// <reference path="../../../vendor/almasaeed2010/adminlte/plugins/sweetalert2/sweetalert2.all.min.js" />

class KKsonCRUD {

    private table;

    private ajaxFormCallback;

    private validateFunctions = [];

    private errorMsgs = [];

    private isUploading: boolean = false;

    public setUploading(val: boolean): void {
        this.isUploading = val;
    }

    constructor() {
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
            let formChanged = false;
    
            // Track form changes
            $("#kkson-form :input").on("change", function() {
                formChanged = true;
            });
            
            // Handle form submission
            $("#kkson-form").on("submit", function() {
                formChanged = false;
            });
            
            // Show confirmation dialog when leaving with unsaved changes
            window.addEventListener("beforeunload", function(e) {
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

                let ok = true;

                let data = {};
                let serialArray = $("#kkson-form").serializeArray();

                $.each(serialArray, function () {
                    data[this.name] = this.value;
                });

                // Validate
                for (let i = 0; i < self.validateFunctions.length; i++) {
                    if (self.validateFunctions[i](data) === false) {
                        ok = false;
                    }
                }

                if (!ok) {
                    let str = "";
                    for (let i = 0; i < self.errorMsgs.length; i++) {
                        str += self.errorMsgs[i] + "\n";
                    }
                    alertError(str);
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

            let pathnameWithGet = decodeURI(location.pathname + location.search);
            // console.log(pathnameWithGet);
            $(".main-sidebar .nav-item").removeClass("menu-open");
            $(".main-sidebar .nav-link").removeClass("active").each(function () {
                let altHrefs = $(this).data("alt-hrefs");
                let altOptions = $(this).data("alt-options");

                let hrefs = [$(this).attr("href")].concat(altHrefs || []);
                let options = [$(this).attr('class').split(/\s+/)].concat(altOptions || []);

                let isActive = false;

                for (let i = 0; i < hrefs.length; i++) {
                    let href = hrefs[i];
                    let option = options[i];

                    let isMatchGet = option.indexOf("match_get") >= 0;
                    let isCrud = option.indexOf("crud") >= 0;

                    let baseHref = href.split('?')[0];
                    let queryString = href.split('?')[1];

                    if (isCrud) {
                        let baseCrudHref = baseHref.replace(/(.*)(\/create|\/edit|\/list)(.*)$/, '$1{{crud}}$3');
                        let createCrudHref = baseCrudHref.replace('{{crud}}', '/create');
                        let editCrudHref = baseCrudHref.replace('{{crud}}', '/edit');
                        let listCrudHref = baseCrudHref.replace('{{crud}}', '/list');

                        isActive = location.pathname.indexOf(createCrudHref) >= 0 || location.pathname.indexOf(editCrudHref) >= 0 || location.pathname.indexOf(listCrudHref) >= 0;
                        if (isMatchGet) {
                            isActive &= location.search === "?" + queryString;
                        }
                    } else {
                        if (isMatchGet) {
                            isActive = pathnameWithGet === decodeURI(href);
                        } else {
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


    public addValidator(func) {
        this.validateFunctions.push(func);
    }

    public addErrorMsg(msg) {
        this.errorMsgs.push(msg);
    }

    public getDataTable() {
        return this.table;
    }

    public mergeObject(obj1, obj2) {
        var obj3 = {};
        for (var attrname in obj1) { obj3[attrname] = obj1[attrname]; }
        for (var attrname in obj2) { obj3[attrname] = obj2[attrname]; }
        return obj3;
    }

    /**
     *
     * @param isAjax
     * @param tableURL
     * @param enableSearch
     * @param enableSorting
     * @param {} customData
     */
    public initListView(ajaxOptions, tableURL: string, enableSearch: boolean = true, enableSorting: boolean = true, customData = null) {
        let self = this;

        let data: {} = {
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

        $(document).ready(() => {
            this.table = $('#kkson-crud-table').DataTable(data);

            // Go to the first page if out of range after searching
            this.table.on("xhr", (e, settings, json, xhr) => {
                let info = this.table.page.info();

                if (info.pages < info.page) {
                    this.table.page(1).draw(1);
                }
            });

            // Column Filter
            this.columnFilter();
        });
    }

    public columnFilter() {
        let self = this;

        $(".column-filter a").click(function (e) {
            e.stopPropagation();
        });

        $(".column-filter [type=checkbox]").change(function (e) {
            e.preventDefault();

            let checked = $(this).is(":checked");

            let column = self.table.column($(this).data('column'));
            column.visible(checked);
        });
    }

    public setAjaxFormCallback(callback) {
        this.ajaxFormCallback = callback;
    }

    public refresh() {
        // Delete Button
        $(".btn-delete:not(.ok)").click(function () {
            AlertUtils.showWarning("刪除","確定要刪除此記錄?").then(v => {
                if (v.isConfirmed) {
                    let btn = $(this);
                    let deleteLink = $(this).data("url");

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
                        } else {
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

            let result = window.confirm($(this).data("msg"));

            if (result) {
                location.href = $(this).attr("href");
            }
        });

    }

    public field(name) {
        return $("#field-" + name);
    }
}

