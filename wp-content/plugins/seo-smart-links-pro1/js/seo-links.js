jQuery(function() {
    var theTable = jQuery('table#mySortable');

    jQuery("#filter").keyup(function(e) {

        if (e.keyCode == 13)
            jQuery.uiTableFilter(theTable, this.value);

        jQuery("#mySortable tbody tr:eq(0)").hide();
       
        });
               
});

jQuery(document).ready(function() {

	 jQuery("#datatable")
	  .tablesorter({ } )
	  .tablesorterPager({container: jQuery("#pager")}); 
  
  

    refresh_me();

    jQuery("input").bind("keypress", function(e) {
        if (e.keyCode == 13)
            return false;
    });

    jQuery("a#edit_all").bind("click", function() {
        jQuery("#customkey").toggle(0);

       
        });

    jQuery("input").bind("keypress", function(e) {
        if (e.keyCode == 13)
            return false;
    });
    jQuery("input[name=addword]").keypress(function(e) {
        if (e.keyCode == 13) {

            jQuery("#saveadd").click();
        }
        if (e.keyCode == 27) {

            jQuery("#canceladd").click();
        }
    });

    jQuery("input[name=addurl]").keypress(function(e) {
        if (e.keyCode == 13) {

            jQuery("#saveadd").click();
        }
        if (e.keyCode == 27) {

            jQuery("#canceladd").click();
        }
    });

});

function put_data() {

    var tmp = '';

    jQuery('table#mySortable tbody tr').each(function(i) {
        //iterate tr
        if (i) {
            jQuery(this).find('td').each(function(i) {
                //iterate td
                elem = jQuery(this);
                if (elem.text())
                if (elem.is(".word") || elem.is(".url") || elem.is(".redir")) {

                    if (i == 1 || i == 2)
                        tmp += '|';//SEOSmartOptions.separator;

                    tmp += elem.text();

                }
                if (i==2)
                	tmp+="\n";
            });
        }

    });

    document.getElementById('customkey').value = tmp;

}

function refresh_me() {

    jQuery("table#mySortable").tableDnD({
        onDrop: function(table, row) {
            //jQuery("table#mySortable tr").removeClass('odd');
            //jQuery("table#mySortable tr:visible:odd").addClass('odd');
            put_data();

        }
    });
    

    jQuery(".editlink").click(function() {

        jQuery(this).hide();
        var datapos = jQuery(this).parent().parent().prevAll().length;
        var editpos = datapos + 1;

       
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td a.savelink").show();
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td a.cancellink").show();
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td a.removelink").hide();

        var word = jQuery("#mySortable tbody tr:eq(" + datapos + ") td.word").text();
        var word_input = '<span name="editword" style="display: none;">' + word + '</span><input type="text" name="editword" value="' + word + '" />';
        var url = jQuery("#mySortable tbody tr:eq(" + datapos + ") td.url").text();
        var url_input = '<span name="editlink" style="display: none;">' + url + '</span><input type="text" name="editlink" value="' + url + '" />';
        var redir = jQuery("#mySortable tbody tr:eq(" + datapos + ") td.redir").text();
        var redir_input = '<span name="editredir" style="display: none;">' + redir + '</span><input type="text" name="editredir" value="' + redir + '" />';

        jQuery("#mySortable tbody tr:eq(" + datapos + ") td.word").html(word_input);
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td.url").html(url_input);
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td.redir").html(redir_input);

        jQuery("input").bind("keypress", function(e) {
            if (e.keyCode == 13)
                return false;
        });
        jQuery("input[name=editword]").keypress(function(e) {
            if (e.keyCode == 13) {

                jQuery("#mySortable tbody tr:eq(" + datapos + ") td a.savelink").click();
            }
            if (e.keyCode == 27) {

                jQuery("#mySortable tbody tr:eq(" + datapos + ") td a.cancellink").click();
            }
        });

        jQuery("input[name=editlink]").keypress(function(e) {
            if (e.keyCode == 13) {

                jQuery("#mySortable tbody tr:eq(" + datapos + ") td a.savelink").click();
            }
            if (e.keyCode == 27) {

                jQuery("#mySortable tbody tr:eq(" + datapos + ") td a.cancellink").click();
            }
        });

    });

    jQuery(".cancellink").click(function() {
        var datapos = jQuery(this).parent().parent().prevAll().length;

        var editword = jQuery("#mySortable tbody tr:eq(" + datapos + ") td span[name=editword]").text();
        var editlink = jQuery("#mySortable tbody tr:eq(" + datapos + ") td span[name=editlink]").text();
        var editredir = jQuery("#mySortable tbody tr:eq(" + datapos + ") td span[name=editredir]").text();

        jQuery("#mySortable tbody tr:eq(" + datapos + ") td.word").html(editword);
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td.url").html(editlink);
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td.redir").html(editredir);
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td a.savelink").hide();
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td a.cancellink").hide();
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td a.removelink").show();
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td a.editlink").show();

        put_data();
    });

    jQuery(".removelink").click(function() {
        var datapos = jQuery(this).parent().parent().prevAll().length;

        jQuery("#mySortable tbody tr:eq(" + datapos + ")").remove();
     
        jQuery("#updatemessage").text("Keyword removed").fadeOut(2000, function() {
            jQuery(this).css('display', 'block').text("");
        });
        put_data();

       
        });

    jQuery(".savelink").click(function() {
        var datapos = jQuery(this).parent().parent().prevAll().length;

        var editword = jQuery("#mySortable tbody tr:eq(" + datapos + ") td input[name=editword]").val();
        var editlink = jQuery("#mySortable tbody tr:eq(" + datapos + ") td input[name=editlink]").val();
        var editredir = jQuery("#mySortable tbody tr:eq(" + datapos + ") td input[name=editredir]").val();

        //alert(editword);
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td.word").text(editword);
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td.url").text(editlink);
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td.redir").text(editredir);
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td a.savelink").hide();
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td a.cancellink").hide();
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td a.removelink").show();
        jQuery("#mySortable tbody tr:eq(" + datapos + ") td a.editlink").show();

        jQuery("#updatemessage").text("Keyword updated").fadeOut(2000, function() {
            jQuery(this).css('display', 'block').text("");
        });

        put_data();

      
        });
   
    jQuery("#addrowbutton").unbind('click').click(function() {
        jQuery("#addrow").show();
    });

    jQuery("#canceladd").click(function() {
        jQuery("#addrow").hide();
    });

    jQuery("#saveadd").click(function() {

        jQuery("#mySortable tbody tr:eq(0)").clone(true).insertAfter("#mySortable tbody tr:eq(0)");

        var addword = jQuery("#addrow input[name=addword]").val();
        var addurl = jQuery("#addrow input[name=addurl]").val();
        var addredir = jQuery("#addrow input[name=addredir]").val();

        jQuery("#mySortable tbody tr:eq(1)").show();
        jQuery("#mySortable tbody tr:eq(1) .word").text(addword);
        jQuery("#mySortable tbody tr:eq(1) .url").text(addurl);
        jQuery("#mySortable tbody tr:eq(1) .redir").text(addredir);

        jQuery("#addrow input[name=addword]").val("");
        jQuery("#addrow input[name=addurl]").val("http://");
        jQuery("#addrow input[name=addredir]").val("");

        jQuery("#addrow").hide();

        jQuery("#updatemessage").text("Keyword added").fadeOut(2000, function() {
            jQuery(this).css('display', 'block').text("");
        });

        // sort on the first column 
        jQuery("#mySortable").trigger("applyWidgets", "zebra");

        put_data();

    });
   
}
//end refresh me
