'use strict';

var delete_contact = function(id, source, type) {
    if (!hm_delete_prompt()) {
        return false;
    }
    Hm_Ajax.request(
        [{'name': 'hm_ajax_hook', 'value': 'ajax_delete_contact'},
        {'name': 'contact_id', 'value': id},
        {'name': 'contact_type', 'value': type},
        {'name': 'contact_source', 'value': source}],
        function(res) {
            if (res.contact_deleted && res.contact_deleted === 1) {
                $('.contact_row_'+id).remove();
            }
        }
    );
};

var add_contact_from_message_view = function() {
    var contact = $('#add_contact').val();
    var source = $('#contact_source').val();

    if (contact) {
      Hm_Ajax.request(
        [
          { name: 'hm_ajax_hook', value: 'ajax_add_contact' },
          { name: 'contact_value', value: contact },
          { name: 'contact_source', value: source },
        ],
        function (res) {
          $('.add_contact_controls').toggle();
          window.location.reload();
          remove_message_content();
        }
      );
    }
  };

var add_contact_from_popup = function(event) {
    event.stopPropagation()
    var source = 'local:local';
    var contact = $('#contact_info').text().replace('>','').replace('<','');


    if (contact) {
        var emailRegex = /\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/g;
        var email = contact.match(emailRegex)[0];
        var name = contact.replace(emailRegex, "");

        var saveContactContent = `<div><table>
                                            <tr><td><strong>Name :</strong></td><td>${name}</td></tr>
                                            <tr><td><strong>Email :</strong></td><td>${email}</td></tr>
                                            <tr><td><strong>Source :</strong></td><td>Local</td></tr>
                                </table></div>`

        Hm_Ajax.request(
            [{'name': 'hm_ajax_hook', 'value': 'ajax_add_contact'},
            {'name': 'contact_value', 'value': contact},
            {'name': 'contact_source', 'value': source}],
            function (res) {
                $("#contact_popup_body").html(saveContactContent);
                sessionStorage.removeItem(`${window.location.pathname}imap_4_${hm_list_path()}`);
                sessionStorage.removeItem(`${window.location.pathname}${hm_msg_uid()}_${hm_list_path()}`);
            }
        );
    }
};

var get_search_term = function(class_name) {
    var fld_val = $(class_name).val();
    var addresses = fld_val.split(' ');
    if (addresses.length > 1) {
        fld_val = addresses.pop();
    }
    return fld_val;
};

var autocomplete_contact = function(e, class_name, list_div) {
    var key_code = e.keyCode;
    if (key_code >= 37 && key_code <= 40) {
        return;
    }
    var first;
    var div = $('<div></div>');
    var fld_val = get_search_term(class_name);
    if (fld_val.length > 0) {
        Hm_Ajax.request(
            [{'name': 'hm_ajax_hook', 'value': 'ajax_autocomplete_contact'},
            {'name': 'contact_value', 'value': fld_val}],
            function(res) {
                var active = $(document.activeElement).attr('class');
                if (active == 'compose_to' || active == 'compose_bcc' || active == 'compose_cc') {
                    if (res.contact_suggestions) {
                        var i;
                        var count = 0;
                        $(list_div).html('');
                        for (i in res.contact_suggestions) {
                            var suggestion = JSON.parse(res.contact_suggestions[i].replace(/&quot;/g, '"'))

                            div.html(suggestion.contact);
                            if ($(class_name).val().match(div.text())) {
                                continue;
                            }
                            if (count == 0) {
                                first = 'first ';
                            }
                            else {
                                first = '';
                            }
                            count++;
                            $(list_div).append('<a tabindex="1" href="#" class="'+first+'contact_suggestion" data-id="'+suggestion.contact_id+'" data-type="'+suggestion.type+'" data-source="'+suggestion.source+'" unread_link">'+suggestion.contact+'</a>');
                        }
                        if (count > 0) {
                            $(list_div).show();
                            setup_autocomplete_events(class_name, list_div, fld_val);
                        }
                        else {
                            $(list_div).hide();
                        }
                    }
                }
            }, [], true
        );
    }
};

var autocomplete_keyboard_nav = function(event, list_div, class_name, fld_val) {
    var in_list = false;
    if (event.keyCode == 40) {
        if ($(event.target).prop('nodeName') == 'INPUT') {
            $('.first').addClass('selected_menu');
            $('.first').focus();
            in_list = true;
        }
        else {
            $(event.target).removeClass('selected_menu');
            $(event.target).next().addClass('selected_menu');
            $(event.target).next().focus();
            in_list = true;
        }
        return false;
    }
    else if (event.keyCode == 38) {
        if ($(event.target).prev().length) {
            $(event.target).removeClass('selected_menu');
            $(event.target).prev().addClass('selected_menu');
            $(event.target).prev().focus();
            in_list = true;
        }
        else {
            $(class_name).focus();
            $(event.target).removeClass('selected_menu');
        }
        return false;
    }
    else if (event.keyCode == 13) {
        $(class_name).focus();
        $(list_div).hide();
        add_autocomplete(event, class_name, list_div);
        return false;
    }
    else if (event.keyCode == 27) {
        $(list_div).html('');
        $(list_div).hide();
        $(class_name).focus();
        return false;
    }
    else if (event.keyCode == 9) {
        $(list_div).html('');
        $(list_div).hide();
        $(class_name).trigger('focusout');
        return true;
    }
    if (in_list) {
        return false;
    }
    return true;
};

