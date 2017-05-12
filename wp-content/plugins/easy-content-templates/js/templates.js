(function($){

    var template_loader = Object();
    template_loader.init = function(){
        $("#btn_ect_mtb_load").click(template_loader.load_clicked);
        $("#ddl_ect_mtb_template_id").change(template_loader.template_id_changed);
    };
    template_loader.disable_elements = function(){
        $("#ddl_ect_mtb_template_id, #chk_ect_load_title, #chk_ect_load_content, #chk_ect_load_excerpt, #btn_ect_mtb_load").attr("disabled", "disabled");
        $("#btn_ect_mtb_load").html("Loading Template...");
    };
    template_loader.enable_elements = function(){
        $("#ddl_ect_mtb_template_id, #chk_ect_load_title, #chk_ect_load_content, #chk_ect_load_excerpt, #btn_ect_mtb_load").removeAttr("disabled", "disabled");
        $("#btn_ect_mtb_load").html("Load Template");
    };
    template_loader.load_clicked = function(event){
        event.preventDefault();
        template_loader.disable_elements();
        var template_id = $("#ddl_ect_mtb_template_id").val();
        if(template_id == 0){
            template_loader.enable_elements();
            alert("Please select the template you want to load!");
        }else{
            $.ajax({
                "complete": template_loader.load_complete,
                "data": {
                    "action": "ect_get_template",
                    "template_id": template_id
                },
                "dataType": "json",
                "error": template_loader.load_error,
                "global": false,
                "success": template_loader.load_success,
                "timeout": 20000,
                "type": "POST",
                "url": ajaxurl
            });
        }
    };
    template_loader.template_id_changed = function(){
        var selected = $(this).find(":selected");
        if(selected.length > 0){
            $("#chk_ect_load_title").prop("checked", selected.attr("data-dlt") != undefined && selected.attr("data-dlt") == "1");
            $("#chk_ect_load_content").prop("checked", selected.attr("data-dlc") != undefined && selected.attr("data-dlc") == "1");
            $("#chk_ect_load_excerpt").prop("checked", selected.attr("data-dle") != undefined && selected.attr("data-dle") == "1");
        }
    };
    template_loader.load_complete = function(){
        template_loader.enable_elements();
    };
    template_loader.load_error = function(){
        alert("Something went wrong while the template was being loaded!");
    };
    template_loader.load_success = function(data){
        if(data.success){
            // load title
            if($("#chk_ect_load_title").is(":checked")){
                $("#title").val(unescape(data.data.title)).focus().blur();
            }

            // load content
            if($("#chk_ect_load_content").is(":checked")){
                var visual_mode = false;
                if(typeof(tinyMCE) != "undefined"){
                    if(tinyMCE.activeEditor != null){
                        if(!tinyMCE.activeEditor.isHidden()){
                            visual_mode = true;
                        }
                    }
                }
                if(visual_mode){
                    switchEditors.go("content", "html");
                    $("#content").val(unescape(data.data.content));
                    switchEditors.go("content", "tinymce");
                }else{
                    $("#content").val(unescape(data.data.content));
                }
            }

            // load excerpt
            if($("#chk_ect_load_excerpt").is(":checked")){
                $("#excerpt").val(unescape(data.data.excerpt));
            }
        }else{
            alert(data.message);
        }
    };

    $(document).ready(template_loader.init);
})(jQuery);
