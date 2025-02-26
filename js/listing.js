function SearchCriteria(scGroup, config, data = null) {
    let self = this;
    this.scGroup = scGroup;
    let tmpl = $(".searchCriteria.tmpl");
    this.elem = tmpl.clone().removeClass("tmpl d-none");
    this.selFieldNameElem = this.elem.find(".selFieldName");
    this.selCond = this.elem.find(".selCond");
    this.keywordContainerElem = this.elem.find('.keywordContainer');
    this.btnDelSc = this.elem.find(".btnDelSc");
    this.btnIndentSc = this.elem.find(".btnIndentSc");
    this.btnUnIndentSc = this.elem.find(".btnUnIndentSc");

    this.elem.data("instance", this);

    this.btnDelSc.click(function() {
        self.elem.remove();
        self.scGroup.onScDeleted();
    });

    this.btnIndentSc.click(function() {
        if (self.scGroup.getSearchCriteriaAndGroupCount() > 1) {
            let sc = new SearchCriteriaGroup(self.scGroup, config, ["and", [self.toDataObject()]]);
            sc.elem.insertAfter(self.elem);
            self.elem.remove();
        } else {
            throw "Cant Indent when group have only 1 group / searchCriteria";
        }
    });

    this.btnUnIndentSc.click(function() {
        if (self.scGroup.parent instanceof SearchCriteriaGroup) {
            let data = self.toDataObject();
            let sc = new SearchCriteria(self.scGroup.parent, config, data);
            sc.elem.insertBefore(self.scGroup.elem);
            self.elem.remove();
            if (self.scGroup.getSearchCriteriaAndGroupCount() === 0) {
                self.scGroup.elem.remove();
            } else {
                self.scGroup.refreshUI(true);
            }
        } else {
            throw "Cant UnIndent root level group";
        }
    });

    this.refreshUI = function() {
        let fieldName = this.selFieldNameElem.val();
        let condValue = this.selCond.val();
        let keyword = this.keywordContainerElem.find(".inputKeyword").val();
        if (fieldName) {
            let fieldConfig = config["searchableFields"][fieldName];
            let condConfig = config["conditionConfig"];

            this.selCond.prop("disabled", false).children(":not(.placeholder)").remove();
            fieldConfig["conditions"].forEach(cond => {
                let localCondConfig = condConfig[cond];
                this.selCond.append($("<option>").val(cond).text(localCondConfig[0]));
            });

            this.keywordContainerElem.html("").show();
            let render = fieldConfig["render"];
            if (typeof render === 'string') {
                this.keywordContainerElem.html(fieldConfig["render"]);
            } else if (typeof render === 'object') {
                let elem = $(`<${render['tag']??'input'}>`).addClass("form-control inputKeyword");
                if (render["attr"]) {
                    for (const [k, v] of Object.entries(render["attr"])) {
                        if (k === "class") {
                            elem.addClass(v);
                        } else {
                            elem.attr(k, v);
                        }
                    }
                }
                if (render['tag'] === 'select' && render["options"]) {
                    if (render["placeholder"]) {
                        elem.append($("<option>").text(render["placeholder"]).prop("disabled", true).prop("selected", true).prop("hidden", true).addClass("placeholder"));
                    }
                    for (const [k, v] of Object.entries(render["options"])) {
                        elem.append($("<option>").val(k).text(v));
                    }
                }
                this.keywordContainerElem.append(elem);
                if (render["js"]) {
                    let callback;
                    eval("callback = " + render["js"]);
                    callback(elem, keyword);
                }

                if (!render["manualInitValue"] && typeof keyword !== "undefined" && keyword !== "") {
                    this.keywordContainerElem.find(".inputKeyword").val(keyword);
                }
            }

            if (this.selCond.children(`[value="${condValue}"]`).length > 0) {
                this.selCond.val(condValue).change();
            } else {
                this.selCond.val("").change();
            }
        } else {
            this.selCond.prop("disabled", true);
            this.keywordContainerElem.hide();
        }

        if (this.scGroup.parent instanceof SearchCriteriaGroup) {
            this.btnUnIndentSc.show();
        } else {
            this.btnUnIndentSc.hide();
        }

        if (this.scGroup.getSearchCriteriaAndGroupCount() > 1) {
            if (config["maxIndent"] > 0 && this.scGroup.groupScElem.parents(".scGroup").length >= config["maxIndent"]) {
                this.btnIndentSc.hide();
            } else {
                this.btnIndentSc.show();
            }
        } else {
            this.btnIndentSc.hide();
        }

    };

    this.toDataObject = function() {
        let fieldName = this.selFieldNameElem.val();
        let condValue = this.selCond.val();
        let keywordElem = this.keywordContainerElem.find(".inputKeyword");
        let keyword = keywordElem.data("value") ?? keywordElem.val();
        if (fieldName) {
            return [fieldName, condValue, keyword];
        } else {
            return null;
        }
    };

    this.selFieldNameElem.change(this.refreshUI.bind(this));
    this.selCond.change(function() {
        let cond = $(this).val();
        let cfg = config["conditionConfig"][cond];
        let isShowsInput = cfg[1];
        if (isShowsInput) {
            self.keywordContainerElem.show();
        } else {
            self.keywordContainerElem.find(".inputKeyword").val("");
            self.keywordContainerElem.hide();
        }
    });
    if (data) {
        if (config["searchableFields"][data[0]]) {
            this.selFieldNameElem.val(data[0]);
            this.refreshUI();
            this.selCond.val(data[1]);
            this.keywordContainerElem.find(".inputKeyword").val(data[2]);
        }
    }
    this.refreshUI();
}

