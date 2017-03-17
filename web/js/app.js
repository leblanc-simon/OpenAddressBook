/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

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
                    
                    if (value.type === 'link' && item[key]) {
                        let link = 'https://audit.enova.fr:8443/asset/show/' + item[key];
                        tr += sprintf('<td class="%s"><a href="%s" target="_blank">%s</a></td>', value.type, htmlspecialchars(link), 'lien');
                    } else if (value.type === 'date' && item[key]) {
                        let dates = item[key].split('/');
                        tr += sprintf('<td class="%s"><span>%s</span>%s</td>', value.type, dates[2] + dates[1] + dates[0], item[key]);
                    } else {
                        tr += sprintf('<td class="%s">%s</td>', value.type, item[key] ? htmlspecialchars(item[key]) + icon : '');
                    }

                
                });
                tr += '</tr>';
                tbody.append(tr);
            });

            if (click2call_enable === true) {
                $('select[name=callers]').trigger('change');
            }
        }
    });

    thead_tr = $('table > thead > tr:first-child');
    $.each(allowed_datas, function(key, value) {
        thead_tr.append('<th>' + value.label + '</th>');
    });
    thead_tr.append('<th></th>');

    // Escape : clear search
    $(document).on('keypress', 'input[type=search]', function(e){
        if (e.keyCode == 27) { // 27 : ESC
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

    var sales = new TableSort("material");
});
