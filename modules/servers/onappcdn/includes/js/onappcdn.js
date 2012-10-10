$(document).ready(function(){

// Assign User Roles selection values

    rolesObj = $('input[name="packageconfigoption[2]"]');
    rolesObj.hide();
    rolesSel = rolesObj.val();
    arrayRoles = rolesSel.split(',');

    rolesObj.after('<select name="roles" size="10" multiple="multiple" class="multiselect"></select>');

    rolesObj = $('select[name="roles"]');
    options_html="";
    for ( var option in userRoles ) {
        options_html +=
            '<option value="'+option+'">'+
            ''+ userRoles[option] +
            '</option>';
    }
    rolesObj.html(options_html);

    for (var i in arrayRoles) {
        rolesObj.find('option:[value='+arrayRoles[i]+']').attr('selected','selected');
    }

   rolesObj.change( function () {
       var input = Array();

       $(this).find("option:selected").each(function () {
           input.push( $(this).val() )
       })

       $('input[name="packageconfigoption[2]"]').val(input.join(','));
   })

// Assign Billing Plans, User Groups and Time Zones selection values

    var selections = new Array();
    selections[3] = billingPlans;
    selections[4] = userGroups;
    selections[5] = timeZones;

    for ( var selection in selections ) {
        var selectionObj = $('select[name="packageconfigoption['+selection+']"]');

        var options_html = '';
        var itemID       = selectionObj.val();

        var selected = "";
        for ( var option in selections[selection] ) {
            var selected = ( option+'' == selectionObj.val()+'') ? ' selected="selected"' : ''

            options_html +=
                '<option value="'+option+'"'+selected+'>'+
                ''+ selections[selection][option] +
                '</option>';
        }

        selectionObj.html(options_html);
    }

    userGroups = $('select[name="packageconfigoption[4]"]').parent().parent();
    
    userGroupTitle  = userGroups.children('td:nth-child(3)');
    userGroupSelect = userGroups.children('td:nth-child(4)');

    userGroupTitleHTML = '<td class="fieldlabel">'+userGroupTitle.html()+'</td>';
    userGroupSelectHTML = '<td class="fieldarea">'+userGroupSelect.html()+'</td>';
    userGroups.after('<tr>'+userGroupTitleHTML+userGroupSelectHTML+'</tr>');

    userGroupTitle.remove();
    userGroupSelect.remove();
    
    $('select').css('width' ,'300px');
});
