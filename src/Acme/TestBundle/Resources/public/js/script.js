$(document).ready(function(){

    $("#controlForm_sourceId").change(function()
    {
        var id = $(this).select().val();

        if (id != '')
        {
            var url = $("#form").attr("action");

            $.ajax({
                type: "POST",
                url: url,
                data: "editId=" + id,
                success: function(response)
                {
                    if (response != null)
                    {
                        $("#controlForm_fieldName").val(response.name);
                        $("#controlForm_fieldUrl").val(response.url);
                    }
                }
            })
        }
        else
        {
            $("#controlForm_fieldName").val("");
            $("#controlForm_fieldUrl").val("");
        }
    });

    $("#btnView").click(function()
    {
        var arrInd = new Array();

        $(':checkbox').filter(':checked').each(function() {
            //alert($(this).attr("ind"));
            arrInd.push($(this).attr("ind"));
        });

    });

})