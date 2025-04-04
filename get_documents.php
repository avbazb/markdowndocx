<?php
// 引入函数库
require_once 'functions.php';

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse('只接受GET请求', 405);
}

// 连接数据库
global $conn;

// 获取文档列表
$sql = "SELECT id, title, content, created_at, updated_at FROM markdown_documents ORDER BY updated_at DESC";
$result = $conn->query($sql);

if (!$result) {
    sendErrorResponse('获取文档列表失败: ' . $conn->error);
}

$documents = [];
while ($row = $result->fetch_assoc()) {
    $documents[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'content' => $row['content'],
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at']
    ];
}

sendSuccessResponse(['documents' => $documents]);

$conn->close();
?> 