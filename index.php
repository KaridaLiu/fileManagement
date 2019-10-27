<?php
include 'lib/file.func.php';
include 'lib/tools.func.php';
header('content-type:text/html;charset=utf-8');
date_default_timezone_set('PRC');
if((isset($_GET['action'])&&$_GET['action'] != 'download') || !isset($_GET['action'])) {
    echo "<script src='js/jquery.js'></script>";
    echo "<script src='js/layer/layer.js'></script>";
}
session_start();
if(!isset($_SESSION['username'])) {
    header('location: login.php');
}
// 如果没有任何参数，则显示space目录下的文件
$dir = isset($_GET['dir']) ? $_GET['dir'] : 'space';
$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.'dir='.$dir;
// 复制
if(isset($_GET['action'])) {
    //有动作
    if($_GET['action'] == 'copy') {
        // 复制操作，把文件路径存放到session的剪贴板中
        $_SESSION['clipboard'] = $_GET['path'];
        $_SESSION['action'] = $_GET['action'];
        $msg = '已复制到剪切板';
    }else if($_GET['action'] == 'cut'){
        // 剪切操作，把文件路径存放到session的剪贴板中
        $_SESSION['clipboard'] = $_GET['path'];
        $_SESSION['action'] = $_GET['action'];
        $msg = '已剪切到剪切板';
    }else if($_GET['action'] == 'rename'){
        // 重命名操作
        $path = trim($_GET['path']);
        $newname = strip_tags($_GET['newname']);
        // 判断是文件还是目录
        if(is_file($path)) {
            // 文件重命名
            $res = rename_file($path, $newname);
        }else if(is_dir($path)){
            // 目录重命名
            $res = rename_dir($path, $newname);
        }
        $msg = '重命名';
    }else if($_GET['action'] == 'paste'){
        // 粘贴操作，执行session中相对应操作，把路径粘贴到当前目录
        if(isset($_SESSION['action'])) {

            if($_SESSION['action'] == 'copy') {
                if(is_file($_SESSION['clipboard'])) {
                    // 复制文件
                    $res = copy_file($_SESSION['clipboard'], $dir);
                }else if(is_dir($_SESSION['clipboard'])) {
                    // 复制目录
                    $res = copy_dir($_SESSION['clipboard'], $dir);
                }
                $msg = '复制';
            }else if($_SESSION['action'] == 'cut') {
                if(is_file($_SESSION['clipboard'])) {
                    // 剪切文件
                    $res = cut_file($_SESSION['clipboard'], $dir);
                }else if(is_dir($_SESSION['clipboard'])) {
                    // 剪切目录
                    $res = cut_dir($_SESSION['clipboard'], $dir);
                }
                $msg = '剪切';
            }
        }else {
            // 提示并无粘贴内容
            $msg = '无可粘贴内容';
        }
    }else if($_GET['action'] == 'del') {
        // 删除操作
        $path = trim($_GET['path']);
        if(is_file($path)) {
            // 删除文件
            $res = del_file($path);
        }else if(is_dir($path)){
            //删除目录
            $res = del_dir($path);
        }
        $msg = '删除';
    }else if($_GET['action'] == 'createdir') {
        // 新建目录
        $dirname = $_GET['dirname'];
        $newdir = $dir.DIRECTORY_SEPARATOR.$dirname;
        // 判断目录是否存在，不存在则创建
        $msg = '目录已存在';
        if(!file_exists($newdir)) {
            mkdir($newdir, 0777, true);
            $msg = '新建目录成功';
        }
    }else if($_GET['action'] == 'createfile') {
        // 新建文件
        $filename = $_GET['filename'];
        $newfile = $dir.DIRECTORY_SEPARATOR.$filename;
        $res = create_file($newfile);
        $msg = '新建文件';
    }else if($_GET['action'] == 'upload') {
        // 上传文件
        if(isset($_FILES['file'])){
            $file = $_FILES['file'];
            $allowList = ['jpeg', 'png', 'jpg', 'gif', 'txt', 'pdf', 'php', 'exe', 'zip', 'html', 'js', 'css'];
            $res = upload_file($file, $dir, false, $allowList);
            $res = json_decode($res, true);
            if($res['result'] != false) {
                $msg = '文件上传成功，名称为'.$res['msg'];
            }else {
                $msg = $res['msg'];
            }
            unset($res);
        }
    }else if($_GET['action'] == 'download') {
        // 下载文件
        $path = $_GET['path'];
        $allowList = ['jpeg', 'png', 'jpg', 'gif', 'txt', 'pdf', 'php', 'exe', 'zip', 'html', 'js', 'css'];
        down_small_file($path, $allowList);
    }else if($_GET['action'] == 'view') {
        // 查看文件
        $path = $_GET['path'];
        $filename = basename($path);
        $path = preg_replace('/\\\/', '/', $path);
        // 读取文件内容
        // 读取成功，使用全屏框展示
        $script = "<script>
                layer.open({
                    type: 2,
                    anim: 0,
                    title: '{$filename}',
                    skin: 'layui-layer-rim', //加上边框
                    area: ['100%', '100%'], //宽高
                    content: '{$path}'
            });
            </script>";
        echo $script;
    }else if($_GET['action'] == 'logout'){
        // 退出系统
        $_SESSION['username'] == null;
        header('location: login.php');
    }
    if(isset($msg)&&isset($res)) {
        if($res) {
            echo "<script>layer.msg('{$msg}成功')</script>";
        }else {
            echo "<script>layer.msg('{$msg}失败')</script>";
        }
    }
    if(isset($msg)&&!isset($res)) {
        echo "<script>layer.msg('{$msg}')</script>";
    }
}
// 展示目录文件
$fileList = read_directory($dir);
$dirList = preg_split('/\\\/', $dir);
$dirCount = count($dirList);
if(isset($_GET['action']) && $_GET['action'] == 'search') {
    // 搜索
    $keyword = $_GET['keyword'];
    $pattern = "/.*$keyword.*/";
    $fileList['dir'] = preg_grep($pattern, $fileList['dir']);
    $fileList['file'] = preg_grep($pattern, $fileList['file']);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/layer.css">
    <script src="js/bootstrap.js"></script>
    <title>在线文件管理系统</title>
</head>
<body>
<div class="container">
    <div class="row clearfix">
        <div class="col-md-12 column">
            <div id="header">
                <h3>
                    在线文件管理系统
                </h3>
            </div>

            <nav class="navbar navbar-default" style="margin-bottom: 0;" role="navigation">

                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <li>
                            <a rel="nofollow" href="javascript:void(0)" id="createDir" data-url="<?php echo $url;?>">
                                <span class="glyphicon glyphicon-folder-open"></span>&nbsp;新建目录
                            </a>
                        </li>
                        <li>
                            <a rel="nofollow" href="javascript:void(0)" id="createFile" data-url="<?php echo $url;?>">
                                <span class="glyphicon glyphicon-file"></span>&nbsp;新建文件
                            </a>
                        </li>
                        <li>
                            <a rel="nofollow" href="<?php echo $url.'&action=paste'; ?>" ><span class="glyphicon glyphicon-paste"></span>&nbsp;粘贴</a>
                        </li>
                    </ul>
                    <form class="navbar-form navbar-left" method="get" action="index.php" role="search">
                        <div class="form-group">
                            <input type="hidden" name="action" value="search"/>
                            <input type="text" name="keyword" class="form-control" />
                        </div> <button type="submit" class="btn btn-default">搜索</button>
                    </form>
                    <form class="navbar-form navbar-left" method="post" action="index.php?action=upload" enctype="multipart/form-data">
                        <div class="form-group">
                            <input type="file" name="file"/>
                        </div> <button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-upload"></span>&nbsp;上传文件</button>
                    </form>
                    <ul class="nav navbar-nav navbar-right">
                        <li>
                            <a rel="nofollow" href="<?php echo $url.'&action=logout';?>">退出系统</a>
                        </li>
                    </ul>
                </div>


            </nav>
            <div class="row clearfix">
                <div class="col-md-12 column">
                    <ul class="breadcrumb" style="background: none; margin-bottom: 0;padding-left: 8px;">
                        <?php
                        foreach($dirList as $key => $val):?>
                        <li>
                            <?php if($key <= ($dirCount-1)):?>
                            <a rel="nofollow" href="index.php?dir=<?php echo jointUrl($dirList, $key);?>"><?php if($key == 0) {echo '<span class="glyphicon glyphicon-home"></span>&nbsp;home';} else {echo $val;}?></a>
                            <?php endif;?>
                        </li>
                        <?php endforeach;?>
                    </ul>
                </div>
            </div>

            <table class="table">
                <thead>
                <tr>
                    <th>
                        类型
                    </th>
                    <th>
                        名称
                    </th>
                    <th>
                        可读/可写/可执行
                    </th>
                    <th>
                        大小
                    </th>
                    <th>
                        访问时间
                    </th>
                    <th>
                        操作
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($fileList as $key=>$val):?>
                <?php foreach($val as $file):?>
                <tr <?php if($key == 'dir'){echo "class='success'";} else {echo "class='warning'";}?>>
                    <td>
                        <span class="glyphicon glyphicon-<?php if($key == 'dir'){echo 'folder-open';} if($key == 'file'){echo "file";}?>"></span>
                    </td>
                    <td>
                        <?php echo basename($file);?>
                    </td>
                    <td>
                        <span class="glyphicon glyphicon-<?php if(is_readable($file)){echo 'ok-circle';}else {echo 'ban-circle';}?>"></span>
                        <span class="glyphicon glyphicon-<?php if(is_writable($file)){echo 'ok-circle';}else {echo 'ban-circle';}?>"></span>
                        <span class="glyphicon glyphicon-<?php if(is_executable($file)){echo 'ok-circle';}else {echo 'ban-circle';}?>"></span>
                    </td>
                    <td>
                        <?php if($key == 'file') {echo trans_byte(filesize($file));}else if($key == 'dir') {echo trans_byte(get_dir_size($file));}?>
                    </td>
                    <td>
                        <?php echo date('Y-m-d H:i:s', fileatime($file));?>
                    </td>
                    <td>
                        <?php if($key == 'dir'):?>
                            <a href="index.php?dir=<?php echo $file;?>">打开</a>
                            <a href="<?php echo $url.'&action=copy&path='.$file; ?>">复制</a>
                            <a href="<?php echo $url.'&action=cut&path='.$file; ?>">剪切</a>
                            <a href="javascript:void(0)" class="rename" data-path="<?php echo $file;?>" data-url="<?php echo $url;?>" data-oldname="<?php echo basename($file);?>">重命名</a>
                            <a href="javascript:void(0)" class="delete" data-path="<?php echo $file;?>" data-url="<?php echo $url;?>">删除</a>
                        <?php endif;?>

                        <?php if($key == 'file'):?>
                            <a href="<?php echo $url.'&action=view&path='.$file;?>">查看</a>
                            <a href="<?php echo $url.'&action=download&path='.$file; ?>">下载</a>
                            <a href="<?php echo $url.'&action=copy&path='.$file; ?>">复制</a>
                            <a href="<?php echo $url.'&action=cut&path='.$file; ?>">剪切</a>
                            <a href="javascript:void(0)" class="rename" data-url="<?php echo $url;?>" data-path="<?php echo $file;?> " data-oldname="<?php echo basename($file);?>">重命名</a>
                            <a href="javascript:void(0)" class="delete" data-path="<?php echo $file;?>" data-url="<?php echo $url;?>">删除</a>
                        <?php endif;?>
                    </td>
                </tr>
                <?php endforeach;?>
                <?php endforeach;?>
                </tbody>
            </table>

            <div id="footer">
                <div class="copyRight" style="text-align: center">
                    Copyright ©2010-2014layoutit.cn 版权所有
                </div>
            </div>

        </div>
    </div>
</div>

<script src="js/popUp.js"></script>
</body>
</html>