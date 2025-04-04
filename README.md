# Markdown在线显示系统

一个支持手机端和电脑端的Markdown格式文本在线显示系统，可以在云端保存文档，并使用AI生成总结。

## 功能特点

- 响应式设计，完美支持手机端和电脑端
- 实时Markdown预览功能
- 代码语法高亮显示
- 一键复制带格式的HTML内容，可粘贴到钉钉等云文档
- 在线保存文档，多用户可查看
- 选择多个文档，一键生成AI总结（基于Kimi API）
- AI总结内容可修改并二次标注

## 安装方法

1. 将所有文件上传到支持PHP的Web服务器
2. 导入数据库结构
   ```bash
   mysql -u username -p < database.sql
   ```
3. 修改`db_config.php`中的数据库连接信息
4. 在`functions.php`中配置你的Kimi API密钥
5. 确保Web服务器对目录有写入权限

## 目录结构

```
├── index.php            # 主页面
├── style.css            # 样式表
├── script.js            # 前端JavaScript
├── marked.min.js        # Markdown解析库
├── highlight.min.js     # 代码高亮库
├── github.min.css       # 代码高亮样式
├── db_config.php        # 数据库配置
├── functions.php        # 公共函数
├── save_markdown.php    # 保存文档API
├── get_documents.php    # 获取文档列表API
├── get_document.php     # 获取单个文档API
├── generate_summary.php # 生成AI总结API
├── update_summary.php   # 更新AI总结API
└── database.sql         # 数据库结构
```

## 使用方法

1. 打开系统首页，在编辑区输入Markdown格式的文本
2. 右侧会实时显示渲染后的效果
3. 点击"保存"按钮保存文档
4. 点击"复制HTML"按钮可复制带格式的内容
5. 点击"文档列表"查看所有保存的文档
6. 在文档列表中选择多个文档，然后点击"生成AI总结"
7. 生成的总结可以编辑和复制

## 技术栈

- 前端：HTML5, CSS3, JavaScript
- Markdown解析：marked.js
- 代码高亮：highlight.js
- 后端：PHP
- 数据库：MySQL
- AI总结：Kimi API

## 注意事项

- 在使用AI总结功能前，需要先在`functions.php`文件中配置有效的Kimi API密钥
- 系统不需要登录功能，所有用户共享所有文档
- 请确保服务器有足够的存储空间保存文档

## 许可协议

本项目采用MIT许可协议。 