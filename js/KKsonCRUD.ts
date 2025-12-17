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

    private initListViewCustomData: {} = {};

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

    public setInitListViewCustomData(data: {}) {
        this.initListViewCustomData = data;
    }

    /**
     *
     * @param ajaxOptions
     * @param ajaxUrl
     * @param enableSearch
     * @param enableSorting
     * @param enableColSearch
     * @param customData
     */
    public initListView(config: {
        ajaxOptions?: {},
        ajaxUrl: string,
        enableSearch: boolean = true,
        enableSorting: boolean = true,
        enableColSearch: boolean = false,
        customData?: {}
    }) {
        let self = this;

        let data: {} = {
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
                "headerOffset": $(".kkson-crud-table-header").outerHeight() + $(".main-header").outerHeight() -5
            },
            "fixedColumns": {
                "left": 2
            },
            "initComplete": function(settings) {
                let api = settings.api;
                $('.dt-paging').first().appendTo('.ext-dt-paging');
                $(".buttons-colvis").first().appendTo('.crud-dt-colFilter-container');
                if(config.enableColSearch) {
                    self.colSearchInit(api);
                }
            },
            "layout": {
                "topStart": {
                    "pageLength" : true,
                    "buttons": [
                        {
                            "extend": "colvis", 
                            "columns": ":not(.noVis)",
                            "popoverTitle": "欄位顯示設定",
                            "prefixButtons": [{
                                text: '全部',
                                action: function ( e, dt, node, config ) {
                                    let colCount = crud.table.columns().count();
                                    crud.table.columns(Array.from({length: colCount}, (_, i) => i)).visible(this.active());
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
            if(typeof config.customData === "function") {
                data = this.mergeObject(data, config.customData(data));
            } else {
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

            // Refresh Button
            $(".btnRefreshDatatable").click(function() {
                self.table.ajax.reload(() => {
                    ToastUtils.showSuccess("重新整理成功");
                });
            });
        });
    }

    public crudDropdownEventInit() {
        $(crud.table.containers()[0]).find(".kkson-crud-dropdown").on('show.bs.dropdown', function() {
            if(!$(this).data("crud-dropdown-menu-relocated")) {
                let menu = $(this).find(".dropdown-menu");
                menu.appendTo("body");
            }
            $(this).data("crud-dropdown-menu-relocated", true);
        });
    }

    public dtStickyScrollbarDrawCallback() {
        let targetElemSelector = ".dt-scroll-body";
        let targetElem = $(targetElemSelector);
        let dtStickyScrollbar = $(".dt-sticky-scrollbar");
        if (!dtStickyScrollbar.length) {
            dtStickyScrollbar = $("<div>").addClass("dt-sticky-scrollbar").appendTo(targetElem.parent());
            //<div class="dt-sticky-scrollbar"></div>
        }


        let dtStickyScrollbarContent = null;
        if (!dtStickyScrollbar.data("isInit")) {
            dtStickyScrollbarContent = $("<div>").appendTo(dtStickyScrollbar);

            dtStickyScrollbar.scroll(function () {
                if ($(this).data("ignoreScroll")) {
                    $(this).data("ignoreScroll", false);
                    console.log("dtStickyScrollbar.scroll ignoreScroll");
                    return;
                }
                let targetElem = $(targetElemSelector);
                if (targetElem.scrollLeft() !== dtStickyScrollbar.scrollLeft()) {
                    targetElem.scrollLeft(dtStickyScrollbar.scrollLeft());
                }
            });

            $(window).scroll(function (e) {
                let targetElem = $(targetElemSelector);
                if (window.innerHeight + window.pageYOffset < targetElem.offset().top + targetElem.height()) {
                    dtStickyScrollbar.addClass("sticky");
                    if (targetElem.scrollLeft() !== dtStickyScrollbar.scrollLeft()) {
                        dtStickyScrollbar.data("ignoreScroll", true).scrollLeft(targetElem.scrollLeft());
                    }
                } else {
                    dtStickyScrollbar.removeClass("sticky");
                }

                let scrollTop = $(this).scrollTop();
                if(scrollTop != $(this).data("last-scroll-top")) {
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
        } else {
            dtStickyScrollbarContent = dtStickyScrollbar.children();
        }

        dtStickyScrollbarContent.css("width", targetElem.children().css("width"));
    };

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
                        if (data.ok === true) {
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

    public resetColOrder() {
        this.table.colReorder.reset();
        ToastUtils.showSuccess("重設欄位順序成功");
    }

    public colSearchInit(api) {
        api.columns().every(function () {
            let column = this;
            let title = column.header(0).textContent;

            let searchable = $(column.header(0)).data("searchable");
            if(searchable === false) {
                return;
            }

            // Create input element
            let input = $('<input>').addClass('form-control form-control-sm').attr('placeholder', title).css('width', '100%');
            column.header(1).replaceChildren(input[0]);

            // Event listener for user input
            let searchTimeout;
            input.on('keyup', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    let fixed = column.search.fixed("kkson-crud-col-search");
                    let oldTerm = fixed?.term ? JSON.parse(fixed?.term).term : undefined;
                    column.search.fixed("kkson-crud-col-search", JSON.stringify({
                        term: input.val(),
                        logic: "contains"
                    }));
                    if (oldTerm !== input.val()) {
                        // Update the page URL hash to save the current column search parameter.
                        // We'll use the column index and value as a key-value in the hash.
                        try {
                            // Collect all current col search values
                            let colSearches = {};
                            api.columns().every(function(i) {
                                let _input = $(this.header(1)).find("input");
                                if(_input.length > 0) {
                                    let searchValue = _input.val();
                                    if (searchValue) {
                                        colSearches[i] = searchValue;
                                    }
                                }
                            });
                            
                            let newHashParts = [];
                            for(let key in colSearches) {
                                newHashParts.push(key + "=" + encodeURIComponent(colSearches[key]).replace(/%20/g, "+"));
                            }
                            window.location.hash = newHashParts.join("&");
                        } catch(e) { /* ignore */ }
                    }
                }, 250);
            });
        });

        window.dispatchEvent(new PopStateEvent("popstate"));
        // if(window.location.hash.replace(/^#/, "") != "") {
        //     api.ajax.reload(() => {
        //     }, true);
        // }
    }
}

