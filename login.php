<?php
/**
 * Created by PhpStorm.
 * User: AmyLiu
 * Date: 2019/10/27
 * Time: 11:14
 */
header('content-type:text/html;charset=utf-8');
echo "<script src='js/jquery.js'></script>";
echo "<script src='js/layer/layer.js'></script>";
if(isset($_POST['username'])) {
    // 如果接受到登录信息
    $username = $_POST['username'];
    $password = $_POST['password'];
    // 查询数据库，看是否匹配
    $config = require './lib/config.php';
    $db_host = $config['DB_HOST'];
    $db_name = $config['DB_NAME'];
    $db_user = $config['DB_USER'];
    $db_password = $config['DB_PASS'];
    $db = new PDO("mysql:host={$db_host};dbname={$db_name}", $db_user, $db_password);
//    var_dump($db);
    $sql = "SELECT username FROM file_user WHERE username = :username AND password = :password";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->execute();
    $res = $stmt->fetch();
//    var_dump($res);
    if($res) {
        session_start();
        $_SESSION['username'] = $res['username'];
        header('location: index.php');
    }else {
        echo '<script>layer.msg("用户名或密码有误");</script>';
    }
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
    <title>登录</title>
</head>
<body>
<div class="container">
    <div class="row clearfix" style="margin-top: 100px;">
        <div class="col-md-12 column">
            <div class="row clearfix">
                <div class="col-md-3 column">
                </div>
                <div class="col-md-6 column bg-success" style="padding: 20px 40px">
                    <h3>
                        在线文件管理系统
                    </h3>
                    <form class="form-horizontal" role="form" method="post" action="login.php">
                        <div class="form-group">
                            <label for="inputEmail3" class="col-sm-2 control-label">用户名：</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="inputEmail3" name="username"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-2 control-label">密码：</label>
                            <div class="col-sm-10">
                                <input type="password" class="form-control" id="inputPassword3" name="password"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button type="submit" class="btn btn-default">登录</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-3 column">
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
