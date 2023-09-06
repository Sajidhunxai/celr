(function ($) {
    var room = 1;
    $(document).ready(function () {
    });
    $(document).on('click', '.wnbell-addElement', function (e) {
        var tab = wnbell_get('tab');
        var name = '';
        var default_value = '';
        var style_id = '';
        var style_class = '';
        if (tab === 'item') {
            name = 'wnbell_name';
            default_value = 'wnbell_default_value';
            style_id = 'wnbell_id_attribute';
            style_class = 'wnbell_class_attribute';
        }
        else if (tab === 'wc_order_updates') {
            name = 'wcou_name';
            default_value = 'wcou_default_value';
            style_id = 'wcou_id_att';
            style_class = 'wcou_class_att';
        }
        room++;
        var objTo = document.getElementById('items');
        var divtest = document.createElement("table");
        divtest.classList.add("form-table");
        divtest.innerHTML = '<tr style="height:1px;"><td colspan="2"><div style="background-color:#bbb;;width:80%;height:1px;margin:0 auto;"></div></td></tr>';
        divtest.innerHTML += '<br/><tr style="vertical-align:bottom;"><th scope="row" >Name ' + room + '</th><td><input type="text" name="' + name + '[]" value=""/></td></tr>';
        divtest.innerHTML += '<tr style="vertical-align:bottom"><th scope="row">Default value</th><td><input type="text" name="' + default_value + '[]" value=""/></td></tr>';
        divtest.innerHTML += '<tr style="vertical-align:bottom"><th scope="row">Id attribute</th><td><input type="text" name="' + style_id + '[]" value=""/></td></tr>';
        divtest.innerHTML += '<tr style="vertical-align:bottom"><th scope="row">Class attribute</th><td><input type="text" name="' + style_class + '[]" value=""/></td></tr>';
        divtest.innerHTML += '<button type="button" class="wnbell-btn-element wnbell-addElement">Add</button>';
        divtest.innerHTML += '<button type="button" class="wnbell-btn-element removeElement">Remove</button>';
        objTo.appendChild(divtest);
    });


    $(document).on('click', '.removeElement', function (e) {
        $(this).parent().remove();
    });
    $(document).on('click', '#wnbell_sound_1', function (e) {
        $('#wnbell-test-sound-1').get(0).play();
    });
    $(document).on('click', '#wnbell_sound_2', function (e) {
        $('#wnbell-test-sound-2').get(0).play();
    });
    $(document).on('click', '#wnbell_sound_3', function (e) {
        $('#wnbell-test-sound-3').get(0).play();
    });
    $(document).on('click', '#wnbell_sound_4', function (e) {
        $('#wnbell-test-sound-4').get(0).play();
    });
})(jQuery);

// get "GET" request parameters
function wnbell_get(name) {
    if (name = (new RegExp('[?&]' + encodeURIComponent(name) + '=([^&]*)')).exec(location.search))
        return decodeURIComponent(name[1]);
}
function wnbell_show_statuses(elem, statuses, i) {
    var size = statuses.length;
    var objTo = elem.parentNode;
    var p = document.getElementById("wnb_field" + i);
    if (!objTo.contains(p)) {
        var divtest = document.createElement("p");
        divtest.setAttribute("id", "wnb_field" + i);
        while (size != 0) {
            divtest.innerHTML += '<br/><div class="wnbell_custom_wc">' + statuses[statuses.length - size] + '</div><input type="text" name="wcou_custom_dv_' + i + '[]" value=""/><br/>';
            size--;
        }

        objTo.appendChild(divtest);
    }
}