<?php
// 引入函数库
require_once 'functions.php';

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse('只接受GET请求', 405);
}

// 获取文档ID
$id = getGetParam('id');

if (empty($id)) {
    sendErrorResponse('文档ID不能为空');
}

// 连接数据库
global $conn;

// 获取文档信息
$stmt = $conn->prepare("SELECT id, title, content, created_at, updated_at FROM markdown_documents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    sendErrorResponse('获取文档失败: ' . $stmt->error);
}

if ($result->num_rows === 0) {
    sendErrorResponse('未找到文档', 404);
}

$document = $result->fetch_assoc();

sendSuccessResponse(['document' => [
    'id' => $document['id'],
    'title' => $document['title'],
    'content' => $document['content'],
    'created_at' => $document['created_at'],
    'updated_at' => $document['updated_at']
]]);

$stmt->close();
$conn->close();
?> 