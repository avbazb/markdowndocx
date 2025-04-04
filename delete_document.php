<?php
// 引入函数库
require_once 'functions.php';

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('只接受POST请求', 405);
}

// 获取文档ID
$id = getPostParam('id');

if (empty($id)) {
    sendErrorResponse('文档ID不能为空');
}

// 连接数据库
global $conn;

// 删除文档
$stmt = $conn->prepare("DELETE FROM markdown_documents WHERE id = ?");
$stmt->bind_param("i", $id);

if (!$stmt->execute()) {
    sendErrorResponse('删除文档失败: ' . $stmt->error);
}

if ($stmt->affected_rows === 0) {
    sendErrorResponse('未找到要删除的文档', 404);
}

// 相关总结也需要检查和更新
// 查找引用了此文档的总结
$relatedSummaries = [];
$summaryQuery = $conn->prepare("SELECT id, doc_ids FROM summaries");
$summaryQuery->execute();
$result = $summaryQuery->get_result();

while ($row = $result->fetch_assoc()) {
    $docIds = explode(',', $row['doc_ids']);
    if (in_array($id, $docIds)) {
        $relatedSummaries[] = $row['id'];
    }
}

// 如果有相关总结，可以选择删除或更新
if (!empty($relatedSummaries)) {
    // 这里只提示相关总结可能需要更新
    sendSuccessResponse([
        'message' => '文档删除成功，但有相关的总结可能需要更新',
        'related_summaries' => $relatedSummaries
    ]);
} else {
    sendSuccessResponse(['message' => '文档删除成功']);
}

$stmt->close();
$conn->close();
?> 