var setup_autocomplete_events = function(class_name, list_div, fld_val) {
    $('.contact_suggestion').on("click", function(event) { return add_autocomplete(event, class_name, list_div); });
    $(class_name).on('keydown', function(event) { return autocomplete_keyboard_nav(event, list_div, class_name, fld_val); });
    $('.contact_suggestion').on('keydown', function(event) { return autocomplete_keyboard_nav(event, list_div, class_name, fld_val); });
    $(document).on("click", function() { $(list_div).hide(); });
};

var add_autocomplete = function(event, class_name, list_div, fld_val) {
    $(class_name).attr("data-id", $(event.target).data('id'));
    $(class_name).attr("data-type", $(event.target).data('type'));
    $(class_name).attr("data-source", $(event.target).data('source'));

    if (!fld_val) {
        fld_val = get_search_term(class_name);
    }
    var new_address = $(event.target).text()
    var existing = $(class_name).val();
    var re = new RegExp(fld_val+'$');
    existing = existing.replace(re, '');
    if (existing.length) {
        existing = existing.replace(/[\s,]+$/, '')+', ';
    }
    $(list_div).html('');
    $(list_div).hide();
    $(class_name).val(existing+new_address);
    $(class_name).focus();
    return false;
};

if (hm_page_name() == 'contacts') {
    $('.delete_contact').on("click", function() {
        delete_contact($(this).data('id'), $(this).data('source'), $(this).data('type'));
        return false;
    });
    $('.show_contact').on("click", function() {
        $('#'+$(this).data('id')).toggle();
        return false;
    });
    $('.reset_contact').on("click", function() {
        window.location.href = '?page=contacts';
    });
    $('.server_title').on("click", function() {
        $(this).next().toggle();
    });
    $('#contact_phone').on("keyup", function() {
        let contact_phone = $('#contact_phone').val();
        const regex_number = new RegExp('^\\d+$');
        const allowed_characters = ['+','-','(',')'];
        for (let chain_counter = 0; chain_counter < contact_phone.length; chain_counter++) {
            if(!(regex_number.test(contact_phone[chain_counter])) && !(allowed_characters.indexOf(contact_phone[chain_counter]) > -1)){
                Hm_Notices.show(["This phone number appears to contain invalid character (s).\nIf you are sure ignore this warning and continue!"]);
                $(this).off();
            }
        }

    });
}
else if (hm_page_name() == 'compose') {
    $('.compose_to').on('keyup', function(e) { autocomplete_contact(e, '.compose_to', '#to_contacts'); });
    $('.compose_cc').on('keyup', function(e) { autocomplete_contact(e, '.compose_cc', '#cc_contacts'); });
    $('.compose_bcc').on('keyup', function(e) { autocomplete_contact(e, '.compose_bcc', '#bcc_contacts'); });
    $('.compose_to').focus();
}

$(function() {
    $('.source_link').on("click", function() { $('.list_actions').toggle(); $('#list_controls_menu').hide(); return false; });
    //import_contact
    // $('#import_contactss').on("click", function () {
        // var form = new FormData($('#import_form')[0]);
        // console.log(form);
        // var fileInput = document.getElementById('contact_csv');
        // var file = fileInput.files[0];
        // $('.contact_import_detail a').css({ display: "block" });
        // var form = $('#import_form');
        // var form = new FormData();
        // form.append('hm_ajax_hook', 'ajax_import_contact');
        // form.append('contact_source', 'csv');
        // form.append('import_contact', 'test');
        // form.append('myfile', $('input[type=file')[0].files[0]);

        // console.log('myfile', form);
        
    //     if(!file) {
    //         Hm_Notices.show('ERREmpty file provided');
    //     }
    //     Hm_Ajax.request(
    //         [
    //             { name: 'hm_ajax_hook', 'value': 'ajax_import_contact' },
    //             { name: 'contact_source', 'value': 'csv' },
    //             { name: 'import_contact', 'value': 'test' },
    //             { name: 'myfile', 'value': $('input[type=file')[0].files[0] },
    //         ],
    //         function (res) {
    //             console.log('log::::', res);
    //             $('.contact_import_detail a').css({ display: "block" });
    //         }
    //     );
    // });


    $('.show_import_detail').on('click', function () {
        if (hm_page_name() === 'contacts') {
            /**************************************************************************************
             *                             TINGLE IMPORT MODAL
             **************************************************************************************/
            var import_detail_modal = new tingle.modal({
                footer: true,
                stickyFooter: false,
                closeMethods: ['overlay', 'button', 'escape'],
                closeLabel: "Close",
                cssClass: ['custom-class-1', 'custom-class-2'],
                onOpen: function () {
                },
                onClose: function () {
                },
                beforeClose: function () {
                    // here's goes some logic
                    // e.g. save content before closing the modal
                    return true; // close the modal
                    return false; // nothing happens
                }
            });

            // set content
            import_detail_modal.setContent('TEST TTTT');
            // set content
            // import_detail_modal.setContent(document.querySelector('#import_detail_modal').innerHTML);
            // $('#import_detail_modal').remove();

            // // add a button
            // import_detail_modal.addFooterBtn('Save', 'tingle-btn tingle-btn--primary tingle-btn--pull-right', async function () {
            //     save_script(current_account);
            // });

            // // add another button
            // edit_script_modal.addFooterBtn('Close', 'tingle-btn tingle-btn--default tingle-btn--pull-right', function () {
            //     // here goes some logic
            //     import_detail_modal.close();
            // });

            // console.log('content::::', document.querySelector('#import_detail_modal').innerHTML)
            // alert(document.querySelector('#import_detail_modal').innerHTML)

            import_detail_modal.open();

        }












        return false;
    });
});



