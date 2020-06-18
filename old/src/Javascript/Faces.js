/**
 * citrus faces js
 *
 * @copyright   Copyright 2017, CitrusFramework. All Rights Reserved.
 * @author      take64 <take64@citrus.tk>
 */

(function(jQuery) {
    jQuery.fn.citrusfaces = function(_options){
        var options = jQuery.extend(true, {
            id: '',
            service: '',
            name: '',
            primaries: {},
            foreigns: {},
            list:{
                faces: {
                    id: ''
                },
                queries:{
                    method: 'facesSummaries',
                    prefix: '',
                    service: ''
                },
                columns:{},
                summaries:{},
                dialog:{
                    id: '',
                    type: 'summary',
                    options: {
                        resizable: false,
                        autoOpen: false,
                        width: 600,
                        modal: true,
                        title: '',
                        position: { my:'center', at:'center' },
                        buttons:{
                            'Close' : function(){
                                $(this).dialog('close');
                            }
                        }
                    }
                },
                method:{
                    initialize: function(){},
                    release: function(){}
                },
                button: {
                    disabled: true,
                    canChangeButtonState: true
                },
                'class': '',
            },
            edit:{
                faces: {
                    id: ''
                },
                queries:{
                    method: 'edit',
                    prefix: '',
                    service: '',
                    rowid: null,
                    rev: null
                },
                columns:{},
                dialog:{
                    id: '',
                    options: {
                        resizable: false,
                        autoOpen: false,
                        width: 600,
                        modal: true,
                        title: '',
                        position: { my:'center', at:'center' },
                        buttons:{
                            'Save' : function(){
                                if(dataModify(options) === true) {
                                    $(this).dialog('close');
                                    dataLoadList(options);
                                }
                            },
                            'Close' : function(){
                                $(this).dialog('close');
                            }
                        }
                    }
                },
                method:{
                    initialize: function(){},
                    release: function(){}
                }
            },
            view:{
                faces: {
                    id: ''
                },
                queries:{
                    method: 'facesDetail',
                    prefix: '',
                    service: '',
                    rowid: null,
                    rev: null
                },
                columns:{},
                dialog:{
                    id: '',
                    options: {
                        resizable: false,
                        autoOpen: false,
                        width: 600,
                        modal: true,
                        title: '',
                        position: { my:'center', at:'center' },
                        buttons:{
                            'Remove' : function() {
                                if(dataRemove(options) === true) {
                                    $(this).dialog('close');
                                    dataLoadList(options);
                                }
                            },
                            'Close' : function(){
                                $(this).dialog('close');
                            }
                        }
                    }
                },
                method:{
                    initialize: function(){},
                    release: function(){}
                }
            },
            toggle:{
                faces: {
                    id: ''
                },
                queries:{
                    method: 'toggle',
                    prefix: '',
                    service: '',
                    rowid: null,
                    rev: null
                },
                columns:{},
                method:{
                    initialize: function(){},
                    release: function(){}
                }
            },
            client:{},
            parent: null
        }, _options);
        
        
        // options modify
        var id = options.id;
        var name = options.name;
        var service = options.service;
        if(!service) {
            service = id;
        }
        options.list.faces.id = id;
        options.edit.faces.id = id;
        options.view.faces.id = id;
        options.toggle.faces.id = id;
        options.list.queries.service = service;
        options.edit.queries.service = service;
        options.view.queries.service = service;
        options.toggle.queries.service = service;
        options.list.queries.prefix = 'call_'+ id + '_';
        options.edit.queries.prefix = 'edit_'+ id + '_';
        options.view.queries.prefix = 'view_'+ id + '_';
        options.toggle.queries.prefix = 'toggle_'+ id + '_';
        options.list.dialog.id = 'dialog-'+ id +'-list';
        options.edit.dialog.id = 'dialog-'+ id +'-edit';
        options.view.dialog.id = 'dialog-'+ id +'-view';
        options.list.dialog.options.title = name + '一覧';
        options.edit.dialog.options.title = name + '登録';
        options.view.dialog.options.title = name + '照会';
        
        if(options.list.dialog.type === 'summary' && options.list.queries.method === undefined) {
            options.list.queries.method = 'facesSummaries';
        }
        else if(options.list.dialog.type === 'selection' || options.list.dialog.type === 'selection-panel') {
            options.list.queries.method = 'selections';
        }
        
        if(options.parent === null) {
            options.list.dialog.id = 'window-'+ id +'-list';
        }

        // list
        var listTag = null;
        var listButton = null;
        
        // list search make
        var selector_list_dialog = $('#' + options.list.dialog.id);
        selector_list_dialog.find('table.faces-search [id^=call_]').each(function(){
            $(this).change(function(){
                if (this.id !== 'call_' + options.id + '_page') {
                    $('#call_' + options.id + '_page').val(1);
                }
                dataLoadList(options);
            }).keydown(function(event){
                if (event.keyCode === 13) {
                    $(this).change();
                    return false;
                }
            });
        });

        // list window
        if(selector_list_dialog.length > 0) {
            listTag = $('#'+ options.list.dialog.id);
        }
        // list dialog
        else {
            listTag = $('<div />').attr('id', options.list.dialog.id);
            listButton = $('<button />').html('一覧').attr('id', 'open-'+ options.list.dialog.id).button().click(function(){ listTag.dialog('open'); return false; });
            
            // dialog option
            options.list.dialog.options.open = function(){
                dataLoadList(options);
            };
        }
        
        $(this)
            .append(listTag)
            .append(listButton);
        
        // edit
        var editTag = null;
        var selector_edit_dialog = $('#' + options.edit.dialog.id);
        if(selector_edit_dialog.length > 0){
            editTag = $('#'+ options.edit.dialog.id);
            // dialog option
            options.edit.dialog.options.open = function(){
                options.edit.method.initialize(options);
                if(options.view.queries.rowid && options.view.queries.rev) {
                    dataLoadEdit(options);
                } else {
                    dataClearEdit(options);
                }
                options.edit.method.release(options);
            };
        }

        // view
        var viewTag = null;
        if(selector_edit_dialog.length > 0){
            viewTag = $('#'+ options.view.dialog.id);
            // dialog option
            options.view.dialog.options.open = function() {
                options.view.method.initialize(options);
                dataLoadView(options);
                options.view.method.release(options);
            };
        }
        
        if(options.list.dialog.type === 'summary') {
            if(editTag !== null) {
                var editButton = $('<button />').html('登録').attr('id', 'open-'+ options.edit.dialog.id).button().click(function(){
                    // selected method
                    var tr = $('#'+ options.list.dialog.id).find('table.faces-list').find('tbody').find('tr.selected');
                    if(tr && tr.length > 0) {
                        $.each(options.primaries, function(ky, vl){
                            options.edit.queries[ky] = $(tr).attr(ky);
                            options.view.queries[ky] = $(tr).attr(ky);
                        });
                    }
                    editTag.dialog('open');
                    
                    return false;
                });
                $(this)
                    .append(editTag)
                    .append(editButton);
                    
                editTag.dialog(options.edit.dialog.options);
            }
            if(viewTag !== null) {
                var viewButton = $('<button />').html('照会').attr('id', 'open-'+ options.view.dialog.id).button().click(function(){
                    
                    // selected method
                    var tr = $('#'+ options.list.dialog.id).find('table.faces-list').find('tbody').find('tr.selected');
                    if(tr) {
                        $.each(options.primaries, function(ky, vl){
                            options.edit.queries[ky] = $(tr).attr(ky);
                            options.view.queries[ky] = $(tr).attr(ky);
                        });
                    }
                    viewTag.dialog('open');
                    
                    return false;
                });
                $(this)
                    .append(viewTag)
                    .append(viewButton);
                
                viewTag.dialog(options.view.dialog.options);
            }
        }
        
        //load list
        var listTable = $('<table />').attr('class', 'faces-list ' + options.list.class);
        if(options.list.dialog.type === 'summary' || options.list.dialog.type === 'selection') {
            if($.objectSize(options.list.columns) > 0) {
                listTable.append('<thead><tr /></thead>');
                $.each(options.list.columns, function(ky, vl){
                    var th = $('<th />')
                        .attr('id', ky)
                        .html(vl.name);
                    if(vl.sortkey) {
                        th.attr('sortkey', vl.sortkey).addClass('sortkey').addClass('default').click(function(){
                            var thisID = $(this).attr('id');
                            var sortkey = vl.sortkey;
                            if($(this).hasClass('default') === true) {
                                $(this).removeClass('default').addClass('asc');
                                sortkey = sortkey.replaceAll(',', ' ASC,') + ' ASC';
                            } else if($(this).hasClass('asc') === true) {
                                $(this).removeClass('asc').addClass('desc');
                                sortkey = sortkey.replaceAll(',', ' DESC,') + ' DESC';
                            } else if($(this).hasClass('desc') === true) {
                                $(this).removeClass('desc').addClass('default');
                                sortkey = '';
                            }
                            listTable.find('th.sortkey[id!='+ thisID +']').removeClass('asc').removeClass('desc').addClass('default');
                            $('#'+ options.list.queries.prefix +'orderby').val(sortkey).change();
                        });
                    }
                    listTable.find('tr').append(th);
                });
            }
        } else if(options.list.dialog.type === 'selection-panel') {}
        listTag.append(listTable);

        // list window
        if(options.parent === null) {
            if($('#'+ options.list.dialog.id).length > 0) {
                dataLoadList(options);
            }   
        }
        
        // parent method
        if(options.parent !== null) {
            var sub = this;
            
            $(sub).hide();
            
            // dialog option
            options.list.dialog.options.open = function(){
                dataLoadList(options);
            };
            options.list.dialog.options.close = function(){
                dataLoadList(options.parent.options);
            };
            $(sub).dialog(options.list.dialog.options);
            
            var listButton = $('<button />').html(name + '一覧').attr('id', 'open-'+ options.list.dialog.id).button(options.list.button).click(function(){
                
                // selected method
                var tr = $('#'+ options.parent.options.list.dialog.id).find('table.faces-list').find('tbody').find('tr.selected');
                if(tr && tr.length > 0) {
                    $.each(options.parent.options.primaries, function(ky, vl){
                        options.primaries[ky] = $(tr).attr(ky);
                        options.list.queries[ky] = $(tr).attr(ky);
                    });
                    $.each(options.parent.options.foreigns, function(ky, vl){
                        options.foreigns[ky] = $(tr).attr(ky);
                    });
                } else {
                    $.each(options.primaries, function(ky, vl){
                        if(options.parent.options.primaries[ky]) {
                            options.primaries[ky] = options.parent.options.primaries[ky];
                            options.list.queries[ky] = options.parent.options.primaries[ky];
                        }
                    });
                    $.each(options.foreigns, function(ky, vl){
                        if(options.parent.options.foreigns[ky]) {
                            options.foreigns[ky] = options.parent.options.foreigns[ky];
                        }
                    });
                }
                $(sub).dialog('open');
                
                return false;
            });
            
            $(options.parent).append(listButton);
        }
        
        this.options = options;

        if (typeof $.fn.datepicker !== 'undefined') {
            $('.datepicker').datepicker({
                dateFormat: 'yy-mm-dd'
            });
        }
        if (typeof $.fn.datetimepicker !== 'undefined') {
            $('.datetimepicker').datetimepicker({
                dateFormat: 'yy-mm-dd',
                timeFormat: 'HH:mm:ss'
            });
        }

        return this;
    };
    jQuery.fn.citrussuggests = function(_options){
        var sub = this;
        var options = jQuery.extend(true, {
            id: '',
            service: '',
            output: '',
            queries:{
                method: 'suggests',
                prefix: '',
                service: '',
                limit: 10
            }
        }, _options);
        
        // options modify
        var id = options.id;
        var name = options.name;
        var service = options.service;
        if(!service) {
            service = id;
        }
        options.input = sub;
        options.queries.service = service;
        options.queries.prefix = 'suggest_'+ id + '_';
        
        
        $(options.input).autocomplete({
            delay: 1000,
            source: function(request, response){
                
                var condition = {'keyword': request.term};
                $.each(options.queries, function(ky, vl){
                    if(ky !== 'service' && ky !== 'method') {
                        if(typeof vl === 'function') {
                            condition[ky] = vl();
                        } else {
                            condition[ky] = vl;
                        }
                    }
                });
                
                var url = '/xhr/' + options.queries.service + '/suggests';
                
                $.getJSON(url, condition, function(response_item){
                    if (response_item.results.result !== false) {
                        response(response_item.results.items);
                    }
                });
            },
            minLength: 0,
            select: function(event, ui){
                $(sub).val(ui.item.value);
                if(options.input === options.output) {
                    $(options.output).val(ui.item.label);
                } else {
                    $(options.output).html(ui.item.label);
                }
            }
        }).change(function(){
            if($(this).val() === "") {
                $(options.output).html("");
            }
        });
        
        this.options = options;
        
        return this;
    };
    jQuery.fn.citruspopup = function(_options){
        var options = jQuery.extend(true, {
            id: '',
            service: '',
            name: '',
            primaries: {},
            foreigns: {},
            popup:{
                faces: {
                    id: ''
                },
                queries:{
                    method: 'facesSummaries',
                    prefix: '',
                    service: ''
                },
                dialog:{
                    id: '',
                    type: 'summary',
                    options: {
                        resizable: false,
                        autoOpen: false,
                        width: 600,
                        modal: true,
                        title: '',
                        position: { my:'center', at:'center' }
                    }
                },
                method:{
                    initialize: function(){},
                    release: function(){}
                },
                button: {
                    disabled: true
                }
            },
            client:{},
            parent: null
        }, _options);

        // options modify
        var id = options.id;
        var name = options.name;
        var service = options.service;
        if(!service) {
            service = id;
        }
        options.popup.faces.id = id;
        options.popup.queries.service = service;
        options.popup.queries.prefix = 'popup_'+ id + '_';
        options.popup.dialog.id = 'dialog-'+ id +'_popup';
        options.popup.dialog.options.title = name + '処理';
        
        if(options.parent === null) {
            options.list.dialog.id = 'window-'+ id +'-list';
        }
        // list
        options.popup.dialog.options.open = function(){
            options.popup.method.initialize();
            // options.edit.method.process();
            options.popup.method.release();
        };
        
        
        // list
        var popupTag = null;
        var popupButton = null;

        // list window
        if($('#'+ options.popup.dialog.id).length > 0) {
            popupTag = $('#'+ options.popup.dialog.id);
        }
        // list dialog
        else {
            popupTag = $('<div />').attr('id', options.popup.dialog.id);
            popupButton = $('<button />').html('処理').attr('id', 'open-'+ options.popup.dialog.id).button().click(function(){ popupTag.dialog('open'); return false; });
            
            // dialog option
            options.popup.dialog.options.open = function(){};
        }
        
        $(this)
            .append(popupTag)
            .append(popupButton);
        
        
        // parent method
        if(options.parent !== null) {
            var sub = this;
            
            $(sub).hide();
            
            // dialog option
            $(sub).dialog(options.popup.dialog.options);
            
            var popupButton = $('<button />').html(name).attr('id', 'open-'+ options.popup.dialog.id).button(options.popup.button).click(function(){
                
                // selected method
                var tr = $('#'+ options.parent.options.list.dialog.id).find('table.faces-list').find('tbody').find('tr.selected');
                if(tr && tr.length > 0) {
                    $.each(options.parent.options.primaries, function(ky, vl){
                        options.primaries[ky] = $(tr).attr(ky);
                        options.popup.queries[ky] = $(tr).attr(ky);
                    });
                    $.each(options.parent.options.foreigns, function(ky, vl){
                        options.foreigns[ky] = $(tr).attr(ky);
                    });
                } else {
                    $.each(options.primaries, function(ky, vl){
                        if(options.parent.options.primaries[ky]) {
                            options.primaries[ky] = options.parent.options.primaries[ky];
                            options.popup.queries[ky] = options.parent.options.primaries[ky];
                        }
                    });
                    $.each(options.foreigns, function(ky, vl){
                        if(options.parent.options.foreigns[ky]) {
                            options.foreigns[ky] = options.parent.options.foreigns[ky];
                        }
                    });
                }
                $(sub).dialog('open');
                
                return false;
            });
            
            $(options.parent).append(popupButton);
        }
        this.options = options;
        
        return this;
    };
    jQuery.fn.citrushtmledit = function(_options){
        var options = jQuery.extend(true, {
            id: ''
        }, _options);
        
        var preview_css = {
            'width': $(this).width(),
            'height': $(this).height()
        };
        var preview = $('<iframe />').attr('id','citrushtmledit_iframe').css(preview_css).append('<html />').append('<body />');
        
        var tabs_ul = $('<ul />');
        tabs_ul.append($('<li />').html($('<a />').attr('href', '#citrushtmledit_textarea').html('HTML')));
        tabs_ul.append($('<li />').html($('<a />').attr('href', '#citrushtmledit_preview').html('PREVIEW')));
        var tabs = $('<div />').attr('id','citrushtmledit_tabs');
        var tabs_div_textarea = $('<div />').attr('id', 'citrushtmledit_textarea');
        var tabs_div_preview = $('<div />').attr('id', 'citrushtmledit_preview');
        
        tabs.append(tabs_ul);
        tabs.append(tabs_div_textarea);
        tabs.append(tabs_div_preview);
        
        $(this).change(function(){
            $(preview).contents().find('body').css({'font-size': '0.8em'}).html($(this).val());
        });
        
        $(this).after(tabs);
        tabs_div_textarea.append(this);
        tabs_div_preview.append(preview);
        
        tabs.tabs();
        
        this.options = options;
        
        return this;
    };
    
    jQuery.fn.timepicker = function(_options){
        var sub = this;
        var options = jQuery.extend(true, {
        }, _options);
        
        $(this).autocomplete({
            source : [
                '00:00', '00:15', '00:30', '00:45', 
                '01:00', '01:15', '01:30', '01:45', 
                '02:00', '02:15', '02:30', '02:45', 
                '03:00', '03:15', '03:30', '03:45', 
                '04:00', '04:15', '04:30', '04:45', 
                '05:00', '05:15', '05:30', '05:45', 
                '06:00', '06:15', '06:30', '06:45', 
                '07:00', '07:15', '07:30', '07:45', 
                '08:00', '08:15', '08:30', '08:45', 
                '09:00', '09:15', '09:30', '09:45', 
                '10:00', '10:15', '10:30', '10:45', 
                '11:00', '11:15', '11:30', '11:45', 
                '12:00', '12:15', '12:30', '12:45', 
                '13:00', '13:15', '13:30', '13:45', 
                '14:00', '14:15', '14:30', '14:45', 
                '15:00', '15:15', '15:30', '15:45', 
                '16:00', '16:15', '16:30', '16:45', 
                '17:00', '17:15', '17:30', '17:45', 
                '18:00', '18:15', '18:30', '18:45', 
                '19:00', '19:15', '19:30', '19:45', 
                '20:00', '20:15', '20:30', '20:45', 
                '21:00', '21:15', '21:30', '21:45', 
                '22:00', '22:15', '22:30', '22:45', 
                '23:00', '23:15', '23:30', '23:45'
            ]
        });
        
        return this;
    };
    
    //
    // private
    //
    
    // list loadind 
    function dataLoadList(options){
        options.list.method.initialize();
        
        // search condition
        var condition = {};
        $.each(options.list.queries, function(ky, vl){
            if(ky !== 'service' && ky !== 'method') {
                condition[ky] = vl;
            }
        });

        var selector_list_dialog = $('#'+ options.list.dialog.id);
        selector_list_dialog.find('table.faces-search [id^=call_]').each(function(){
            var value = $(this).val();
            var condition_key = this.id.replace(options.list.queries.prefix, '');
            if(value) {
                condition[condition_key] = value;
            } else {
                delete condition[condition_key];
            }
        });
        
        var url = '/xhr/' + options.list.queries.service + '/' + options.list.queries.method;
        //options.client.program
        $.getJSON(url, condition, function(response) {
            if(response.results.result === true) {
                var table = selector_list_dialog.find('table.faces-list');
                var tbody = $('<tbody />');
                var tfoot = $('<tfoot />');
                
                if(options.list.dialog.type === 'summary' || options.list.dialog.type === 'selection') {
                    if($.objectSize(response.results.items) > 0) {
                        $.each(response.results.items, function(count, record){
                            var tr = $('<tr />').attr('rowid', record.rowid).attr('rev', record.rev);
                            $.each(options.primaries, function(ky,vl){
                                if(record[ky]) {
                                    tr.attr(ky, record[ky]);
                                } else if(vl) {
                                    tr.attr(ky, vl);
                                }
                            });
                            $.each(options.foreigns, function(ky,vl){
                                if(record[ky]) {
                                    tr.attr(ky, record[ky]);
                                } else if(vl) {
                                    tr.attr(ky, vl);
                                }
                            });
                            $.each(options.list.columns, function(ky, vl){
                                var td = $('<td />');
                                if(ky === 'exist' && options.list.dialog.type === 'selection') {
                                    var checkbox_id = $.uniqueID({prefix: options.id});
                                    var label = $('<label />').attr('for', checkbox_id);
                                    var checkbox = $('<input />').attr('type', 'checkbox').attr('id', checkbox_id).val(1);
                                    if (record['exist'] === true) {
                                        checkbox.attr('checked', 'checked');
                                        label.html('選択済');
                                    } else {
                                        label.html('未選択');
                                    }
                                    td.append(checkbox).append(label);
                                    
                                    $(checkbox).button().click(function(){
                                        $.each(options.primaries, function(ky, vl){
                                            options.primaries[ky] = $(tr).attr(ky);
                                        });
                                        if(checkbox.attr('checked') === 'checked') {
                                            dataOff(options);
                                            $(checkbox).button({label: '未選択'});
                                        } else {
                                            dataOn(options);
                                            $(checkbox).button({label: '選択済'});
                                        }
                                        dataLoadList(options);
                                    });
                                } else {
                                    if(typeof vl['class'] === 'function') {
                                        td.attr('class', vl['class'](record));
                                    } else if(vl['class']) {
                                        td.attr('class', vl['class']);
                                    }
                                    if(typeof vl.format === 'function') {
                                        td.html(vl.format(record));
                                    } else if(record[ky]) {
                                        td.html(record[ky]).attr('property', ky);
                                    }
                                }
                                
                                tr.append(td);
                            });
                            tbody.append(tr);
                        });
                        if(options.list.dialog.type === 'summary') {
                            tbody.find('tr').click(function(){
                                if($(this).hasClass('selected') === true) {
                                    recordDeselect(options, this);
                                } else {
                                    tbody.find('tr.selected').removeClass('selected');
                                    recordSelect(options, this);
                                }
                            });
                        }
                        if($.objectSize(options.list.summaries) > 0) {
                            var tr = $('<tr />');
                            $.each(options.list.summaries, function(ky, vl){
                                
                                var td = $('<td />');
                                
                                if(typeof vl['class'] === 'function') {
                                    td.attr('class', vl['class'](response.results.items));
                                } else if(vl['class']) {
                                    td.attr('class', vl['class']);
                                }
                                if(typeof vl.format === 'function') {
                                    td.html(vl.format(response.results.items));
                                }
                                $.each(vl, function(ky2, vl2){
                                    if(ky2 === 'class' || ky2 === 'format') {}
                                    else if(ky2 === 'name') {
                                        td.html(vl2);
                                    }
                                    else {
                                        td.attr(ky2,vl2);
                                    }
                                });
                                tr.append(td);
                            });
                            tfoot.append(tr);
                        }
                    }
                } else if(options.list.dialog.type === 'selection-panel') {
                    if($.objectSize(response.results.items) > 0) {
                        var tr;
                        $.each(response.results.items, function(count, record){
                            if((count + 1) % 5 === 1) {
                                tr = $('<tr />').attr('rowid', record.rowid).attr('rev', record.rev);
                            }
                            $.each(options.list.columns, function(ky, vl){
                                var td = $('<td />');
                                $.each(options.primaries, function(ky,vl){
                                    if(record[ky]) {
                                        td.attr(ky, record[ky]);
                                    } else if(vl) {
                                        td.attr(ky, vl);
                                    }
                                });
                                var checkbox_id = $.uniqueID({prefix: options.id});
                                var label = $('<label />').attr('for', checkbox_id);
                                var checkbox = $('<input />').attr('type', 'checkbox').attr('id', checkbox_id).val(1);
                                if (record['exist'] === true) {
                                    checkbox.attr('checked', 'checked');
                                    label.html('選択済');
                                }
                                else {
                                    label.html('未選択');
                                }
                                td.append(checkbox).append(label);
                                
                                $(checkbox).button().click(function(){
                                    $.each(options.primaries, function(ky, vl){
                                        options.primaries[ky] = $(td).attr(ky);
                                    });
                                    if(checkbox.attr('checked') === 'checked') {
                                        dataOff(options);
                                        $(checkbox).button({label: '未選択'});
                                    } else {
                                        dataOn(options);
                                        $(checkbox).button({label: '選択済'});
                                    }
                                    dataLoadList(options);
                                });
                                if(vl['class']) {
                                    td.attr('class', vl['class']);
                                }
                                if(typeof vl.format === 'function') {
                                    td.append(vl.format(record));
                                }
                                
                                tr.append(td);
                            });
                            if((count + 1) % 5 === 0 || (count + 1) === response.results.items.length ) {
                                tbody.append(tr);
                            }
                        });
                    }
                }
                
                table.find('tfoot').remove();
                table.append(tfoot);
                table.find('tbody').remove();
                table.append(tbody);

                recordDeselect(options);
                
                // pager
                if(response.results.pager) {
                    var pager_status = selector_list_dialog.find('table.faces-search tfoot tr.pager th.status');
                    var pager_navigation = selector_list_dialog.find('table.faces-search tfoot tr.pager th.navigation');

                    pager_status.find('.total').html($.numberFormat({number:response.results.pager.total}));
                    pager_status.find('.from').html($.numberFormat({number:response.results.pager.view_from}));
                    pager_status.find('.to').html($.numberFormat({number:response.results.pager.view_to}));
                    
                    var navigation = $('<ul />').attr('id', options.list.queries.prefix + 'pager');
                    navigation.append($('<li />').attr('page', response.results.pager.first).html('&lt;&lt;'));
                    navigation.append($('<li />').attr('page', response.results.pager.prev).html('&lt;'));
                    if (response.results.pager.view) {
                        $.each(response.results.pager.view, function(ky, vl){
                            navigation.append($('<li />').attr('page', vl).html(vl));
                        });
                    }
                    navigation.append($('<li />').attr('page', response.results.pager.next).html('&gt;'));
                    navigation.append($('<li />').attr('page', response.results.pager.last).html('&gt;&gt;'));
                    navigation.find('li').each(function(){
                       if ($(this).attr('page') === 'null') {
                           $(this).button({
                               disabled: true
                           });
                       } else {
                           $(this).button().click(function(){
                               $('#'+ options.list.queries.prefix +'page').val($(this).attr('page')).change();
                           });
                           if ($(this).attr('page') === response.results.pager.current) {
                               $(this).css('color', 'darkorange');
                           }
                       }
                    });
                    pager_navigation.empty().append(navigation);
                }
            }
        });
        options.list.method.release();
    }
    
    function dataLoadEdit(options){
        // search condition
        var condition = {};
        $.each(options.view.queries, function(ky, vl){
            if(ky !== 'service' && ky !== 'method') {
                condition[ky] = vl;
            }
        });
        $('#'+ options.list.dialog.id).find('.faces-edit [id^=edit_]').each(function(){
            var value = $(this).val();
            if(value) {
                condition[this.id.replace(options.view.queries.prefix, '')] = value;
            } else {
                delete condition[this.id.replace(options.view.queries.prefix, '')];
            }
        });
        
        var url = '/xhr/' + options.view.queries.service + '/' + options.view.queries.method;
        //options.client.program
        $.getJSON(url, condition, function(response){
            if(response.results.result === true) {
                var selector_edit_dialog = $('#'+ options.edit.dialog.id);
                $.each(response.results.items, function(ky, vl){
                    var edit_faces_tag = selector_edit_dialog.find('.faces-edit #edit_' + options.edit.faces.id + '_' + ky);
                    var view_faces_tag = selector_edit_dialog.find('.faces-edit #view_' + options.view.faces.id + '_' + ky);
                    if(edit_faces_tag.length > 0) {
                        if (edit_faces_tag.get(0).tagName === 'SELECT') {
                            edit_faces_tag.val(vl).change();
                        } else {
                            edit_faces_tag.val(vl).html(vl).change();
                        }
                    }
                    else if(view_faces_tag.length > 0) {
                        view_faces_tag.html(vl);
                    }
                });
            }
        });
    }
    function dataClearEdit(options){
        $('#'+ options.edit.dialog.id).find('.faces-edit [id^=edit_]').each(function(){
            var form_element = $(this);
            var default_value = form_element.attr('default');
            if (default_value === undefined) {
                default_value = '';
            }
            switch(form_element.get(0).tagName) {
                case 'INPUT' :
                case 'HIDDEN' :
                    if(form_element.attr('type') === 'text' || form_element.attr('type') === 'file') {
                        form_element.val(default_value).change();
                    }
                    break;
                case 'TEXTAREA' :
                    form_element.val(default_value);
                    break;
                case 'SELECT' :
                    form_element.val(default_value);
                    break;
            }
        });
    }
    function dataLoadView(options){
        // search condition
        var condition = {};
        $.each(options.view.queries, function(ky, vl){
            if(ky !== 'service' && ky !== 'method') {
                condition[ky] = vl;
            }
        });

        var selector_view_dialog = $('#'+ options.view.dialog.id);
        selector_view_dialog.find('.faces-edit [id^=view_]').each(function(){
            var value = $(this).val();
            if(value) {
                condition[this.id.replace(options.view.queries.prefix, '')] = value;
            } else {
                delete condition[this.id.replace(options.view.queries.prefix, '')];
            }
        });
        
        var url = '/xhr/' + options.view.queries.service + '/' + options.view.queries.method;
        //options.client.program
        $.getJSON(url, condition, function(response){
            if(response.results.result === true) {
                $.each(response.results.items, function(ky, vl){
                    if(selector_view_dialog.find('.faces-view #view_' + options.view.faces.id + '_' + ky).length > 0) {
                        selector_view_dialog.find('.faces-view #view_' + options.view.faces.id + '_' + ky).html(vl);
                    }
                });
            }
        });
    }
    function dataModify(options) {
        var result = false;
        var entity = {};
        
        $.each(options.primaries, function(ky, vl){
            if(vl !== null && vl !== 'null') {
                entity[ky] = vl;
            }
        });
        $.each(options.edit.queries, function(ky, vl){
            if(ky !== 'service' && ky !== 'method') {
                entity[ky] = vl;
            }
        });
        $('#'+ options.edit.dialog.id).find('.faces-edit [id^=edit_]').each(function(){
            var form_type = $(this).attr('type');
            var ky = $(this).attr('id').replace(options.edit.queries.prefix, '');
            var vl = $(this).val();
            if (vl === null && form_type === 'select') {
                $(this).find('option:first').prop('selected', true);
                vl = $(this).val();
            }
            entity[ky] = vl;
        });
        
        var url = '/xhr/' + options.edit.queries.service + '/modify';
        
        $.post(url, entity, function(response){
            result = response.results.result;
            $.message({messages: response.messages});
        }, 'json');
        
        return result;
    }
    function dataRemove(options) {
        var result = false;
        var entity = {
            rowid: options.edit.queries.rowid,
            rev: options.edit.queries.rev,
            prefix: options.edit.queries.prefix,
            status: 9
        };
        if(entity.rowid === null && entity.rev === null) {
            $.each(options.primaries, function(ky, vl){
                entity[ky] = options.edit.queries[ky];
            });
        }
        var url = '/xhr/' + options.edit.queries.service + '/remove';
        
        $.post(url, entity, function(response){
            result = response.results.result;
            $.message({messages: response.messages});
        }, 'json');
        
        return result;
    }
    function dataOn(options) {
        var result = false;
        var entity = {};
        
        $.each(options.toggle.queries, function(ky, vl){
            if(ky !== 'service' && ky !== 'method') {
                entity[ky] = vl;
            }
        });
        $.each(options.primaries, function(ky, vl){
            entity[ky] = vl;
        });
        var url = '/xhr/' + options.toggle.queries.service + '/on';
        
        $.post(url, entity, function(response){
            result = response.results.result;
            $.message({messages: response.messages});
        }, 'json');
        
        return result;
    }
    function dataOff(options) {
        var result = false;
        var entity = {};
        
        $.each(options.toggle.queries, function(ky, vl){
            if(ky !== 'service' && ky !== 'method') {
                entity[ky] = vl;
            }
        });
        $.each(options.primaries, function(ky, vl){
            entity[ky] = vl;
        });
        
        var url = '/xhr/' + options.edit.queries.service + '/off';
        
        $.post(url, entity, function(response){
            result = response.results.result;
            $.message({messages: response.messages});
        }, 'json');
        
        return result;
    }
    function recordSelect(options, record) {
        $(record).addClass('selected');
        
        options.edit.queries.rowid = $(record).attr('rowid');
        options.edit.queries.rev = $(record).attr('rev');
        options.view.queries.rowid = $(record).attr('rowid');
        options.view.queries.rev = $(record).attr('rev');
        
        $('#'+options.id+'.faces-area button[id^=open-dialog-]:not([id$=-list])').button('option', 'disabled', false);
        $('#'+options.id+'.faces-area button[id^=open-dialog-][id$=-list]').each(function(){
            if($(this).button('option', 'canChangeButtonState') === true) {
                $(this).button('option', 'disabled', false);
            }
        });
    }
    function recordDeselect(options, record) {
        $(record).removeClass('selected');
        
        $.each(options.view.queries, function(ky, vl){
            if(ky !== 'method' && ky !== 'prefix' && ky !== 'service') {
                options.view.queries[ky] = null;
            }
        });
        $.each(options.edit.queries, function(ky, vl){
            if(ky !== 'method' && ky !== 'prefix' && ky !== 'service') {
                delete options.edit.queries[ky];
            }
        });
        
        $('#'+options.id+'.faces-area button[id^=open-dialog-]:not([id$=-edit]):not([id$=-list])').button('option', 'disabled', true);
        $('#'+options.id+'.faces-area button[id^=open-dialog-][id$=-list]').each(function(){
            if($(this).button('option', 'canChangeButtonState') === false) {} else {
                $(this).button('option', 'disabled', true);
            }
        });
    }

    jQuery.extend({
        showOverlay : function() {
            if ($('body').children().is('#citrus-overlay')) {
                $('#citrus-overlay').show();
            }
            else {
                var css = {
                    width: $(document).width(),
                    height: $(document).height(),
                    zIndex: 2000,
                    dispay: 'block',
                    position: 'absolute',
                    top: 0,
                    left: 0,
                    opacity: 0.3,
                    backgroundAttachment: 'scroll',
                    backgroundColor: '#000000',
                    backgroundImage: 'url("data:image/gif;base64,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")',
                    backgroundPosition: 'center center',
                    backgroundRepeat: 'no-repeat'
                };
                $('<div />').attr('id', 'citrus-overlay').css(css).appendTo(document.body);
            }
        },
        hideOverlay: function() {
            if ($('body').children().is('#citrus-overlay')) {
                $('#citrus-overlay').hide();
            }
        },
        
        message: function(_options){
            var options = jQuery.extend(true, {
                position: 'bottom',
                messages: []
            }, _options);
            
            if (options.messages === null) {
                return;
            }
            if (options.messages.length === 0) {
                return;
            }
            
            var class_name;
            switch (options.messages[0].type) {
                case 'message':
                    class_name = 'ui-state-highlight';
                    break;
                case 'error':
                    class_name = 'ui-state-error';
                    break;
            }
            
            var stylesheet = {
                zIndex: 9999
            };
            switch (options.position) {
                case 'top':
                    stylesheet.position = 'fixed';
                    stylesheet.top = '0px';
                    stylesheet.left = '20px';
                    stylesheet.width = ($(document).width() - 80) + 'px';
                    break;
                case 'bottom':
                    stylesheet.position = 'fixed';
                    stylesheet.bottom = '0px';
                    stylesheet.left = '20px';
                    stylesheet.width = ($(document).width() - 80) + 'px';
                    break;
            }
            
            var frame;
            var selector_citrus_message = $('#citrus-message');
            if (selector_citrus_message.length > 0) {
                frame = selector_citrus_message;
            }
            else {
                frame = $('<div />').attr({
                    'id': 'citrus-message'
                }).appendTo(document.body);
            }
            
            $(frame).attr('class', 'ui-message-frame ui-corner-all ' + class_name).css(stylesheet).show();
            
            var date = new Date().getTime();
            for (var i in options.messages) {
                $(frame).append($('<p />').attr({
                    'date': date
                }).html(options.messages[i].date + '&nbsp;' + options.messages[i].description).show(0, setTimeout(function(){
                    $(frame).find('[date='+date+']').fadeOut(1000, function(){
                        $(this).remove('#citrus-message [date='+date+']');
                        if($(frame).children().length === 0) {
                            $(frame).empty().hide();
                        }
                    });
                }, 5000)));
            }
        }
    });
})(jQuery);