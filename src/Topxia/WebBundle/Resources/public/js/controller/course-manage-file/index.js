define(function(require, exports, module) {

    exports.run = function() {
        require('../course-manage/header').run();

        var $panel = $('#file-manage-panel');
	    require('../../util/batch-select')($panel);
	    require('../../util/batch-delete')($panel);

        $panel.on('click', '.convert-file-btn', function(){
            $.post($(this).data('url'), function(response) {
                if (response.status == 'error') {
                    alert(response.message);
                } else {
                    window.location.reload();
                }
            }, 'json').fail(function(){
                alert('文件转换提交失败，请重试！');
            });
        });

        $('.tip').tooltip();

        $("#modal").modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });
        
        $("button", ".panel-heading").on('click',function(){
            var url = $(this).data("url");
            $("#modal").html('');
            $("#modal").modal('show');
            $.get(url, function(html){
                $("#modal").html(html);
            });
        })

        $("[rel='tooltip']").tooltip();

        asyncLoadFiles();
    };

    function asyncLoadFiles()
    {
        var fileIds = new Array();
        $('tbody [type=checkbox]').each(function(){
            if(!isNaN($(this).val())){
                fileIds.push($(this).val());
            }
        });

        if(fileIds.length==0){
          return ;
        }

        $.post("/course/manage/file/status",{'ids':fileIds.join(",")},function(data){
            if(!data||data.length==0){
                return ;
            }

            for(var i=0; i<data.length; i++){
                var file=data[i];
                if($.inArray(file.type, ['video','ppt','document'])>-1){
                    if(file.convertStatus=='waiting'||file.convertStatus=='doing'){
                        $("#upload-file-tr-"+file.id).find('a:first ~ br:first').after("<span class='text-warning text-sm'>正在文件格式转换</span><br/>");
                    }else if(file.convertStatus=='error'){
                        $("#upload-file-tr-"+file.id).find('a:first ~ br:first').after("<span class='text-danger text-sm'>文件格式转换失败</span><br/>");
                    }else if(file.convertStatus=='none'){
                        $("#upload-file-tr-"+file.id).find('a:first ~ br:last').after("<span class='label label-default tip'>未转码</span>");
                    }else if(file.convertStatus=='success'){
                        $("#upload-file-tr-"+file.id).find('a:first ~ br:last').after("<span class='label label-success tip'>已转码</span>");
                    }

                }
                if(file.type == 'video' && file.metas2) {
                    if(file.metas2.hd) {
                        $("#upload-file-tr-"+file.id).find('a:first ~ br:first').after('<span class="label label-info tip">超清</span>');
                    } else if(file.metas2.md){
                        $("#upload-file-tr-"+file.id).find('a:first ~ br:first').after('<span class="label label-info tip">高清</span>');
                    } else if(file.metas2.sd) {
                        $("#upload-file-tr-"+file.id).find('a:first ~ br:first').after('<span class="label label-info tip">标清</span>');
                    }
                }
            }
        });
    }


});