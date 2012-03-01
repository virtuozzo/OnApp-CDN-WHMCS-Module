$(document).ready(function(){
    $("select[name='servergroup']").change( function () {
        form = $("form[name$='packagefrm']");
        form.submit();
    });

    var serverSelect = $("select[name='packageconfigoption[1]']");

    var serverId     = serverSelect.val();
    var servers_html = '';

    for ( var option in servers ) {
        var selected = ( option == serverId ) ? ' selected="selected"' : ''

        servers_html +=
            '<option value="'+option+'"'+selected+'>'+
            ''+ servers[option] +
            '</option>';
    }

    serverSelect.html(servers_html);

    server = $('select[name="packageconfigoption[1]"]').parent().parent();
    serverTitle  = server.children('td:nth-child(1)');
    serverSelect = server.children('td:nth-child(2)');

    servergroupObj = $('select[name="servergroup"]').parent().parent();
    serverTitleHTML  = '<td class="fieldlabel">'+serverTitle.html()+'</td>';
    serverSelectHTML = '<td class="fieldarea">'+serverSelect.html()+'</td>'
    servergroupObj.after('<tr>'+serverTitleHTML+serverSelectHTML+'</tr>');

    serverTitle.remove();
    serverSelect.remove();

    $('select[name="packageconfigoption[1]"]').change( function () {
        form = $("form[name$='packagefrm']");
        form.submit();
    });

    $('select').css('width' ,'300px');
});
