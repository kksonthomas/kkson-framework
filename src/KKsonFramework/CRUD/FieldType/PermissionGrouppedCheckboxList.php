<?php

namespace KKsonFramework\CRUD\FieldType;

use KKsonFramework\CRUD\FieldType\CheckboxList;
use KKsonFramework\RedBeanPHP\Model\Permission;
use KKsonFramework\RedBeanPHP\Model\PermissionGroup; 

class PermissionGrouppedCheckboxList extends CheckboxList
{
    protected $options = [];
    protected $renderOptions = [];
    /**
     * @param callable $nameClosure function ($bean) {}
     * @param string $valueField The field name that used to be value. The default field is "id".
     */
    public function __construct() {
        $permissionGroups = PermissionGroup::find("1=1 ORDER BY display_weight");
        $permissions = Permission::find("1=1 ORDER BY display_weight");

        foreach ($permissionGroups as $permissionGroup) {
            $this->renderOptions[$permissionGroup->display_name] = [];
            foreach ($permissions as $permission) {
                if($permission->group == $permissionGroup->name) {
                    $this->options[$permission->id] = $permission->display_name;
                    //explode display name
                    $displayNames = explode("-", $permission->display_name);
                    $lastPart = "";
                    if(count($displayNames) > 1) {
                        $lastPart = trim($displayNames[count($displayNames) - 1]);
                        $premissionMainName =  trim(implode("-", array_slice($displayNames, 0, count($displayNames) - 1)));
                    } else {
                        $premissionMainName = $permission->display_name;
                    }
                    if(!isset($this->renderOptions[$permissionGroup->display_name][$premissionMainName])) {
                        $this->renderOptions[$permissionGroup->display_name][$premissionMainName] = [];
                    }
                    if(!isset($this->renderOptions[$permissionGroup->display_name][$premissionMainName][$lastPart])) {
                        $this->renderOptions[$permissionGroup->display_name][$premissionMainName][$lastPart] = $permission->id;
                    } else {
                        throw new \Exception("Permission display name is duplicated: " . $permission->display_name);
                    }
                }
            }
        }
        parent::__construct(Permission::_getTableName(), $this->options);
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
        $bean = $this->field->getBean();
        $valueList = $this->getValue();

        $readOnly = $this->getDisabledString();
        $required = $this->getRequiredString();

        $html = <<<TAG
<label for="field-$name" >$display</label>
TAG;

        // Global "Select All" checkbox
        $html .= <<< HTML
        <div style="margin-bottom: 15px;">
            <label class="select-all-label" style="font-weight: bold;">
                <input type="checkbox" id="select-all-global" class="select-all-global" $readOnly />
                全選所有權限
            </label>
        </div>
HTML;

        $html .= <<<TAG
       <div class="form-group checkboxes-group">
TAG;

        $groupIndex = 0;
        $rowIndex = 0;
        foreach ($this->renderOptions as $permissionGroup => $permissionMainNames) {
            $groupClass = 'permission-group-' . $groupIndex;
            $groupSelectAllId = 'select-all-' . $groupIndex;
            
            // Group header with "Select All" checkbox
            $html .= <<< HTML
                <div class="permission-group" data-group="$groupIndex">
                    <div class="group-header">
                        <strong>$permissionGroup</strong>
                        <label class="select-all-label" style="margin-left: 15px;">
                            <input type="checkbox" id="$groupSelectAllId" class="select-all-group" data-group="$groupIndex" $readOnly />
                            全選
                        </label>
                    </div>
HTML;

            // Render each permissionMainName in a single row
            foreach ($permissionMainNames as $premissionMainName => $lastParts) {
                $rowSelectAllId = 'select-all-row-' . $rowIndex;
                $html .= <<< HTML
                    <div class="permission-main-row" data-row="$rowIndex" style="display: flex; align-items: center; margin: 5px 0;">
                        <label class="select-all-row-label mb-0" style="margin-right: 10px;">
                            <input type="checkbox" id="$rowSelectAllId" class="select-all-row" data-row="$rowIndex" $readOnly />
                        </label>
                        <div class="permission-main-name" style="min-width: 200px; font-weight: bold;">$premissionMainName:</div>
                        <div class="permission-options" style="display: flex; flex-wrap: wrap; gap: 10px;">
HTML;

                // Render all lastPart checkboxes for this premissionMainName
                foreach ($lastParts as $lastPart => $permissionId) {
                    $isChecked = isset($valueList[$permissionId]) ? "checked" : "";
                    $nameAttr = 'name="' . $name . '[]"';
                    $checkboxId = 'perm-' . $permissionId;
                    $checkboxClass = 'group-' . $groupIndex . '-checkbox row-' . $rowIndex . '-checkbox';
                    
                    $html .= <<< HTML
                            <label class="checkbox-inline" style="margin: 0;">
                                <input type="checkbox" id="$checkboxId" value="$permissionId" $nameAttr class="$checkboxClass" $required $readOnly $isChecked />
                                $lastPart
                            </label>
HTML;
                }

                $html .= <<< HTML
                        </div>
                    </div>
HTML;
                $rowIndex++;
            }

            $html .= <<< HTML
                </div>
                <hr style="margin: 15px 0;" />
HTML;

            $groupIndex++;
        }

        $html .= " </div><br />";

        // Add JavaScript for "Select All" functionality
        $html .= <<< HTML
<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        // Handle global "Select All" checkbox
        var globalSelectAll = document.getElementById('select-all-global');
        if (globalSelectAll) {
            globalSelectAll.addEventListener('change', function() {
                // Select all individual permission checkboxes (those with both group and row classes)
                var allCheckboxes = document.querySelectorAll('input[type="checkbox"][class*="group-"][class*="row-"]');
                var allGroupSelectAll = document.querySelectorAll('.select-all-group');
                var allRowSelectAll = document.querySelectorAll('.select-all-row');
                
                // Update all individual checkboxes
                allCheckboxes.forEach(function(checkbox) {
                    if (!checkbox.disabled) {
                        checkbox.checked = globalSelectAll.checked;
                    }
                });
                
                // Update all group-level "Select All" checkboxes
                allGroupSelectAll.forEach(function(selectAll) {
                    if (!selectAll.disabled) {
                        selectAll.checked = globalSelectAll.checked;
                    }
                });
                
                // Update all row-level "Select All" checkboxes
                allRowSelectAll.forEach(function(selectAllRow) {
                    if (!selectAllRow.disabled) {
                        selectAllRow.checked = globalSelectAll.checked;
                    }
                });
            });
        }

        // Handle group-level "Select All" checkboxes
        var selectAllCheckboxes = document.querySelectorAll('.select-all-group');
        selectAllCheckboxes.forEach(function(selectAll) {
            selectAll.addEventListener('change', function() {
                var groupIndex = this.getAttribute('data-group');
                var groupCheckboxes = document.querySelectorAll('.group-' + groupIndex + '-checkbox');
                groupCheckboxes.forEach(function(checkbox) {
                    if (!checkbox.disabled) {
                        checkbox.checked = selectAll.checked;
                    }
                });
                // Update all row-level "Select All" checkboxes in this group
                var groupRows = document.querySelectorAll('.permission-group[data-group="' + groupIndex + '"] .permission-main-row');
                groupRows.forEach(function(row) {
                    var rowIndex = row.getAttribute('data-row');
                    var rowSelectAll = document.getElementById('select-all-row-' + rowIndex);
                    if (rowSelectAll) {
                        var rowCheckboxes = document.querySelectorAll('.row-' + rowIndex + '-checkbox');
                        var allRowChecked = true;
                        rowCheckboxes.forEach(function(cb) {
                            if (!cb.disabled && !cb.checked) {
                                allRowChecked = false;
                            }
                        });
                        rowSelectAll.checked = allRowChecked;
                    }
                });
                
                // Update global "Select All" checkbox
                if (globalSelectAll) {
                    var allIndividualCheckboxes = document.querySelectorAll('input[type="checkbox"][class*="group-"][class*="row-"]');
                    var allGlobalChecked = true;
                    allIndividualCheckboxes.forEach(function(cb) {
                        if (!cb.disabled && !cb.checked) {
                            allGlobalChecked = false;
                        }
                    });
                    globalSelectAll.checked = allGlobalChecked;
                }
            });
        });

        // Handle row-level "Select All" checkboxes
        var selectAllRowCheckboxes = document.querySelectorAll('.select-all-row');
        selectAllRowCheckboxes.forEach(function(selectAllRow) {
            selectAllRow.addEventListener('change', function() {
                var rowIndex = this.getAttribute('data-row');
                var rowCheckboxes = document.querySelectorAll('.row-' + rowIndex + '-checkbox');
                rowCheckboxes.forEach(function(checkbox) {
                    if (!checkbox.disabled) {
                        checkbox.checked = selectAllRow.checked;
                    }
                });
                // Update group-level "Select All" checkbox
                var rowElement = document.querySelector('.permission-main-row[data-row="' + rowIndex + '"]');
                if (rowElement) {
                    var groupElement = rowElement.closest('.permission-group');
                    if (groupElement) {
                        var groupIndex = groupElement.getAttribute('data-group');
                        var groupCheckboxes = document.querySelectorAll('.group-' + groupIndex + '-checkbox');
                        var groupSelectAll = document.getElementById('select-all-' + groupIndex);
                        if (groupSelectAll) {
                            var allGroupChecked = true;
                            groupCheckboxes.forEach(function(cb) {
                                if (!cb.disabled && !cb.checked) {
                                    allGroupChecked = false;
                                }
                            });
                            groupSelectAll.checked = allGroupChecked;
                        }
                    }
                }
                
                // Update global "Select All" checkbox
                if (globalSelectAll) {
                    var allIndividualCheckboxes = document.querySelectorAll('input[type="checkbox"][class*="group-"][class*="row-"]');
                    var allGlobalChecked = true;
                    allIndividualCheckboxes.forEach(function(cb) {
                        if (!cb.disabled && !cb.checked) {
                            allGlobalChecked = false;
                        }
                    });
                    globalSelectAll.checked = allGlobalChecked;
                }
            });
        });

        // Update "Select All" state when individual checkboxes change
        var allGroupCheckboxes = document.querySelectorAll('[class*="group-"][class*="-checkbox"]');
        allGroupCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                var classList = this.className.split(' ');
                var groupClass = classList.find(function(cls) { return cls.startsWith('group-') && cls.endsWith('-checkbox'); });
                var rowClass = classList.find(function(cls) { return cls.startsWith('row-') && cls.endsWith('-checkbox'); });
                
                // Update row-level "Select All"
                if (rowClass) {
                    var rowIndex = rowClass.replace('row-', '').replace('-checkbox', '');
                    var rowCheckboxes = document.querySelectorAll('.row-' + rowIndex + '-checkbox');
                    var rowSelectAll = document.getElementById('select-all-row-' + rowIndex);
                    if (rowSelectAll) {
                        var allRowChecked = true;
                        rowCheckboxes.forEach(function(cb) {
                            if (!cb.disabled && !cb.checked) {
                                allRowChecked = false;
                            }
                        });
                        rowSelectAll.checked = allRowChecked;
                    }
                }
                
                // Update group-level "Select All"
                if (groupClass) {
                    var groupIndex = groupClass.replace('group-', '').replace('-checkbox', '');
                    var groupCheckboxes = document.querySelectorAll('.group-' + groupIndex + '-checkbox');
                    var selectAll = document.getElementById('select-all-' + groupIndex);
                    if (selectAll) {
                        var allChecked = true;
                        groupCheckboxes.forEach(function(cb) {
                            if (!cb.disabled && !cb.checked) {
                                allChecked = false;
                            }
                        });
                        selectAll.checked = allChecked;
                    }
                }
                
                // Update global "Select All" checkbox
                if (globalSelectAll) {
                    var allIndividualCheckboxes = document.querySelectorAll('input[type="checkbox"][class*="group-"][class*="row-"]');
                    var allGlobalChecked = true;
                    allIndividualCheckboxes.forEach(function(cb) {
                        if (!cb.disabled && !cb.checked) {
                            allGlobalChecked = false;
                        }
                    });
                    globalSelectAll.checked = allGlobalChecked;
                }
            });
        });

        // Initialize "Select All" states on page load
        // Initialize row-level "Select All"
        selectAllRowCheckboxes.forEach(function(selectAllRow) {
            var rowIndex = selectAllRow.getAttribute('data-row');
            var rowCheckboxes = document.querySelectorAll('.row-' + rowIndex + '-checkbox');
            var allRowChecked = true;
            rowCheckboxes.forEach(function(checkbox) {
                if (!checkbox.disabled && !checkbox.checked) {
                    allRowChecked = false;
                }
            });
            selectAllRow.checked = allRowChecked;
        });
        
        // Initialize group-level "Select All"
        selectAllCheckboxes.forEach(function(selectAll) {
            var groupIndex = selectAll.getAttribute('data-group');
            var groupCheckboxes = document.querySelectorAll('.group-' + groupIndex + '-checkbox');
            var allChecked = true;
            groupCheckboxes.forEach(function(checkbox) {
                if (!checkbox.disabled && !checkbox.checked) {
                    allChecked = false;
                }
            });
            selectAll.checked = allChecked;
        });
        
        // Initialize global "Select All" checkbox
        if (globalSelectAll) {
            var allIndividualCheckboxes = document.querySelectorAll('input[type="checkbox"][class*="group-"][class*="row-"]');
            var allGlobalChecked = true;
            allIndividualCheckboxes.forEach(function(checkbox) {
                if (!checkbox.disabled && !checkbox.checked) {
                    allGlobalChecked = false;
                }
            });
            globalSelectAll.checked = allGlobalChecked;
        }
    });
})();
</script>
HTML;

        if ($echo)
            echo $html;

        return $html;
    }
}