document.addEventListener('DOMContentLoaded', function() {
    // DOM 元素
    const markdownEditor = document.getElementById('markdownEditor');
    const markdownPreview = document.getElementById('markdownPreview');
    const saveBtn = document.getElementById('saveBtn');
    const copyBtn = document.getElementById('copyBtn');
    const newDocBtn = document.getElementById('newDocBtn');
    const docsListBtn = document.getElementById('docsListBtn');
    const summaryBtn = document.getElementById('summaryBtn');
    const backToEditorBtn = document.getElementById('backToEditorBtn');
    const editorContainer = document.getElementById('editorContainer');
    const documentsList = document.getElementById('documentsList');
    const documentsGrid = document.getElementById('documentsGrid');
    const summaryContainer = document.getElementById('summaryContainer');
    const selectedDocsList = document.getElementById('selectedDocsList');
    const generateSummaryBtn = document.getElementById('generateSummaryBtn');
    const summaryResult = document.getElementById('summaryResult');
    const summaryEditor = document.getElementById('summaryEditor');
    const editSummaryBtn = document.getElementById('editSummaryBtn');
    const saveSummaryBtn = document.getElementById('saveSummaryBtn');
    const copySummaryBtn = document.getElementById('copySummaryBtn');
    const backFromSummaryBtn = document.getElementById('backFromSummaryBtn');
    const toast = document.getElementById('toast');

    // 状态变量
    let currentDocId = null;
    let selectedDocs = [];
    let currentSummaryId = null;

    // 安全解析Markdown
    function safeMarkdownParse(content) {
        try {
            // 确保内容是字符串
            if (content === null || content === undefined) {
                return '';
            }
            
            // 如果内容是对象，尝试JSON序列化
            if (typeof content === 'object') {
                try {
                    content = JSON.stringify(content);
                } catch (jsonError) {
                    console.error('转换对象为JSON字符串失败:', jsonError);
                    content = String(content);
                }
            } else if (typeof content !== 'string') {
                content = String(content);
            }
            
            // 预处理：删除内容最后的孤立反引号，这些引号可能导致解析错误
            content = content.replace(/```\s*$/, '');
            
            try {
                return marked.parse(content);
            } catch (markdownError) {
                console.error('初次Markdown解析失败，尝试更多预处理:', markdownError);
                
                // 更多预处理：替换所有三个反引号块，将它们转换为预格式化文本
                const preprocessed = content.replace(/```[\s\S]*?```/g, (match) => {
                    return '<pre>' + match.substring(3, match.length - 3) + '</pre>';
                });
                
                return marked.parse(preprocessed);
            }
        } catch (error) {
            console.error('Markdown解析错误:', error);
            showToast('Markdown解析错误: ' + error.message, true);
            // 解析失败时返回原始内容
            return `<pre>${content}</pre>`;
        }
    }

    // 初始化 marked 库
    marked.use({
        renderer: {
            code(code, language) {
                // 确保language是字符串类型
                const lang = (language && typeof language === 'string') ? language : 'plaintext';
                const validLanguage = hljs.getLanguage(lang) ? lang : 'plaintext';
                try {
                    const highlightedCode = hljs.highlight(code, { language: validLanguage }).value;
                    return `<pre><code class="hljs ${validLanguage}">${highlightedCode}</code></pre>`;
                } catch (error) {
                    console.error('代码高亮错误:', error);
                    return `<pre><code class="hljs">${marked.parseInline(code)}</code></pre>`;
                }
            }
        },
        // 添加安全措施，确保输入是字符串
        tokenizer: {
            codespan(src) {
                if (typeof src !== 'string') {
                    console.error('非字符串输入:', src);
                    return false;
                }
                return false; // 让默认的tokenizer处理
            }
        }
    });

    // 更新预览
    function updatePreview() {
        try {
            const markdownText = markdownEditor.value || '';
            markdownPreview.innerHTML = safeMarkdownParse(markdownText);
        } catch (error) {
            console.error('Markdown解析错误:', error);
            showToast('Markdown解析错误: ' + error.message, true);
        }
    }

    // 显示通知
    function showToast(message, isError = false) {
        toast.textContent = message;
        toast.classList.remove('hidden');
        if (isError) {
            toast.classList.add('error');
        } else {
            toast.classList.remove('error');
        }
        
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }

    // 保存文档
    async function saveDocument() {
        const title = prompt('请输入文档标题', '未命名文档');
        if (!title) return;

        const content = markdownEditor.value;
        if (!content.trim()) {
            showToast('内容不能为空', true);
            return;
        }

        try {
            const formData = new FormData();
            formData.append('title', title);
            formData.append('content', content);
            if (currentDocId) {
                formData.append('id', currentDocId);
            }

            const response = await fetch('save_markdown.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                currentDocId = data.id;
                showToast('文档保存成功');
            } else {
                throw new Error(data.message || '保存失败');
            }
        } catch (error) {
            showToast(`保存失败: ${error.message}`, true);
        }
    }

    // 加载文档列表
    async function loadDocuments() {
        try {
            documentsGrid.innerHTML = '<p>正在加载文档列表...</p>';
            
            const response = await fetch('get_documents.php');
            const data = await response.json();
            
            if (data.success) {
                renderDocumentsList(data.documents);
            } else {
                throw new Error(data.message || '加载文档列表失败');
            }
        } catch (error) {
            showToast(`加载文档列表失败: ${error.message}`, true);
            documentsGrid.innerHTML = '<p>加载文档列表失败，请刷新页面重试</p>';
        }
    }

    // 渲染文档列表
    function renderDocumentsList(documents) {
        documentsGrid.innerHTML = '';
        
        if (documents.length === 0) {
            documentsGrid.innerHTML = '<p>没有找到任何文档</p>';
            return;
        }

        documents.forEach(doc => {
            const card = document.createElement('div');
            card.className = 'document-card';
            
            // 使用文本内容预览而不是HTML预览，避免可能的XSS
            const preview = doc.content.substring(0, 150) + (doc.content.length > 150 ? '...' : '');
            
            // 将日期格式化为本地日期时间
            const date = new Date(doc.created_at);
            const formattedDate = date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
            
            card.innerHTML = `
                <div class="document-title">${doc.title}</div>
                <div class="document-date">${formattedDate}</div>
                <div class="document-preview">${preview}</div>
                <div class="document-actions">
                    <div class="checkbox-container">
                        <input type="checkbox" id="select-${doc.id}" class="doc-select" data-id="${doc.id}" data-title="${doc.title}">
                        <label for="select-${doc.id}">选择</label>
                    </div>
                    <div>
                        <button class="edit-doc" data-id="${doc.id}">编辑</button>
                        <button class="view-doc" data-id="${doc.id}">查看</button>
                        <button class="delete-doc" data-id="${doc.id}" data-title="${doc.title}">删除</button>
                    </div>
                </div>
            `;
            
            documentsGrid.appendChild(card);
        });

        // 添加事件监听器
        document.querySelectorAll('.edit-doc').forEach(btn => {
            btn.addEventListener('click', function() {
                const docId = this.getAttribute('data-id');
                loadDocument(docId, true);
            });
        });

        document.querySelectorAll('.view-doc').forEach(btn => {
            btn.addEventListener('click', function() {
                const docId = this.getAttribute('data-id');
                loadDocument(docId, false);
            });
        });

        document.querySelectorAll('.delete-doc').forEach(btn => {
            btn.addEventListener('click', function() {
                const docId = this.getAttribute('data-id');
                const docTitle = this.getAttribute('data-title');
                confirmDeleteDocument(docId, docTitle);
            });
        });

        document.querySelectorAll('.doc-select').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const docId = this.getAttribute('data-id');
                const docTitle = this.getAttribute('data-title');
                
                if (this.checked) {
                    if (!selectedDocs.some(doc => doc.id === docId)) {
                        selectedDocs.push({ id: docId, title: docTitle });
                    }
                } else {
                    selectedDocs = selectedDocs.filter(doc => doc.id !== docId);
                }
                
                updateSelectedDocsList();
            });
        });
    }

    // 更新已选文档列表
    function updateSelectedDocsList() {
        selectedDocsList.innerHTML = '';
        
        if (selectedDocs.length === 0) {
            generateSummaryBtn.disabled = true;
            selectedDocsList.innerHTML = '<li>未选择任何文档</li>';
            return;
        }

        generateSummaryBtn.disabled = false;
        
        selectedDocs.forEach(doc => {
            const li = document.createElement('li');
            li.textContent = doc.title;
            selectedDocsList.appendChild(li);
        });
    }

    // 加载单个文档
    async function loadDocument(id, edit = false) {
        try {
            const response = await fetch(`get_document.php?id=${id}`);
            const data = await response.json();
            
            if (data.success) {
                const doc = data.document;
                
                if (edit) {
                    // 编辑模式
                    markdownEditor.value = doc.content;
                    updatePreview();
                    currentDocId = doc.id;
                    
                    // 切换到编辑界面
                    documentsList.classList.add('hidden');
                    editorContainer.classList.remove('hidden');
                } else {
                    // 查看模式
                    try {
                        // 确保文档内容是字符串
                        const safeContent = typeof doc.content === 'string' ? doc.content : String(doc.content);
                        const html = safeMarkdownParse(safeContent);
                        
                        const viewWindow = window.open('', '_blank');
                        
                        if (viewWindow) {
                            viewWindow.document.write(`
                                <!DOCTYPE html>
                                <html>
                                <head>
                                    <title>${doc.title}</title>
                                    <link rel="stylesheet" href="github.min.css">
                                    <link rel="stylesheet" href="style.css">
                                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                    <style>
                                        body {
                                            padding: 20px;
                                            max-width: 800px;
                                            margin: 0 auto;
                                        }
                                        .document-header {
                                            margin-bottom: 20px;
                                            border-bottom: 1px solid #ddd;
                                            padding-bottom: 10px;
                                        }
                                        .copy-btn {
                                            margin-top: 20px;
                                        }
                                    </style>
                                    <script src="marked.min.js"></script>
                                    <script src="highlight.min.js"></script>
                                </head>
                                <body>
                                    <div class="document-header">
                                        <h1>${doc.title}</h1>
                                        <p>创建于: ${new Date(doc.created_at).toLocaleString()}</p>
                                    </div>
                                    <div class="markdown-body">${html}</div>
                                    <button class="copy-btn" onclick="copyFormattedContent()">复制带格式内容</button>
                                    <script>
                                        function copyFormattedContent() {
                                            const markdownBody = document.querySelector('.markdown-body');
                                            
                                            // 创建一个临时元素并选中它
                                            const range = document.createRange();
                                            range.selectNode(markdownBody);
                                            window.getSelection().removeAllRanges();
                                            window.getSelection().addRange(range);
                                            
                                            try {
                                                // 尝试使用新的剪贴板API
                                                const clipboardItem = new ClipboardItem({
                                                    'text/html': new Blob([markdownBody.innerHTML], {type: 'text/html'}),
                                                    'text/plain': new Blob([markdownBody.innerText], {type: 'text/plain'})
                                                });
                                                navigator.clipboard.write([clipboardItem])
                                                    .then(() => alert('内容已复制到剪贴板（带格式）'))
                                                    .catch(err => {
                                                        console.error('剪贴板API失败:', err);
                                                        // 回退到旧方法
                                                        document.execCommand('copy');
                                                        alert('内容已复制到剪贴板');
                                                    });
                                            } catch (e) {
                                                // 如果新API不可用，使用传统方法
                                                document.execCommand('copy');
                                                alert('内容已复制到剪贴板');
                                            }
                                            
                                            window.getSelection().removeAllRanges();
                                        }
                                    </script>
                                </body>
                                </html>
                            `);
                            viewWindow.document.close();
                        }
                    } catch (parseError) {
                        console.error('Markdown解析错误:', parseError);
                        showToast(`Markdown解析错误: ${parseError.message}`, true);
                    }
                }
            } else {
                throw new Error(data.message || '加载文档失败');
            }
        } catch (error) {
            showToast(`加载文档失败: ${error.message}`, true);
        }
    }

    // 生成AI总结
    async function generateSummary() {
        if (selectedDocs.length === 0) {
            showToast('请先选择要总结的文档', true);
            return;
        }

        try {
            const docIds = selectedDocs.map(doc => doc.id);
            
            // 显示加载状态
            summaryResult.innerHTML = '<p>正在生成总结，请稍候...</p>';
            generateSummaryBtn.disabled = true;
            editSummaryBtn.classList.add('hidden');
            copySummaryBtn.classList.add('hidden');
            
            const formData = new FormData();
            formData.append('doc_ids', JSON.stringify(docIds));
            
            const response = await fetch('generate_summary.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                try {
                    // 使用通用的安全解析函数处理内容
                    summaryResult.innerHTML = safeMarkdownParse(data.summary.content);
                    
                    currentSummaryId = data.summary.id;
                    
                    // 显示编辑和复制按钮
                    editSummaryBtn.classList.remove('hidden');
                    copySummaryBtn.classList.remove('hidden');
                    
                    showToast('总结生成成功');
                } catch (error) {
                    console.error('处理总结内容错误:', error);
                    // 异常情况下显示原始响应，确保内容能被显示
                    const contentToShow = typeof data.summary.content === 'string' 
                        ? data.summary.content.replace(/\n/g, '<br>') 
                        : JSON.stringify(data.summary, null, 2);
                    
                    summaryResult.innerHTML = `<div class="markdown-body">${contentToShow}</div>`;
                    showToast('总结处理失败，显示原始内容', true);
                }
            } else {
                throw new Error(data.message || '生成总结失败');
            }
        } catch (error) {
            showToast(`生成总结失败: ${error.message}`, true);
            summaryResult.innerHTML = '<p>生成总结时出错</p>';
        } finally {
            generateSummaryBtn.disabled = false;
        }
    }

    // 编辑总结
    function editSummary() {
        // 获取原始Markdown内容
        try {
            const response = fetch(`get_summary.php?id=${currentSummaryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        summaryEditor.value = data.summary.content;
                    } else {
                        // 如果API不可用，使用备选方法提取文本
                        summaryEditor.value = summaryResult.textContent;
                    }
                    
                    // 显示编辑区域
                    summaryResult.classList.add('hidden');
                    summaryEditor.classList.remove('hidden');
                    
                    // 更新按钮状态
                    editSummaryBtn.classList.add('hidden');
                    saveSummaryBtn.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('获取总结内容失败:', error);
                    // 备选方法
                    summaryEditor.value = summaryResult.textContent;
                    
                    // 显示编辑区域
                    summaryResult.classList.add('hidden');
                    summaryEditor.classList.remove('hidden');
                    
                    // 更新按钮状态
                    editSummaryBtn.classList.add('hidden');
                    saveSummaryBtn.classList.remove('hidden');
                });
        } catch (error) {
            // 如果API请求出错，使用备选方法
            summaryEditor.value = summaryResult.textContent;
            
            // 显示编辑区域
            summaryResult.classList.add('hidden');
            summaryEditor.classList.remove('hidden');
            
            // 更新按钮状态
            editSummaryBtn.classList.add('hidden');
            saveSummaryBtn.classList.remove('hidden');
        }
    }

    // 保存总结
    async function saveSummary() {
        if (!currentSummaryId) {
            showToast('未找到总结ID', true);
            return;
        }

        try {
            const content = summaryEditor.value;
            
            const formData = new FormData();
            formData.append('id', currentSummaryId);
            formData.append('content', content);
            
            const response = await fetch('update_summary.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                // 更新显示
                try {
                    // 使用通用的安全解析函数处理内容
                    summaryResult.innerHTML = safeMarkdownParse(content);
                } catch (error) {
                    console.error('处理总结内容错误:', error);
                    summaryResult.innerHTML = `<div class="markdown-body">${content.replace(/\n/g, '<br>')}</div>`;
                }
                
                // 隐藏编辑区域
                summaryEditor.classList.add('hidden');
                summaryResult.classList.remove('hidden');
                
                // 更新按钮状态
                saveSummaryBtn.classList.add('hidden');
                editSummaryBtn.classList.remove('hidden');
                
                showToast('总结更新成功');
            } else {
                throw new Error(data.message || '更新总结失败');
            }
        } catch (error) {
            showToast(`更新总结失败: ${error.message}`, true);
        }
    }

    // 复制总结
    function copySummary() {
        try {
            // 尝试使用新的剪贴板API来保持格式
            const htmlContent = summaryResult.innerHTML;
            const textContent = summaryResult.innerText;
            
            if (navigator.clipboard && window.ClipboardItem) {
                const clipboardItem = new ClipboardItem({
                    'text/html': new Blob([htmlContent], {type: 'text/html'}),
                    'text/plain': new Blob([textContent], {type: 'text/plain'})
                });
                
                navigator.clipboard.write([clipboardItem])
                    .then(() => showToast('总结已复制到剪贴板（带格式）'))
                    .catch(err => {
                        console.error('剪贴板API失败:', err);
                        // 回退到传统方法
                        fallbackCopy();
                    });
            } else {
                // 如果新API不可用
                fallbackCopy();
            }
        } catch (error) {
            console.error('复制失败:', error);
            // 回退到传统方法
            fallbackCopy();
        }
        
        // 传统复制方法
        function fallbackCopy() {
            const range = document.createRange();
            range.selectNode(summaryResult);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();
            showToast('总结已复制到剪贴板');
        }
    }

    // 复制带格式的内容
    function copyHtml() {
        try {
            // 尝试使用新的剪贴板API来保持格式
            const htmlContent = markdownPreview.innerHTML;
            const textContent = markdownPreview.innerText;
            
            if (navigator.clipboard && window.ClipboardItem) {
                const clipboardItem = new ClipboardItem({
                    'text/html': new Blob([htmlContent], {type: 'text/html'}),
                    'text/plain': new Blob([textContent], {type: 'text/plain'})
                });
                
                navigator.clipboard.write([clipboardItem])
                    .then(() => showToast('内容已复制到剪贴板（带格式）'))
                    .catch(err => {
                        console.error('剪贴板API失败:', err);
                        // 回退到旧方法
                        fallbackCopy();
                    });
            } else {
                // 如果新API不可用
                fallbackCopy();
            }
        } catch (error) {
            console.error('复制失败:', error);
            // 回退到旧方法
            fallbackCopy();
        }
        
        // 传统复制方法（会丢失部分格式）
        function fallbackCopy() {
            const range = document.createRange();
            range.selectNode(markdownPreview);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();
            showToast('内容已复制到剪贴板');
        }
    }

    // 确认删除文档
    function confirmDeleteDocument(id, title) {
        if (confirm(`确定要删除文档"${title}"吗？此操作不可恢复。`)) {
            deleteDocument(id);
        }
    }

    // 删除文档
    async function deleteDocument(id) {
        try {
            const formData = new FormData();
            formData.append('id', id);
            
            const response = await fetch('delete_document.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showToast(data.message);
                // 如果有关联的总结，提示用户
                if (data.related_summaries && data.related_summaries.length > 0) {
                    showToast('注意：删除的文档关联了一些总结，这些总结可能需要更新', true);
                }
                // 重新加载文档列表
                loadDocuments();
            } else {
                throw new Error(data.message || '删除文档失败');
            }
        } catch (error) {
            showToast(`删除文档失败: ${error.message}`, true);
        }
    }

    // 事件监听器
    markdownEditor.addEventListener('input', updatePreview);
    saveBtn.addEventListener('click', saveDocument);
    copyBtn.addEventListener('click', copyHtml);
    
    newDocBtn.addEventListener('click', function() {
        currentDocId = null;
        markdownEditor.value = '';
        updatePreview();
        editorContainer.classList.remove('hidden');
        documentsList.classList.add('hidden');
        summaryContainer.classList.add('hidden');
    });
    
    docsListBtn.addEventListener('click', function() {
        loadDocuments();
        editorContainer.classList.add('hidden');
        documentsList.classList.remove('hidden');
        summaryContainer.classList.add('hidden');
    });
    
    summaryBtn.addEventListener('click', function() {
        // 清空已选文档列表
        selectedDocs = [];
        updateSelectedDocsList();
        
        // 先加载文档列表
        loadDocuments();
        
        // 显示文档列表和总结区域
        editorContainer.classList.add('hidden');
        documentsList.classList.remove('hidden'); // 显示文档列表，让用户可以选择文档
        summaryContainer.classList.remove('hidden');
        
        // 确保用户知道需要选择文档
        showToast('请从下方列表中选择要总结的文档');
    });
    
    backToEditorBtn.addEventListener('click', function() {
        editorContainer.classList.remove('hidden');
        documentsList.classList.add('hidden');
        summaryContainer.classList.add('hidden');
    });
    
    backFromSummaryBtn.addEventListener('click', function() {
        summaryContainer.classList.add('hidden');
    });
    
    generateSummaryBtn.addEventListener('click', generateSummary);
    editSummaryBtn.addEventListener('click', editSummary);
    saveSummaryBtn.addEventListener('click', saveSummary);
    copySummaryBtn.addEventListener('click', copySummary);

    // 初始化
    updatePreview();
}); 