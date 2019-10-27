<?php
header('content-type:text/html;charset=utf-8');
/**
 * 创建文件操作
 * @method create_file
 * @param  string      $filename 文件名
 * @return boolean                true|false
 */
function create_file($filename){
    //检测文件是否存在，不存在则创建
    if(file_exists($filename)){
        return false;
    }
    //检测目录是否存在，不存在则创建
    if(!file_exists(dirname($filename))){
        //创建目录，可以创建多级
        mkdir(dirname($filename),0777,true);
    }
    // if(touch($filename)){
    //   return true;
    // }
    // return false;
    if(file_put_contents($filename,'')!==false){
        return true;
    }
    return false;
}

/**
 * 删除文件操作
 * @method del_file
 * @param  string   $filename 文件名
 * @return boolean             true|false
 */
function del_file( $filename){
    //检测删除的文件是否存在,并且是否有权限操作
    if(!file_exists($filename)||!is_writable($filename)){
        return false;
    }
    if(unlink($filename)){
        return true;
    }
    return false;
}

/**
 * 拷贝文件操作
 * @method copy_file
 * @param  string    $filename 文件名
 * @param  string    $dest     指定目录
 * @return boolean              true|false
 */
function copy_file( $filename, $dest){
    //检测$dest是否是目标并且这个目录是否存在，不存在则创建
    if(!is_dir($dest)){
        mkdir($dest,0777,true);
    }
    $destName=$dest.DIRECTORY_SEPARATOR.basename($filename);
    //检测目标路径下是否存在同名文件
    if(file_exists($destName)){
        return false;
    }
    //拷贝文件
    if(copy($filename,$destName)){
        return true;
    }
    return false;
}

/**
 * 重命名操作
 * @method rename_file
 * @param  string      $oldName 原文件
 * @param  string      $newName 新文件名
 * @return boolean               true|false
 */
function rename_file( $oldName, $newName){
    //检测原文件并且存在
    if(!is_file($oldName)){
        return false;
    }
    //得到原文件所在的路径
    $path=dirname($oldName);
    $destName=$path.DIRECTORY_SEPARATOR.$newName;
    if(is_file($destName)){
        return false;
    }
    if(rename($oldName,$destName)){
        return true;
    }
    return false;
}

/**
 * 剪切文件操作
 * @method cut_file
 * @param  string   $filename 原文件
 * @param  string   $dest     目标路径
 * @return boolean             true|false
 */
function cut_file( $filename, $dest){
    if(!is_file($filename)){
        return false;
    }
    if(!is_dir($dest)){
        mkdir($dest,0777,true);
    }
    $destName=$dest.DIRECTORY_SEPARATOR.basename($filename);
    if(is_file($destName)){
        return false;
    }
    if(rename($filename,$destName)){
        return true;
    }
    return false;
}


/**
 * 返回文件信息
 * @method get_file_info
 * @param  string        $filename 文件名
 * @return mixed                  文件信息相关数组|false
 */
function get_file_info( $filename){
    if(!is_file($filename)||!is_readable($filename)){
        return false;
    }
    return [
        'atime'=>date("Y-m-d H:i:s",fileatime($filename)),
        'mtime'=>date("Y-m-d H:i:s",filemtime($filename)),
        'ctime'=>date("Y-m-d H:i:s",filectime($filename)),
        'size'=>trans_byte(filesize($filename)),
        'type'=>filetype($filename)
    ];
}

/**
 * 字节单位转换的函数
 * @method trans_byte
 * @param  int        $byte      字节
 * @param  integer    $precision 默认精度，保留小数点后2位
 * @return string                转换之后的字符串
 */
function trans_byte( $byte, $precision=2){
    $kb=1024;
    $mb=1024*$kb;
    $gb=1024*$mb;
    $tb=1024*$gb;
    if($byte<$kb){
        return $byte.'B';
    }elseif($byte<$mb){
        return round($byte/$kb,$precision).'KB';
    }elseif($byte<$gb){
        return round($byte/$mb,$precision).'MB';
    }elseif($byte<$tb){
        return round($byte/$gb,$precision).'GB';
    }else{
        return round($byte/$tb,$precision).'TB';
    }
}



/**
 * 读取文件内容，返回字符串
 * @method read_file
 * @param  string    $filename 文件名
 * @return mixed              文件内容|false
 */
function read_file( $filename){
    //检测是否是一个文件并且文件已存在
    if(is_file($filename) && is_readable($filename)){
        return file_get_contents($filename);
    }
    return false;
}