function SearchCriteriaGroup(parent, config, data = null) {
    let self = this;
    let tmpl = $(".scGroup.tmpl");
    this.parent = parent;
    this.elem = tmpl.clone().removeClass("tmpl");
    this.groupScElem = self.elem.find(".groupSc");
    this.btnAddElem = this.elem.find(".btnAddSc");
    this.groupBtnCol = this.elem.find(".groupBtnCol");
    this.groupCondBtn = this.groupBtnCol.find(".btnScGroupCondition");
    this.btnDelScGroup = this.elem.find(".btnDelScGroup");

    this.elem.data("instance", this);

    this.btnAddElem.click(function() {
        let sc = new SearchCriteria(self, config);
        self.groupScElem.append(sc.elem);
        self.refreshUI(true);
    });
    this.toggleGroupCondBtnStatus = () => {
        this.setGroupCondBtnStatus(this.groupCondBtn.data("value") === "and" ? "or" : "and");
    };
    this.setGroupCondBtnStatus = v => {
        if (v === "or") {
            this.groupCondBtn.data("value", "or").html("或");
        } else if (v === "and") {
            this.groupCondBtn.data("value", "and").html("且");
        } else {
            this.groupCondBtn.data("value", "").html("?");
        }
    };

    this.groupCondBtn.click(this.toggleGroupCondBtnStatus.bind(this)).click();

    this.btnDelScGroup.click(function() {
        self.elem.remove();
        self.parent.onDelScGroup();
    });

    this.onDelScGroup = function() {
        this.refreshUI(true);
        this.onScDeleted();
    };

    this.getSearchCriteriaCount = function() {
        return this.groupScElem.children(".searchCriteria").length
    };

    this.getSearchCriteriaAndGroupCount = function() {
        return this.groupScElem.children(".searchCriteria,.scGroup").length;
    };

    this.refreshUI = function(refreshSc = false) {
        if (this.getSearchCriteriaCount() > 0) {
            this.groupBtnCol.show();
        } else {
            this.groupBtnCol.hide();
        }

        if (refreshSc) {
            this.groupScElem.find(".searchCriteria,.scGroup").each((idx, elem) => {
                $(elem).data("instance").refreshUI();
            });
        }
    };

    this.onScDeleted = function() {
        if (this.getSearchCriteriaCount() === 0) {
            this.btnDelScGroup.click();
        }
        this.refreshUI(true);
    };

    this.toDataObject = function() {
        let cond = this.groupCondBtn.data("value");
        let scs = this.groupScElem.children(".searchCriteria,.scGroup");
        let scDataList = [];
        scs.each((index, scElem) => {
            let sc = $(scElem).data("instance");
            let scData = sc.toDataObject();
            if (scData !== null) {
                scDataList.push(scData);
            }
        });
        if (scDataList.length > 0) {
            return [cond, scDataList];
        } else {
            return null;
        }
    };

    if (data) {
        self.setGroupCondBtnStatus(data[0]);
        data[1].forEach(d => {
            if (d == null || d.length === 3) {
                let sc = new SearchCriteria(self, config, d);
                self.groupScElem.append(sc.elem);
            } else if (d.length === 2) {
                let scGroup = new SearchCriteriaGroup(self, config, d);
                self.groupScElem.append(scGroup.elem);
            } else {
                throw "Unknown data length";
            }
        });

        self.refreshUI();
    }
}

