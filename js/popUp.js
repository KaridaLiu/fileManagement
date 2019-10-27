// 重命名弹框
$('.rename').on('click', function () {
    // 接收path信息
    var path = $(this).attr('data-path');
    var url = $(this).attr('data-url');
    var oldname = $(this).attr('data-oldname');
    // 弹框让用户输入新名字
    layer.prompt({title: '重命名', formType: 0, value: oldname}, function(text, index){
        layer.close(index);
        var newname = text;
        window.location.href=url+"&action=rename&path="+path+"&newname="+newname;
    });
});

// 删除确认弹框
$('.delete').on('click', function () {
    // 接收path信息
    var path = $(this).attr('data-path');
    var url = $(this).attr('data-url');
    // 提示是否确认删除
    layer.confirm('您确定要删除这个文件吗？', {
        btn: ['确定','取消'] //按钮
    }, function(){
        window.location.href=url+"&action=del&path="+path;
    });
});

// 新建目录弹框
$('#createDir').on('click', function () {
    var url = $(this).attr('data-url');
    // 弹框让用户输入新建目录名字
    layer.prompt({title: '新建目录', formType: 0}, function(text, index){
        layer.close(index);
        var dirname = text;
        window.location.href=url+"&action=createdir&dirname="+dirname;
    });
});

// 新建文件弹框
$('#createFile').on('click', function () {
    var url = $(this).attr('data-url');
    // 弹框让用户输入新建目录名字
    layer.prompt({title: '新建文件', formType: 0}, function(text, index){
        layer.close(index);
        var filename = text;
        window.location.href=url+"&action=createfile&filename="+filename;
    });
});

// 阻止浏览器返回按钮
window.history.pushState(null, null, document.URL);
window.addEventListener('popstate', function () {
    window.history.pushState(null, null, document.URL);
});