/**
 * 读取文件中的内容到数组中
 * @method read_file_array
 * @param  string          $filename         文件名
 * @param  boolean         $skip_empty_lines 是否过滤空行
 * @return mixed                            array|false
 */
function read_file_array( $filename, $skip_empty_lines=false){
    if(is_file($filename)&&is_readable($filename)){
        if($skip_empty_lines){
            return file($filename,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        }else{
            return file($filename);
        }
    }
    return false;
}


/**
 * 向文件中写入内容覆盖
 * @method write_file
 * @param  string     $filename 文件名
 * @param  mixed      $data     数据，数组和对象需要处理
 * @return boolean               true|false
 */
function write_file_cover( $filename,$data){
    $dirname=dirname($filename);
    //检测目标路径是否存在
    if(!file_exists($dirname)){
        mkdir($dirname,0777,true);
    }
    //判断内容是否是数组或者对象
    if(is_array($data)||is_object($data)){
        //序列化数据
        $data=serialize($data);
    }
    //向文件中写入内容
    if(file_put_contents($filename,$data)!==false){
        return true;
    }else{
        return false;
    }
}

/**
 * 向文件中写入内容，追加
 * @method write_file1
 * @param  string      $filename  文件名
 * @param  mixed       $data      数据
 * @param  boolean     $clearFlag 是否清空文件
 * @return boolean                 true|false
 */
function write_file_add( $filename,$data, $clearFlag=false){
    $dirname=dirname($filename);
    //检测目标路径是否存在
    if(!file_exists($dirname)){
        mkdir($dirname,0777,true);
    }
    //检测文件是否存在并且可读
    if(is_file($filename)&&is_readable($filename)){
        //读取文件内容，之后和新写入的内容拼装到一起
        if(filesize($filename)>0){
            $srcData=file_get_contents($filename);
        }
    }

    //判断内容是否是数组或者对象
    if(is_array($data)||is_object($data)){
        //序列化数据
        $data=serialize($data);
    }
    //拼装到一起
    $data=$srcData.$data;
    //向文件中写入内容
    if(file_put_contents($filename,$data)!==false){
        return true;
    }else{
        return false;
    }
}

/**
 * 截断文件到指定大小
 * @method truncate_file
 * @param  string        $filename 文件名
 * @param  int           $length   长度
 * @return boolean                 true|false
 */
function truncate_file( $filename, $length){
    //检测是否是文件
    if(is_file($filename)&&is_writable($filename)){
        $handle=fopen($filename,'r+');
        $length=$length<0?0:$length;
        ftruncate($handle,$length);
        fclose($handle);
        return true;
    }
    return false;
}


/**
 * 下载小文件
 * @method down_small_file
 * @param  string    $filename     文件名
 * @param  array     $allowDownExt 允许下载的文件类型
 * @return void
 */
function down_small_file( $filename,array $allowDownExt=array('jpeg','jpg','png','gif','txt','html','php','rar','zip')){
    //检测下载文件是否存在，并且可读
    if(!is_file($filename)||!is_readable($filename)){
        return false;
    }
    //检测文件类型是否允许下载
    $ext=strtolower(pathinfo($filename,PATHINFO_EXTENSION));
    if(!in_array($ext,$allowDownExt)){
        return false;
    }
    $file = fopen ( $filename, "rb" );
    //通过header()发送头信息
    //告诉浏览器输出的是字节流
    header('Content-Type:application/octet-stream');

    //告诉浏览器返回的文件大小是按照字节进行计算的
    header('Accept-Ranges: bytes');

    //告诉浏览器返回的文件大小
    header('Accept-Length: '.filesize($filename));

    //告诉浏览器文件作为附件处理，告诉浏览器最终下载完的文件名称
    header('Content-Disposition: attachment;filename='.basename($filename));

    //读取文件中的内容
    echo fread ( $file, filesize ( $filename ) );
    fclose ( $file );
    exit ();

}

/**
 * 下载大文件
 * @method down_big_file
 * @param  string    $filename     文件名
 * @param  array     $allowDownExt 允许下载的文件类型
 * @return void
 */
function down_big_file( $filename,array $allowDownExt=array('jpeg','jpg','png','gif','txt','html','php','rar','zip')){
    //检测下载文件是否存在，并且可读
    if(!is_file($filename)||!is_readable($filename)){
        return false;
    }
    //检测文件类型是否允许下载
    $ext=strtolower(pathinfo($filename,PATHINFO_EXTENSION));
    if(!in_array($ext,$allowDownExt)){
        return false;
    }
    //通过header()发送头信息

    //告诉浏览器输出的是字节流
    header('Content-Type:application/octet-stream');

    //告诉浏览器返回的文件大小是按照字节进行计算的
    header('Accept-Ranges: bytes');

    $filesize=filesize($filename);
    //告诉浏览器返回的文件大小
    header('Accept-Length: '.$filesize);

    //告诉浏览器文件作为附件处理，告诉浏览器最终下载完的文件名称
    header('Content-Disposition: attachment;filename=king_'.basename($filename));

    //读取文件中的内容

    //规定每次读取文件的字节数为1024字节，直接输出数据
    $read_buffer=1024;
    $sum_buffer=0;
    $handle=fopen($filename,'rb');
    while(!feof($handle) && $sum_buffer<$filesize){
        echo fread($handle,$read_buffer);
        $sum_buffer+=$read_buffer;
    }
    fclose($handle);
    exit;
}

/**
 * 单文件上传
 * @method upload_file
 * @param  array       $fileInfo   上传文件的信息，是一个数组
 * @param  string      $uploadPath 文件上传默认路径
 * @param  boolean     $imageFlag  是否检测真实图片
 * @param  array       $allowExt   允许上传的文件类型
 * @return mixed                  成功返回文件最终保存路径及名称，失败返回false
 */
function upload_file(array $fileInfo, $uploadPath='./uploads', $imageFlag=true,array $allowExt=array('jpeg','jpg','png','gif'), $maxSize=4194304){
    define('UPLOAD_ERRS',[
        'upload_max_filesize'=>'超过了PHP配置文件中upload_max_filesize选项的值',
        'form_max_size'=>'超过了表单MAX_FILE_SIZE选项的值',
        'upload_file_partial'=>'文件部分被上传',
        'no_upload_file_select'=>'没有选择上传文件',
        'upload_system_error'=>'系统错误',
        'no_allow_ext'=>'非法文件类型',
        'exceed_max_size'=>'超出允许上传的最大值',
        'not_true_image'=>'文件不是真实图片',
        'not_http_post'=>'文件不是通过HTTP POST方式上传上来的',
        'move_error'=>'文件移动失败'
    ]);

    //检测是否上传是否有错误
    if($fileInfo['error']===UPLOAD_ERR_OK){
        //检测上传文件类型
        $ext=strtolower(pathinfo($fileInfo['name'],PATHINFO_EXTENSION));
        if(!in_array($ext,$allowExt)){
            return json_encode(['result' => false, 'msg' => UPLOAD_ERRS['no_allow_ext']]);
        }
        //检测上传文件大小是否符合规范
        if($fileInfo['size']>$maxSize){
            return json_encode(['result' => false, 'msg' => UPLOAD_ERRS['exceed_max_size']]);
        }
        //检测是否是真实图片
        if($imageFlag){
            if(@!getimagesize($fileInfo['tmp_name'])){
                return json_encode(['result' => false, 'msg' => UPLOAD_ERRS['not_true_image']]);
            }
        }
        //检测文件是否通过HTTP POST方式上传上来的
        if(!is_uploaded_file($fileInfo['tmp_name'])){
            return json_encode(['result' => false, 'msg' => UPLOAD_ERRS['not_http_post']]);
        }
        //检测目标目录是否存在，不存在则创建
        if(!is_dir($uploadPath)){
            mkdir($uploadPath,0777,true);
        }
        //生成唯一文件名，防止重名产生覆盖
        $uniName=md5(uniqid(microtime(true),true)).'.'.$ext;
        $dest=$uploadPath.DIRECTORY_SEPARATOR.$uniName;

        //移动文件
        if(@!move_uploaded_file($fileInfo['tmp_name'],$dest)){
            return json_encode(['result' => false, 'msg' => UPLOAD_ERRS['move_error']]);
        }
        return json_encode(['result' => true, 'msg' => $uniName]);

    }else{
        switch($fileInfo['error']){
            case 1:
                // $mes='超过了PHP配置文件中upload_max_filesize选项的值';
                $mes=UPLOAD_ERRS['upload_max_filesize'];
                break;
            case 2:
                $mes=UPLOAD_ERRS['form_max_size'];
                break;
            case 3:
                $mes=UPLAOD_ERRS['upload_file_partial'];
                break;
            case 4:
                $mes=UPLOAD_ERRS['no_upload_file_select'];
                break;
            case 6:
            case 7:
            case 8:
                $mes=UPLAOD_ERRS['upload_system_error'];
                break;
        }
        return json_encode(['result' => false, 'msg' => $mes]);
    }
}

/**
 * 压缩单个文件
 * @method zip_file
 * @param  string   $filename 文件名
 * @return boolean             true|false
 */
function zip_file( $filename){
    if(!is_file($filename)){
        return false;
    }
    $zip=new ZipArchive();
    $zipName=basename($filename).'.zip';
    //打开指定压缩包，不存在则创建，存在则覆盖
    if($zip->open($zipName,ZipArchive::CREATE|ZipArchive::OVERWRITE)){
        //将文件添加到压缩包中
        if($zip->addFile($filename)){
            $zip->close();
            @unlink($filename);
            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }
}

/**
 * 多文件压缩
 * @method zip_files
 * @param  string    $zipName 压缩包的名称，.zip结尾
 * @param  string     $files   需要压缩文件名，可以是多个
 * @return boolean             true|false
 */
function zip_files( $zipName,...$files){
    //检测压缩包名称是否正确
    $zipExt=strtolower(pathinfo($zipName,PATHINFO_EXTENSION));
    if('zip'!==$zipExt){
        return false;
    }
    $zip=new ZipArchive();
    if($zip->open($zipName,ZipArchive::CREATE|ZipArchive::OVERWRITE)){
        foreach($files as $file){
            if(is_file($file)){
                $zip->addFile($file);
            }
        }
        $zip->close();
        return true;
    }else{
        return false;
    }
}

/**
 * 解压缩
 * @method unzip_file
 * @param  string     $zipName 压缩包名称
 * @param  string     $dest    解压到指定目录
 * @return boolean              true|false
 */
function unzip_file( $zipName, $dest){
    //检测要解压压缩包是否存在
    if(!is_file($zipName)){
        return false;
    }
    //检测目标路径是否存在
    if(!is_dir($dest)){
        mkdir($dest,0777,true);
    }
    $zip=new ZipArchive();
    if($zip->open($zipName)){
        $zip->extractTo($dest);
        $zip->close();
        return true;
    }else{
        return false;
    }
}
/**
 * 检测目录是否为空
 * @method check_empty_dir
 * @param  string          $path 目录名
 * @return boolean         true|false
 */
function check_empty_dir( $path){
    //检测目录是否存在，存在则打开
    if(!is_dir($path)){
        return false;
    }
    //打开指定目录
    $handle=opendir($path);
    //读取
    while(($item=@readdir($handle))!==false){
        //去掉.和..操作
        if($item!='.'&&$item!='..'){
            return false;
        }
    }
    //关闭句柄
    closedir($handle);
    return true;
}


/**
 * 读取当前目录下的文件和目录，不递归
 * @param $path
 * @return bool|array
 */
function read_directory($path) {
    if(!is_dir($path)) {
        return false;
    }
    $handle = opendir($path);
    $res = [];
    while(($item=@readdir($handle))!==false) {
        if ($item != '.' && $item != '..') {
            $pathName = $path . DIRECTORY_SEPARATOR . $item;
            if (is_file($pathName)) {
                $res['file'][] = $pathName;
            }else if(is_dir($pathName)) {
                $res['dir'][] = $pathName;
            }
        }
    }
    closedir($handle);
    return $res;
}

/**
 * 读取目录下的所有文件
 * @method read_directory_string
 * @param  string         $path 目录名称
 * @return bool            直接输出目录下的所有文件及子目录
 */
function read_directory_string( $path){
    if(!is_dir($path)){
        return false;
    }
    $handle=opendir($path);
    while(($item=@readdir($handle))!==false){
        if($item!='.'&&$item!='..'){
            $pathName=$path.DIRECTORY_SEPARATOR.$item;
            if(is_file($pathName)){
                echo '文件:',$item,'<br/>';
            }else{
                echo '目录:',$item,'<br/>';
                $func=__FUNCTION__;
                $func($pathName);
            }
        }
    }
    closedir($handle);
}

/**
 * 遍历目录下所有内容返回数组
 * @method read_directory_array
 * @param  string          $path 目录名称
 * @return mixed            false|array
 */
function read_directory_array( $path){
    if(!is_dir($path)){
        return false;
    }
    $handle=opendir($path);
    $arr = [];
    while(($item=@readdir($handle))!==false){
        if($item!='.'&&$item!='..'){
            $pathName=$path.DIRECTORY_SEPARATOR.$item;
            if(is_file($pathName)){
                $arr['file'][]=$pathName;
            }elseif(is_dir($pathName)){
                $arr['dir'][]=$pathName;
                $func=__FUNCTION__;
                $func($pathName);
            }
        }
    }
    closedir($handle);
    return $arr;
}

/**
 * 读取目录中的所有文件，递归获得目录下所有文件，包括子目录的文件
 * @method get_all_files
 * @param  string        $path 目录名称
 * @return mixed              false|array
 */
function get_all_files( $path){
    if(!is_dir($path)){
        return false;
    }
    if($handle=opendir($path)){
        $res=[];
        while(($item=readdir($handle))!==false){
            if($item!='.'&&$item!='..'){
                $pathName=$path.DIRECTORY_SEPARATOR.$item;
                is_dir($pathName)?$res[$pathName]=get_all_files($pathName):$res[]=$pathName;
            }
        }
        closedir($handle);
        return $res;
    }else{
        return false;
    }
}

/**
 * 得到目录大小
 * @method get_dir_size
 * @param  string       $path 目录名称
 * @return mixed             false|int
 */
function get_dir_size( $path){
    if(!is_dir($path)){
        return false;
    }
    $sum=0;
    $handle=opendir($path);
    while(($item=readdir($handle))!==false){
        if($item!='.'&&$item!='..'){
            $pathName=$path.DIRECTORY_SEPARATOR.$item;
            if(is_file($pathName)){
                $sum+=filesize($pathName);
            }else{
                $func=__FUNCTION__;
                $func($pathName);
            }
        }
    }
    closedir($handle);
    return $sum;
}

/**
 * 重命名目录
 * @method rename_dir
 * @param  string     $oldName 原目录
 * @param  string     $newName 新目录
 * @return boolean              true|false
 */
function rename_dir( $oldName, $newName){
    //检测原文件是否存在,或者当前目录下存在同名目录
    $dest=dirname($oldName).DIRECTORY_SEPARATOR.$newName;
    if(!is_dir($oldName)|| file_exists($dest)){
        return false;
    }
    if(rename($oldName,$dest)){
        return true;
    }
    return false;
}

/**
 * 剪切目录
 * @method cut_dir
 * @param  string  $src 原目录
 * @param  string  $dst 新目录位置
 * @return boolean       true|false
 */
function cut_dir( $src, $dst){
    //检测原目录是否存在，不存在返回false
    if(!is_dir($src)){
        return false;
    }
    //检测目录路径是否存在，不存在则创建
    if(!is_dir($dst)){
        mkdir($dst,755,true);
    }
    //检测目录路径下是否存在同名目录
    $dest=$dst.DIRECTORY_SEPARATOR.basename($src);
    if(is_dir($dest)){
        return false;
    }
    //剪切
    if(rename($src,$dest)){
        return true;
    }
    return false;
}

/**
 * 拷贝目录操作
 * @method copy_dir
 * @param  string   $src 原目录
 * @param  string   $dst 目标路径
 * @return boolean        true|false
 */
function copy_dir( $src, $dst){
    //检测原目录是否存在
    if(!is_dir($src)){
        return false;
    }
    //检测目标目录是否存在，不存在则创建
    if(!is_dir($dst)){
        mkdir($dst,755,true);
    }
    //检测目标目录下是否存在同名文件
    $dest=$dst.DIRECTORY_SEPARATOR.basename($src);
    if(is_dir($dest)){
        return false;
    }
    $handle=opendir($src);
    while(($item=@readdir($handle))!==false){
        if($item!='.'&&$item!='..'){
            if(is_file($src.DIRECTORY_SEPARATOR.$item)){
                copy($src.DIRECTORY_SEPARATOR.$item,$dst.DIRECTORY_SEPARATOR.$item);
            }
            if(is_dir($src.DIRECTORY_SEPARATOR.$item)){
                $func=__FUNCTION__;
                $func($src.DIRECTORY_SEPARATOR.$item,$dst.DIRECTORY_SEPARATOR.$item);
            }
        }
    }
    closedir($handle);
    return true;
}

/**
 * 删除非空目录
 * @method del_dir
 * @param  string  $path 目录名称
 * @return boolean        true|false
 */
function del_dir( $path){
    //检测目录是否存在
    if(!is_dir($path)){
        return false;
    }
    $handle=opendir($path);
    while(($item=@readdir($handle))!==false){
        if($item!='.'&&$item!='..'){
            $pathName=$path.DIRECTORY_SEPARATOR.$item;
            if(is_file($pathName)){
                @unlink($pathName);
            }else{
                $func=__FUNCTION__;
                $func($pathName);
            }
        }
    }
    closedir($handle);
    rmdir($path);
    return true;
}




