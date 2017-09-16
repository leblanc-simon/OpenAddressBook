/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

var tpl_button = '<button class="%s" data-id="%d">%s</button>';
var tpl_callers = '<option value="%s"%s>%s</option>';

$(document).ready(function() {
    $.ajax({
        url: url_all,
        type: 'GET',
        dataType: 'json',
        success: function(datas) {
            console.debug(datas);
            var tbody = $('table tbody');
            $.each(datas, function(iterator, item) {
                console.debug(item);
                var tr = '<tr data-id="' + item.id + '">';

                $.each(allowed_datas, function(key, value) {
                    var icon = '';
                    if (click2call_enable === true && value.type === 'tel' && item[key]) {
                        icon = '<i class="phone" data-tel="' + convertToTel(item[key]) + '"></i>';
                    }
                    
                    if (value.type === 'email' && item[key]) {
                        tr += sprintf('<td class="%s"><a href="mailto:%s">%s</a></td>', value.type, htmlspecialchars(item[key]), htmlspecialchars(item[key]));
                    } else {
                        tr += sprintf('<td class="%s">%s</td>', value.type, item[key] ? htmlspecialchars(item[key]) + icon : '');
                    }

                
                });
                tr += '<td>' + sprintf(tpl_button, 'edit icon-pencil', item.id, '&nbsp;') + sprintf(tpl_button, 'delete icon-remove', item.id, '&nbsp;') + '</td>';
                tr += '</tr>';
                tbody.append(tr);
            });

            if (click2call_enable === true) {
                $('select[name=callers]').trigger('change');
            }
        }
    });

    if (click2call_enable === true) {
        $.ajax({
            url: url_callers,
            type: 'GET',
            dataType: 'json',
            success: function(datas) {
                var keys = Object.getOwnPropertyNames(datas);
                var callers = '';
                var save_caller = readCookie('open-address-book-caller');
                $.each(keys, function(iterator, item) {
                    var selected = '';
                    if (save_caller === item) {
                        selected = ' selected="selected"';
                    }
                    callers += sprintf(tpl_callers, item, selected, datas[item].name);
                });

                $('.callers').append('<select name="callers"><option></option>' + callers + '</select>');
                $('select[name=callers]').trigger('change');
            }
        });

        // Add custom phone to call
        var input_custom_tel = document.createElement('input');
        input_custom_tel.setAttribute('type', 'text');
        input_custom_tel.setAttribute('placeholder', 'Appeler');
        var submit_custom_tel = document.createElement('i');
        submit_custom_tel.setAttribute('class', 'custom-phone phone');
        if (window.mozInnerScreenX !== undefined) {
            // it's Firefox : fix position (yes, it's crap !)
            submit_custom_tel.setAttribute('style', 'top: 7px;');
        }
        var menu = document.getElementById('custom-call');
        menu.appendChild(input_custom_tel);
        menu.appendChild(submit_custom_tel);

        // autoselect text on focus
        $(input_custom_tel).on('focus', function() { $(this).select(); });
        $(input_custom_tel).on('mouseup', function() { return false; });
    }

    thead_tr = $('table > thead > tr:first-child');
    $.each(allowed_datas, function(key, value) {
        thead_tr.append('<th>' + value.label + '</th>');
    });
    thead_tr.append('<th></th>');

    $(document).on('click', '.edit', function(){
        $.ajax({
            url: sprintf(url_uniq, $(this).attr('data-id')),
            type: 'GET',
            dataType: 'json',
            success: function(item) {
                var form = '';
                $.each(allowed_datas, function(key, value) {
                    form += buildInput(key, item[key] ? item[key] : '', value.label, value.type);
                });

                $('form .content').html(form);
                $('form').attr('action', sprintf(url_uniq, item.id));
                $('.form').removeClass('hide');
                $('.form input:eq(0)').focus();
                console.debug(form);
            }
        });

        return false;
    });

    $(document).on('click', '.new', function(){
        openNewForm();
        return false;
    });

    $(document).on('click', '.delete', function(){
        if (!confirm('Souhaitez-vous supprimer le contact ?')) {
            return false;
        }

        var delete_id = $(this).attr('data-id');
        $.ajax({
            url: sprintf(url_uniq, delete_id),
            type: 'DELETE',
            dataType: 'json',
            success: function(result) {
                console.debug(result);

                $('table > tbody > tr[data-id=' + delete_id + ']').remove();
            }
        });

        return false;
    });

    $(document).on('click', 'button[type=reset]', function(){
        $('.form').addClass('hide');
        $('form .content').html('');
        $('input[type=search]').focus();
        return false;
    });

    $(document).on('submit', 'form', function(){
        var form = $(this);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(item) {
                var tr = '';

                $.each(allowed_datas, function(key, value) {
                    var icon = '';
                    if (click2call_enable === true && value.type === 'tel' && item[key]) {
                        icon = '<i class="phone" data-tel="' + convertToTel(item[key]) + '"></i>';
                    }

                    tr += sprintf('<td class="%s">%s</td>', value.type, item[key] ? htmlspecialchars(item[key]) + icon : '');
                });

                tr += '<td>' + sprintf(tpl_button, 'edit icon-pencil', item.id, '&nbsp;') + sprintf(tpl_button, 'delete icon-remove', item.id, '&nbsp;') + '</td>';

                var line = $('table > tbody > tr[data-id=' + item.id + ']');
                if (line.length) {
                    line.html(tr);
                } else {
                    $('table tbody').append('<tr data-id="' + item.id + '">' + tr + '</tr>');
                }

                $('.form').addClass('hide');
                $('form .content').html('');
                $('input[type=search]').focus();

                if (click2call_enable === true) {
                    $('select[name=callers]').trigger('change');
                }
            }
        });

        return false;
    });

    // Escape : clear search
    $(document).on('keypress', 'input[type=search]', function(e){
        if (e.keyCode === 27) { // 27 : ESC
            $(this).val('');
            $(this).keyup();
        }
    });

    // Ctrl + F or F3 : set focus to the search input
    $(document).on('keydown', function(e){
        if (e.keyCode === 114 || (e.ctrlKey && e.keyCode === 70)) {
            $('input[type=search]').focus();
            return false;
        }
    });

    // Click2Call
    if (click2call_enable === true) {
        $(document).on('change', 'select[name=callers]', function(){
            createCookie('open-address-book-caller', $(this).val());
            if ($(this).val() === '') {
                disableClick2Call();
            } else {
                enableClick2Call();
            }
        });

        $(document).on('paste', '#custom-call input', function(){
            setTimeout(function(){
                $('.custom-phone').trigger('click');
            }, 0);
        });

        $(document).on('keypress', '#custom-call input', function(e){
            if (e.keyCode === 13) { // Enter
                $('.custom-phone').trigger('click');
            }
        });

        $(document).on('click', '.action-cancel', function(){
            $('#add-proposal').slideUp('slow');
        });

        $(document).on('click', '.action-add', function(){
            var phone_number = $('#add-proposal .add-tel').html();
            $('#add-proposal').slideUp('slow');
            openNewForm(phone_number);
        });

        var clear_custom_phone = false;
        $(document).on('click', '.custom-phone', function(){
            var custom_phone_number = document.getElementById('custom-call').getElementsByTagName('input')[0].value;
            phone_number = convertToTel(custom_phone_number);
            if (!phone_number) {
                return false;
            }

            $(this).attr('data-tel', phone_number);
            clear_custom_phone = true;

            if (true === checkTelInCurrentTable(phone_number)) {
                var $add_proposal = $('#add-proposal');
                $add_proposal.find('.add-tel').html(custom_phone_number);
                $add_proposal.slideDown('slow');
            }

            return true;
        });

        $(document).on('click', '.phone', function(){
            var tel = $(this).attr('data-tel');
            if ($.trim(tel) === '') {
                return false;
            }

            $.ajax({
                url: sprintf(url_call, $('select[name=callers]').val(), tel),
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.debug(data);
                },
                error: function() {
                    $('#error').slideDown('slow', function(){
                        setTimeout(function(){
                            $('#error').slideUp('slow');
                        }, 2500);
                    });
                }
            });
            if (true === clear_custom_phone) {
                $('.custom-phone').removeAttr('data-tel');
            }
            return false;
        });
    }
});
