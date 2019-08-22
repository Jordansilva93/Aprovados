$().ready(function () {
    //--------------------------------------------------------------------------
    $("#extraiInformacao").click(function () {
        var form = new FormData();
        form.append('arquivo', $('#fileUpload')[0].files[0]);
        //----------------------------------------------------------------------------------------------------------------------------------------
        $.ajax({
            type: 'POST',
            url: 'Home/extrair',
            data: form,
            dataType: 'json',
            contentType: false,
            processData: false,
            success: function (data) {
                if (data.status == true) {
                    $("#texto").text(data.texto);
                } else {
                    $.notify(data.mensagem, data.notify);
                }
            }, error: function (data) {
                $.notify('Erro no Envio', "warning");
            }
        });
    });
    //--------------------------------------------------------------------------
    $("#fileUpload").change(function () {
        var x = document.getElementById("fileUpload").files;
        if (x[0].type == "application/pdf") {
            var form = new FormData();
            form.append('arquivo', $('#fileUpload')[0].files[0]);
            //----------------------------------------------------------------------------------------------------------------------------------------
            $.ajax({
                type: 'POST',
                url: 'Home/extrair',
                data: form,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.status !== true) {
                        $.notify(data.mensagem, data.notify);
                    }
                }, error: function (data) {
                    $.notify('Erro no Envio', "warning");
                }
            });
        } else {
            $.notify("Arquivo não suportado", "warning");
            $("#fileUpload").val("");
        }
    });
});