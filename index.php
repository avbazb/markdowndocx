<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Markdown在线显示系统</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="github.min.css">
    <script src="marked.min.js"></script>
    <script src="highlight.min.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Markdown在线显示系统</h1>
            <div class="nav-buttons">
                <button id="newDocBtn">新建文档</button>
                <button id="docsListBtn">文档列表</button>
                <button id="summaryBtn">生成AI总结</button>
            </div>
        </header>

        <main>
            <div id="editorContainer">
                <div id="editorWrapper">
                    <h2>编辑区</h2>
                    <textarea id="markdownEditor" placeholder="请输入Markdown文本..."></textarea>
                    <div class="editor-buttons">
                        <button id="saveBtn">保存</button>
                        <button id="copyBtn">复制带格式内容</button>
                    </div>
                </div>
                <div id="previewWrapper">
                    <h2>预览区</h2>
                    <div id="markdownPreview" class="markdown-body"></div>
                </div>
            </div>

            <div id="documentsList" class="hidden">
                <h2>文档列表</h2>
                <div class="list-container">
                    <div class="documents-grid" id="documentsGrid"></div>
                </div>
                <button id="backToEditorBtn" class="back-btn">返回编辑器</button>
            </div>

            <div id="summaryContainer" class="hidden">
                <h2>AI总结</h2>
                <div class="summary-options">
                    <div class="selected-docs">
                        <h3>已选择的文档</h3>
                        <ul id="selectedDocsList"></ul>
                    </div>
                    <button id="generateSummaryBtn" disabled>生成总结</button>
                </div>
                <div class="summary-result">
                    <h3>总结结果</h3>
                    <div id="summaryResult" class="markdown-body"></div>
                    <textarea id="summaryEditor" class="hidden"></textarea>
                    <div class="summary-buttons">
                        <button id="editSummaryBtn" class="hidden">编辑总结</button>
                        <button id="saveSummaryBtn" class="hidden">保存总结</button>
                        <button id="copySummaryBtn" class="hidden">复制总结</button>
                    </div>
                </div>
                <button id="backFromSummaryBtn" class="back-btn">返回文档列表</button>
            </div>
        </main>

        <div id="toast" class="toast hidden"></div>
    </div>

    <script src="script.js"></script>
</body>
</html> 