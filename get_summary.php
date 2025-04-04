<?php
// 引入函数库
require_once 'functions.php';

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse('只接受GET请求', 405);
}

// 获取总结ID
$id = getGetParam('id');

if (empty($id)) {
    sendErrorResponse('总结ID不能为空');
}

// 连接数据库
global $conn;

// 获取总结信息
$stmt = $conn->prepare("SELECT id, title, content, doc_ids, created_at, updated_at FROM summaries WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    sendErrorResponse('获取总结失败: ' . $stmt->error);
}

if ($result->num_rows === 0) {
    sendErrorResponse('未找到总结', 404);
}

$summary = $result->fetch_assoc();

// 解析文档ID列表
$docIds = [];
if (!empty($summary['doc_ids'])) {
    $docIds = explode(',', $summary['doc_ids']);
}

sendSuccessResponse(['summary' => [
    'id' => $summary['id'],
    'title' => $summary['title'],
    'content' => $summary['content'],
    'doc_ids' => $docIds,
    'created_at' => $summary['created_at'],
    'updated_at' => $summary['updated_at']
]]);

$stmt->close();
$conn->close();
?> 