$(document).ready(function(){

    var url = $("#form").attr("action");

    $.ajax({
        type: "POST",
        dataType: 'json',
        url: url,
        data: "loadActive=",
        success: function(response)
        {
            $.each(response.arrLoadActive, function(i,ind) {
                //alert(val);
                $(".checkSave").each(function() {
                    //arrSaveInd.push($(this).attr("value"));
                    if ($(this).attr("value") == ind)
                    {
                        $(this).attr('checked','checked');
                    }
                });
            });
        }
    })


    /*$("#controlForm_sourceId").change(function()
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
    });*/


    $("#viewForm_Save").click(function()
    {
        var arrSaveInd = [];
        var url = $("#form").attr("action");

        $(".checkSave").filter(':checked').each(function() {
            arrSaveInd.push($(this).attr("value"));
        });

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: url,
            data: "arrSaveInd=" + $.toJSON(arrSaveInd),
            success: function(response)
            {
                window.location.href = $(location).attr('href');
            }
        })
    });


    $("#viewForm_Delete").click(function()
    {
        var arrDeleteInd = [];
        var url = $("#form").attr("action");

        $(".checkDelete").filter(':checked').each(function() {
            arrDeleteInd.push($(this).attr("value"));
        });

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: url,
            data: "arrDeleteInd=" + $.toJSON(arrDeleteInd),
            success: function(response)
            {
                window.location.href = $(location).attr('href');
            }
        })


    });



})