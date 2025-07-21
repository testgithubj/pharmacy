function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;

    console.log(charCode);
    // 190 and 110 for decimal 
    // 96 - 105 for num keypad decimal 
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        if (charCode == 110 || charCode == 190 || (charCode >= 96 && charCode <= 105)) {
            return true; // for decimal 
        }

        return false;
    }
    return true;
}


/* Modal function
 * Using bootstrap modal
 * Prepend to html tag then open
 * */
/* Modal call */
function modalCall(heading, text, body, size) {
    var html = '<div style="" class="modal fade bs-example-modal-' + size + '" id="modalCall" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">'
            + '<div class="modal-dialog modal-' + size + '">'
            + '<div class="modal-content" style="padding: 15px"><h3>' + heading + '</h3>'
            + '<h4 style="">' + text + '</h4>'
            + '<div class="clearfix">&nbsp;</div>'
            + '<div class="">' + body + '</div>'
            + '<div class="col-xs-12 text-right">'
            + '<button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>'
            + '</div>'
            + '<div>&nbsp;</div>'
            + '</div>'
            + '</div>'
            + '</div>';
    $('body').prepend(html);
    $('#modalCall').modal({'backdrop': 'static'});
    $('#modalCall').addClass('animated bounce');
    $('#modalCall').on('hidden.bs.modal', function(e) {
        $('#modalCall').remove();
    });
}


$('.btn-new-prescription').click(function() {
    var btn = $(this);
    var form_content = $('.new-user-form');
    form_content.html('<i class="fa fa-spinner fa-spin">');
    $.get('/prescrive/pop').done(function(e) {
        form_content.html('');
        $.fn.Modal({'borderRadius': '0px', 'backdrop': 'static', 'body': e, 'heading': 'Add New Prescription', 'size': 'large'});
        //btn.addClass('hide');
        form_content.animate({scrollTop: 200}, 1000);

    });

});



function addDiagnosisField() {
    // alert($.parseJSON(drug).length);
    $options = '<option> Select Diagnosis test </option>';
    $data = $.parseJSON(diagnosis);
    for (p = 0; $data.length > p; p++) {
        $options += '<option value="' + $data[p].diagnosis_id + '"> ' + $data[p].test_name + ',' + $data[p].center_name + ' </option>';
        //alert($data[p].drug_id) ; 
        console.log($data[p].drug_id);
    }

    $html = '<div class="well mt-1" style="display:flex;gap: 3px"> <input name="diag_test[]" class="form-control input-sm" placeholder="Enter Test Name">';
    $html += '<button onclick="$(this).parent().remove();countSerial();" type="button" style="margin-right:-6px; padding: 0 12px!important" class="float-start btn btn-sm border">X</button> </div>';

    $('#diagnosis').append($html);
    countSerial();
    $(".chosen-select").chosen({width: "100%%"});
}

function addDiagnosisDrug() {
    $options = '<option> Select Medicine </option>';
    $data = $.parseJSON(drug);
    for (p = 0; $data.length > p; p++) {
        $options += '<option value="' + $data[p].drug_id + '"> ' + $data[p].drug_name + ',' + $data[p].generic_name + ',' + $data[p].strength + ' </option>';
        //alert($data[p].drug_id) ; 
        console.log($data[p].drug_id);
    }
    $opt = '<option> 1+1+1 </option><option> 1+0+1 </option><option> 1+1+0 </option><option> 0+0+1 </option><option> 1+0+0 </option><option> 0+1+0 </option>';

    $html = '<div class="well mt-1" style="padding:6px;background:#fff"><input style="width: 275px;" name="drugs[]" class="form-control input-sm" placeholder="Medicince Name" required />';
    $html += '<button onclick="$(this).parent().fadeOut(500, function(){ $(this).remove();countDrugSerial()});" type="button" style="float:right;margin-top:-40px;margin-right:-16px; padding: 10px 20px" class=" btn btn-sm border">X</button>';
    $html += '<p> </p> <div class="row">';
    $html += '<div class="col-md-8"><select name="instruction[]" class="form-control input-sm chosen-select" >' + $opt + '</select></div><div class="col-md-4"><input style="height:25px;color:red;font-weight:bold" type="text" name="days[]" class="form-control input-sm" placeholder="Days"></div>';
    $html += '</div>';
    //$html += '<div class=""></div>';
    $html += '</div>';
    $('#drug').append($html);
    countDrugSerial();
    $(".chosen-select").chosen({width: "100%"});
}

function countSerial() {
    $('select[name="diag_test[]"]').each(function(e) {
        $(this).parent().find('label').html('Test # ' + (e + 1));
    });
}

function countDrugSerial() {
    $('select[name="drugs[]"]').each(function(e) {
        $(this).parent().find('label').html('Medicine # ' + (e + 1));
    });
}



$('.edit-drug').click(function() {

    param = $(this);
    drug_id = param.attr('drug_id');
    $btn = param;
    $btn_body = param.html();
    param.html('<i class="fa fa-spin fa-spinner"></i>');
    $.get('/user/drugs?action=edit&drug_id=' + drug_id).done(function(e) {
        $.fn.Modal({'body': e, 'backdrop': 'static', 'heading': 'Update:', 'borderRadius': '0px', 'size': 'medium', 'closeButton': false});
        $btn.html($btn_body);
    });
});


$('.delete-diagnosis').click(function() {

    var cnf = confirm('Are you sure ? want to delete this ?');
    if (cnf === false) {
        return false;
    }

    param = $(this);
    diagnosis_id = param.attr('diagnosis_id');
    $btn = param;
    $btn_body = param.html();
    param.html('<i class="fa fa-spin fa-spinner"></i>');
    $.get('/user/diagnosis?action=delete&daig_id=' + diagnosis_id).done(function(e) {
        // $.fn.Modal({'body': e, 'heading': 'Update:', 'borderRadius': '0px', 'size': 'medium', 'closeButton': false});
        if (e.success == 1) {
            param.parent().parent().hide('slow');
        }
        $btn.html($btn_body);
    });
});




function loadData(param) {
    var search_key = $('input[name="prescript_id"]').val();

    param = param;
    patient_id = param.attr('patient_id');
    $btn = param;
    $btn_body = param.html();
    param.html('<i class="fa fa-spin fa-spinner"></i>');

    $('input[type="text"]').val('');
    $.get('/userinfo/' + search_key).done(function(e) {
        $btn.html($btn_body);
        if (!$.hasData($.parseJSON(e))) {

            $('input[name="phone"]').val($.parseJSON(e).phone);
            $('input[name="name"]').val($.parseJSON(e).full_name);
            $('input[name="age"]').val($.parseJSON(e).age);


        }


    });

}


function loadAppointmentData(appointment_id) {
    var appointment_id = appointment_id;

    //param = param;
    ///patient_id = param.attr('patient_id');
    //$btn = param;
    //$btn_body = param.html();
    //param.html('<i class="fa fa-spin fa-spinner"></i>');


    $.get('/user/appointment-data?appointment_id=' + appointment_id).done(function(e) {
        //$btn.html($btn_body);
        $dt = $.parseJSON(e);
        if (!$.hasData($dt)) {
            //alert($dt.email);
            $('input[name="mobile"]').val($dt.phone);
            // $('input[name="name"]').val($dt.full_name);
            $('input[name="age"]').val($dt.age);

            $('input[name="appointment_id"]').val($dt.appointment_id);
            var gender = $dt.gender;
            if (gender) {
                $('#gender option[value=' + gender + ']').prop({selected: true});
            } else {
                $('#gender option[value=""]').prop({selected: true});
            }


        }


    });

}