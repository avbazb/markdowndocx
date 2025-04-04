<?php
// 数据库连接配置
$db_host = 'localhost';      // 数据库主机名
$db_user = getenv('DB_USER');     // 从环境变量获取数据库用户
$db_password = getenv('DB_PASSWORD'); // 从环境变量获取数据库密码
$db_name = getenv('DB_NAME');     // 从环境变量获取数据库名称

// 创建数据库连接
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// 检查连接
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

// 设置字符集
$conn->set_charset("utf8mb4");
?>