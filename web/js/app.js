/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

var tpl_button = '<button class="%s" data-id="%d">%s</button>';

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
                $.each(allowed_datas, function(key) {
                    tr += sprintf('<td>%s</td>', item[key] ? htmlspecialchars(item[key]) : '');
                });
                tr += '<td>' + sprintf(tpl_button, 'edit', item.id, '&nbsp;') + sprintf(tpl_button, 'delete', item.id, '&nbsp;') + '</td>';
                tr += '</tr>';
                tbody.append(tr);
            });
        }
    });

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
                console.debug(form);
            }
        });

        return false;
    });

    $(document).on('click', '.new', function(){
        var form = '';
        $.each(allowed_datas, function(key, value) {
            form += buildInput(key, '', value.label, value.type);
        });

        $('form .content').html(form);
        $('form').attr('action', url_all);
        $('.form').removeClass('hide');
        console.debug(form);

        return false;
    });

    $(document).on('click', '.delete', function(){
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
                $.each(allowed_datas, function(key) {
                    tr += sprintf('<td>%s</td>', item[key] ? htmlspecialchars(item[key]) : '');
                });
                tr += '<td>' + sprintf(tpl_button, 'edit', item.id, '&nbsp;') + sprintf(tpl_button, 'delete', item.id, '&nbsp;') + '</td>';

                var line = $('table > tbody > tr[data-id=' + item.id + ']');
                if (line.length) {
                    line.html(tr);
                } else {
                    $('table tbody').append('<tr data-id="' + item.id + '">' + tr + '</tr>');
                }

                $('.form').addClass('hide');
                $('form .content').html('');
            }
        });

        return false;
    });

    $(document).on('keypress', 'input[type=search]', function(e){
        if (e.keyCode == 27) { // 27 : ESC
            $(this).val('');
            $(this).keyup();
        }
    });
});