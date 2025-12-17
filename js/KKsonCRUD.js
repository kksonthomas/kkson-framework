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
        this.initListViewCustomData = {};
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
                }).fail(function (data) {
                    AlertUtils.showError("錯誤", "發生未預期的錯誤<br>請稍後再試 或 聯絡管理員");
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
    KKsonCRUD.prototype.setInitListViewCustomData = function (data) {
        this.initListViewCustomData = data;
    };
    /**
     *
     * @param ajaxOptions
     * @param ajaxUrl
     * @param enableSearch
     * @param enableSorting
     * @param enableColSearch
     * @param customData
     */
    KKsonCRUD.prototype.initListView = function (config) {
        var _this = this;
        var self = this;
        var data = {
            "pageLength": 25,
            "paging": true,
            "ordering": config.enableSorting,
            "autoWidth": false,
            "searching": config.enableSearch,
            "info": true,
            "drawCallback": function (settings) {
                self.crudDropdownEventInit();
                self.dtStickyScrollbarDrawCallback();
                self.refresh();
            },
            "bStateSave": true,
            "scrollX": true,
            "colReorder": {
                "headerRows": [0]
            },
            "fixedHeader": {
                "headerOffset": $(".kkson-crud-table-header").outerHeight() + $(".main-header").outerHeight() - 5
            },
            "fixedColumns": {
                "left": 2
            },
            "initComplete": function (settings) {
                var api = settings.api;
                $('.dt-paging').first().appendTo('.ext-dt-paging');
                $(".buttons-colvis").first().appendTo('.crud-dt-colFilter-container');
                if (config.enableColSearch) {
                    self.colSearchInit(api);
                }
            },
            "layout": {
                "topStart": {
                    "pageLength": true,
                    "buttons": [
                        {
                            "extend": "colvis",
                            "columns": ":not(.noVis)",
                            "popoverTitle": "欄位顯示設定",
                            "prefixButtons": [{
                                    text: '全部',
                                    action: function (e, dt, node, config) {
                                        var colCount = crud.table.columns().count();
                                        crud.table.columns(Array.from({ length: colCount }, function (_, i) { return i; })).visible(this.active());
                                        this.active(!this.active());
                                        crud.table.columns.adjust();
                                        crud.table.draw();
                                    }
                                }]
                        }
                    ]
                },
                "bottomEnd": {
                    "paging": {
                        "previousNext": false
                    }
                },
                "topEnd": {
                    "paging": {
                        "previousNext": false
                    }
                },
            }
        };
        if (this.initListViewCustomData != null) {
            data = this.mergeObject(data, this.initListViewCustomData);
        }
        if (config.customData != null) {
            if (typeof config.customData === "function") {
                data = this.mergeObject(data, config.customData(data));
            }
            else {
                data = this.mergeObject(data, config.customData);
            }
        }
        if (!!config.ajaxOptions) {
            data.serverSide = true;
            data.processing = true;
            //data.searching = true;
            data.ajax = this.mergeObject({
                url: config.ajaxUrl,
                type: "POST",
                data: {
                    "csrf_token": (csrfToken) ? csrfToken : "",
                }
            }, config.ajaxOptions);
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
            // Refresh Button
            $(".btnRefreshDatatable").click(function () {
                self.table.ajax.reload(function () {
                    ToastUtils.showSuccess("重新整理成功");
                });
            });
        });
    };
    KKsonCRUD.prototype.crudDropdownEventInit = function () {
        $(crud.table.containers()[0]).find(".kkson-crud-dropdown").on('show.bs.dropdown', function () {
            if (!$(this).data("crud-dropdown-menu-relocated")) {
                var menu = $(this).find(".dropdown-menu");
                menu.appendTo("body");
            }
            $(this).data("crud-dropdown-menu-relocated", true);
        });
    };
    KKsonCRUD.prototype.dtStickyScrollbarDrawCallback = function () {
        var targetElemSelector = ".dt-scroll-body";
        var targetElem = $(targetElemSelector);
        var dtStickyScrollbar = $(".dt-sticky-scrollbar");
        if (!dtStickyScrollbar.length) {
            dtStickyScrollbar = $("<div>").addClass("dt-sticky-scrollbar").appendTo(targetElem.parent());
            //<div class="dt-sticky-scrollbar"></div>
        }
        var dtStickyScrollbarContent = null;
        if (!dtStickyScrollbar.data("isInit")) {
            dtStickyScrollbarContent = $("<div>").appendTo(dtStickyScrollbar);
            dtStickyScrollbar.scroll(function () {
                if ($(this).data("ignoreScroll")) {
                    $(this).data("ignoreScroll", false);
                    console.log("dtStickyScrollbar.scroll ignoreScroll");
                    return;
                }
                var targetElem = $(targetElemSelector);
                if (targetElem.scrollLeft() !== dtStickyScrollbar.scrollLeft()) {
                    targetElem.scrollLeft(dtStickyScrollbar.scrollLeft());
                }
            });
            $(window).scroll(function (e) {
                var targetElem = $(targetElemSelector);
                if (window.innerHeight + window.pageYOffset < targetElem.offset().top + targetElem.height()) {
                    dtStickyScrollbar.addClass("sticky");
                    if (targetElem.scrollLeft() !== dtStickyScrollbar.scrollLeft()) {
                        dtStickyScrollbar.data("ignoreScroll", true).scrollLeft(targetElem.scrollLeft());
                    }
                }
                else {
                    dtStickyScrollbar.removeClass("sticky");
                }
                var scrollTop = $(this).scrollTop();
                if (scrollTop != $(this).data("last-scroll-top")) {
                    $(this).data("last-scroll-top", scrollTop);
                    targetElem.scroll();
                }
            }).resize(function () {
                if (dtStickyScrollbar.hasClass("sticky")) {
                    dtStickyScrollbar.width(targetElem.width());
                }
            });
            dtStickyScrollbar.data("isInit", true);
            dtStickyScrollbar.width(targetElem.width());
        }
        else {
            dtStickyScrollbarContent = dtStickyScrollbar.children();
        }
        dtStickyScrollbarContent.css("width", targetElem.children().css("width"));
    };
    ;
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
                        if (data.ok === true) {
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
    KKsonCRUD.prototype.resetColOrder = function () {
        this.table.colReorder.reset();
        ToastUtils.showSuccess("重設欄位順序成功");
    };
    KKsonCRUD.prototype.colSearchInit = function (api) {
        api.columns().every(function (index) {
            var column = this;
            var title = column.header(0).textContent;
            if (index === 0) {
                //add action button
                // Only insert for the first column header row
                var $headerActionContainer = $(column.header(1));
                if ($headerActionContainer.find('.kkson-crud-col-clearall').length === 0) {
                    var $clearBtn = $('<button type="button">')
                        .addClass('btn btn-sm btn-outline-danger kkson-crud-col-clearall')
                        .html('<i class="fa fa-times"></i> 清除')
                        .on('click', function (e) {
                        e.preventDefault();
                        // For each column, clear the input and clear fixed search
                        api.columns().every(function () {
                            var c = this;
                            var input = $(c.header(1)).find("input");
                            input.val("");
                            c.search.fixed("kkson-crud-col-search", null);
                        });
                        // Clear hash from url
                        window.location.hash = "";
                    });
                    $headerActionContainer.append($clearBtn);
                }
            }
            var searchable = $(column.header(0)).data("searchable");
            if (searchable === false) {
                return;
            }
            // Create input element
            var input = $('<input>').addClass('form-control form-control-sm').attr('placeholder', title).css('width', '100%');
            column.header(1).replaceChildren(input[0]);
            // Event listener for user input
            var searchTimeout;
            input.on('keyup', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    var fixed = column.search.fixed("kkson-crud-col-search");
                    var oldTerm = (fixed === null || fixed === void 0 ? void 0 : fixed.term) ? JSON.parse(fixed === null || fixed === void 0 ? void 0 : fixed.term).term : undefined;
                    column.search.fixed("kkson-crud-col-search", JSON.stringify({
                        term: input.val(),
                        logic: "contains"
                    }));
                    if (oldTerm !== input.val()) {
                        // Update the page URL hash to save the current column search parameter.
                        // We'll use the column index and value as a key-value in the hash.
                        try {
                            // Collect all current col search values
                            var colSearches_1 = {};
                            api.columns().every(function (i) {
                                var _input = $(this.header(1)).find("input");
                                if (_input.length > 0) {
                                    var searchValue = _input.val();
                                    if (searchValue) {
                                        colSearches_1[i] = searchValue;
                                    }
                                }
                            });
                            var newHashParts = [];
                            for (var key in colSearches_1) {
                                newHashParts.push(key + "=" + encodeURIComponent(colSearches_1[key]).replace(/%20/g, "+"));
                            }
                            window.location.hash = newHashParts.join("&");
                        }
                        catch (e) { /* ignore */ }
                    }
                }, 250);
            });
        });
        window.dispatchEvent(new PopStateEvent("popstate"));
        // if(window.location.hash.replace(/^#/, "") != "") {
        //     api.ajax.reload(() => {
        //     }, true);
        // }
    };
    return KKsonCRUD;
}());