function KKsonCRUDSearchingPane(formElem, config) {
    let self = this;
    this.sc = null;
    this.formElem = formElem;
    this.elem = formElem.find(".formScBody");

    function findGetParameter(parameterName) {
        var result = null,
            tmp = [];
        location.search
            .substr(1)
            .split("&")
            .forEach(function(item) {
                tmp = item.split("=");
                if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
            });
        return result;
    }

    this.getScGroupCount = function() {
        return this.elem.children(".scGroup").length;
    };


    this.onDelScGroup = function() {
        this.refreshUI();
    };

    this.refreshUI = function() {
        if (this.getScGroupCount() === 0) {
            this.sc = new SearchCriteriaGroup(this, config);
            this.elem.append(this.sc.elem);
        }
        this.elem.find(".searchCriteria").each((idx, elem) => {
            $(elem).data("instance").refreshUI();
        });
    };


    this.toDataObject = function() {
        let dataObject = this.sc ? this.sc.toDataObject() : null;
        if (dataObject && dataObject[1].length === 0) {
            return null;
        }
        return dataObject;
    };

    this.loadFromGetParam = () => {
        //load from param
        let q = findGetParameter("q");
        try {
            if (this.sc) {
                this.sc.elem.remove();
            }
            if (q) {
                let decodedQ = decodeURIComponent(q);
                let json = atob(decodedQ);
                json = decodeURIComponent(json);
                let data = JSON.parse(json);
                if (data) {
                    this.sc = new SearchCriteriaGroup(this, config, data);
                    this.elem.append(this.sc.elem);
                }
            }
        } catch (e) {
            console.error(e);
        }
        this.refreshUI();
    };

    this.loadFromGetParam();

    this.toQueryParamString = function(encode = true) {
        let dataObject = self.toDataObject();
        if (!dataObject) {
            return "";
        }
        let jsonString = JSON.stringify(dataObject);
        let encodedJson = encodeURIComponent(jsonString);
        let q = btoa(encodedJson);
        return encode ? encodeURIComponent(q) : q;
    };

    this.formElem.submit(function(ev) {
        ev.preventDefault();
        let q = self.toQueryParamString();
        let newPageUrl = new URL(location);
        let btnExport = document.getElementById("btn-kksoncrud-export");
        newPageUrl.searchParams.set("q", q !== "null" ? q : "");
        if(btnExport) {
            let newExportUrl = new URL(btnExport.href);
            newExportUrl.searchParams.set("q", q !== "null" ? q : "");
            btnExport.href = newExportUrl.toString();
        }
        
        history.pushState({}, "", newPageUrl);
        let newAjaxUrl = new URL(crud.getDataTable().ajax.url(), location);
        crud.getDataTable().ajax.url(newAjaxUrl.pathname + newPageUrl.search).load(() => {
            ToastUtils.showSuccess("搜尋完成");
        });
    });

    window.addEventListener("popstate", (event) => {
        let newPageUrl = new URL(location);
        let newAjaxUrl = new URL(crud.getDataTable().ajax.url(), location);
        crud.getDataTable().ajax.url(newAjaxUrl.pathname + newPageUrl.search).load();

        this.loadFromGetParam();
    });

    this.formElem.find(".btnResetSearch").click(ev => {
        ev.preventDefault();
        let newQ = this.toQueryParamString(false);
        let q = findGetParameter("q");
        if (newQ !== q) {
            AlertUtils.showConfirm("重設搜尋?").then(v => {
                if (v.isConfirmed) {
                    this.elem.empty();
                    this.loadFromGetParam();
                }
            });
        } else {
            this.elem.empty();
            this.loadFromGetParam();
            ToastUtils.showInfo("搜尋已為初始狀態");
        }

    });
}