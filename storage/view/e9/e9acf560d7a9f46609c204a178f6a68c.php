<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* errors/500.html.twig */
class __TwigTemplate_e37fdde8b0118a444ed4dbaf02fb6845 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 2
        yield "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>500 - Internal Server Error</title>
    <!-- 引入我们自己的纯CSS文件 -->
    <style>

/* styles.css */

/* --- 基础重置和全局样式 --- */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;
    background-color: #f3f4f6; /* bg-gray-100 */
    color: #1f2937; /* text-gray-800 */
    line-height: 1.5;
}

/* --- 布局组件 --- */
.container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1rem; /* p-4 */
}

.card {
    width: 100%;
    max-width: 56rem; /* max-w-4xl */
    background-color: #ffffff; /* bg-white */
    border-radius: 0.5rem; /* rounded-lg */
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); /* shadow-xl */
    overflow: hidden;
}

/* --- 卡片头部 --- */
.card-header {
    background-color: #dc2626; /* bg-red-600 */
    color: #ffffff; /* text-white */
    padding: 1.5rem; /* p-6 */
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.error-title {
    font-size: 1.875rem; /* text-3xl */
    font-weight: 700; /* font-bold */
    display: flex;
    align-items: center;
}

.error-code {
    background-color: #b91c1c; /* bg-red-700 */
    color: #ffffff; /* text-white */
    font-size: 0.75rem; /* text-xs */
    padding: 0.25rem 0.75rem; /* px-3 py-1 */
    border-radius: 9999px; /* rounded-full */
}

.error-subtitle {
    margin-top: 0.5rem; /* mt-2 */
    color: #fecdd3; /* text-red-100 */
}

/* --- 卡片主体 --- */
.card-body {
    padding: 1.5rem; /* p-6 */
}

/* --- 错误消息框 --- */
.error-message-box {
    margin-bottom: 1.5rem; /* mb-6 */
    padding: 1rem; /* p-4 */
    background-color: #fef2f2; /* bg-red-50 */
    border-left: 4px solid #ef4444; /* border-l-4 border-red-500 */
    border-radius: 0.375rem; /* rounded */
}

.error-message-title {
    font-size: 1.125rem; /* text-lg */
    font-weight: 600; /* font-semibold */
    color: #991b1b; /* text-red-800 */
    margin-bottom: 0.5rem; /* mb-2 */
}

.error-message-content {
    color: #4b5563; /* text-gray-700 */
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; /* font-mono */
    background-color: #fafafa; /* bg-gray-50 */
    padding: 0.75rem; /* p-3 */
    border-radius: 0.375rem; /* rounded */
    overflow-x: auto;
}

/* --- 错误位置 (使用Flexbox实现8:2比例) --- */
.error-location {
    display: flex;
    flex-wrap: wrap; /* 允许在小屏幕上换行 */
    gap: 1rem; /* gap-4 */
    margin-bottom: 1.5rem; /* mb-6 */
}

.location-item {
    flex: 1; /* 基础flex值 */
    min-width: 200px; /* 在非常小的屏幕上保证最小宽度 */
    padding: 1rem; /* p-4 */
    background-color: #f9fafb; /* bg-gray-50 */
    border-radius: 0.375rem; /* rounded */
}

.location-item.file {
    flex-grow: 8; /* 文件部分占据8份 */
}

.location-item.line {
    flex-grow: 2; /* 行号部分占据2份 */
}

.location-title {
    font-size: 1.125rem; /* text-lg */
    font-weight: 600; /* font-semibold */
    color: #1f2937; /* text-gray-800 */
    margin-bottom: 0.5rem; /* mb-2 */
    display: flex;
    align-items: center;
}

.location-content {
    color: #4b5563; /* text-gray-700 */
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; /* font-mono */
    background-color: #ffffff; /* bg-white */
    padding: 0.5rem; /* p-2 */
    border-radius: 0.375rem; /* rounded */
    overflow-x: auto;
}

.location-content.line-number {
    text-align: center; /* text-center */
    width: 5rem; /* w-20 */
}

/* --- 堆栈跟踪 (可折叠部分) --- */
.stack-trace {
    border: 1px solid #e5e7eb; /* border border-gray-200 */
    border-radius: 0.5rem; /* rounded-lg */
    overflow: hidden;
}

.toggle-button {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem; /* p-4 */
    background-color: #f9fafb; /* bg-gray-50 */
    cursor: pointer;
    border: none;
    font-size: 1.125rem; /* text-lg */
    font-weight: 600; /* font-semibold */
    color: #1f2937; /* text-gray-800 */
}

.toggle-button:hover {
    background-color: #f3f4f6; /* hover:bg-gray-100 */
}

.trace-content {
    display: none; /* 默认隐藏 */
    padding: 1rem; /* p-4 */
    background-color: #f9fafb; /* bg-gray-50 */
}

.trace-list {
    list-style-type: decimal;
    list-style-position: inside; /* list-inside */
    margin-left: 0.5rem; /* ml-2 */
}

.trace-item {
    margin-bottom: 1rem; /* space-y-4 */
    padding: 0.75rem; /* p-3 */
    background-color: #ffffff; /* bg-white */
    border-radius: 0.375rem; /* rounded */
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* shadow-sm */
    border-left: 2px solid #a855f7; /* border-l-2 border-purple-400 */
}

.trace-details {
    display: grid;
    grid-template-columns: 3fr 1fr; /* 在大屏幕上分为两列 */
    gap: 0.5rem; /* gap-2 */
    font-size: 0.875rem; /* text-sm */
}

.trace-file {
    grid-column: span 2; /* 文件信息占满两列 */
    font-size: 0.75rem; /* text-xs */
}

/* --- 卡片底部 --- */
.card-footer {
    background-color: #f9fafb; /* bg-gray-50 */
    padding: 1rem; /* p-4 */
    border-top: 1px solid #e5e7eb; /* border-t border-gray-200 */
    text-align: center; /* text-center */
}

.footer-links {
    color: #4b5563; /* text-gray-600 */
    font-size: 0.875rem; /* text-sm */
}

.footer-link {
    color: #2563eb; /* text-blue-500 */
    font-weight: 500; /* font-medium */
    text-decoration: none;
}

.footer-link:hover {
    color: #1d4ed8; /* hover:text-blue-700 */
}

.footer-divider {
    margin: 0 0.5rem; /* mx-2 */
    color: #d1d5db; /* text-gray-400 */
}

/* --- 页脚 --- */
.page-footer {
    margin-top: 1.5rem; /* mt-6 */
    color: #6b7280; /* text-gray-500 */
    font-size: 0.75rem; /* text-sm */
}

/* --- 自定义图标 (使用伪元素) --- */
.icon {
    margin-right: 0.75rem; /* mr-3 或 mr-2 */
    font-size: 1.25rem;
}

.icon-exclamation::before {
    content: \"⚠\";
}

.icon-file::before {
    content: \"📄\";
}

.icon-line::before {
    content: \"🔢\";
}

.icon-history::before {
    content: \"📜\";
}

.icon-refresh::before {
    content: \"↻\";
}

.icon-home::before {
    content: \"⌂\";
}

.icon-chevron::before {
    content: \"▼\";
    transition: transform 0.3s ease;
}

.icon-chevron.rotate::before {
    transform: rotate(180deg);
}

</style>
</head>
<body>
    <div class=\"container\">
        <!-- 错误卡片 -->
        <div class=\"card\">
            <!-- 卡片头部 - 红色背景 -->
            <div class=\"card-header\">
                <div class=\"header-content\">
                    <h1 class=\"error-title\">
                        <span class=\"icon icon-exclamation\"></span>
                        500 - Internal Server Error
                    </h1>
                    <span class=\"error-code\">
                        ";
        // line 302
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["exception"] ?? null), "code", [], "any", false, false, false, 302), "html", null, true);
        yield "
                    </span>
                </div>
                <p class=\"error-subtitle\">
                    Sorry, something went wrong on the server.
                </p>
            </div>

            <!-- 卡片主体 -->
            <div class=\"card-body\">
                <!-- 错误消息 -->
                <div class=\"error-message-box\">
                    <h2 class=\"error-message-title\">Error Message</h2>
                    <p class=\"error-message-content\">
                        ";
        // line 316
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["exception"] ?? null), "message", [], "any", false, false, false, 316), "html", null, true);
        yield "
                    </p>
                </div>

                <!-- 错误位置 (使用Flexbox实现8:2比例) -->
                <div class=\"error-location\">
                    <div class=\"location-item file\">
                        <h2 class=\"location-title\">
                            <span class=\"icon icon-file\"></span> File
                        </h2>
                        <p class=\"location-content\">
                            ";
        // line 327
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["exception"] ?? null), "file", [], "any", false, false, false, 327), "html", null, true);
        yield "
                        </p>
                    </div>
                    <div class=\"location-item line\">
                        <h2 class=\"location-title\">
                            <span class=\"icon icon-line\"></span> Line
                        </h2>
                        <p class=\"location-content line-number\">
                            ";
        // line 335
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["exception"] ?? null), "line", [], "any", false, false, false, 335), "html", null, true);
        yield "
                        </p>
                    </div>
                </div>

                <!-- 错误追踪 (Trace) - 可折叠 -->
                <div class=\"stack-trace\">
                    <button id=\"toggleTrace\" class=\"toggle-button\">
                        <h2 class=\"location-title\">
                            <span class=\"icon icon-history\"></span>
                            Stack Trace
                        </h2>
                        <span id=\"traceIcon\" class=\"icon icon-chevron\"></span>
                    </button>
                    <div id=\"traceContent\" class=\"trace-content\">
                        <ol class=\"trace-list\">
                            ";
        // line 351
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["exception"] ?? null), "trace", [], "any", false, false, false, 351));
        foreach ($context['_seq'] as $context["_key"] => $context["trace"]) {
            // line 352
            yield "                                <li class=\"trace-item\">
                                    <div class=\"trace-details\">
                                        ";
            // line 354
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["trace"], "class", [], "any", false, false, false, 354)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 355
                yield "                                            <div>
                                                <strong>Class:</strong> ";
                // line 356
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["trace"], "class", [], "any", false, false, false, 356), "html", null, true);
                yield "
                                            </div>
                                            <div>
                                                <strong>Function:</strong> ";
                // line 359
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["trace"], "function", [], "any", false, false, false, 359), "html", null, true);
                yield "
                                            </div>
                                        ";
            } else {
                // line 362
                yield "                                            <div>
                                                <strong>Function:</strong> ";
                // line 363
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["trace"], "function", [], "any", false, false, false, 363), "html", null, true);
                yield "
                                            </div>
                                        ";
            }
            // line 366
            yield "                                        <div class=\"trace-file\">
                                            <strong>File:</strong> ";
            // line 367
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["trace"], "file", [], "any", false, false, false, 367), "html", null, true);
            yield ":";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["trace"], "line", [], "any", false, false, false, 367), "html", null, true);
            yield "
                                        </div>
                                    </div>
                                </li>
                            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['trace'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 372
        yield "                        </ol>
                    </div>
                </div>
            </div>

            <!-- 卡片底部 -->
            <div class=\"card-footer\">
                <p class=\"footer-links\">
                    <a href=\"#\" class=\"footer-link\">
                        <span class=\"icon icon-refresh\"></span> Refresh Page
                    </a>
                    <span class=\"footer-divider\">|</span>
                    <a href=\"/\" class=\"footer-link\">
                        <span class=\"icon icon-home\"></span> Return to Home
                    </a>
                </p>
            </div>
        </div>

        <!-- 页脚 -->
        <p class=\"page-footer\">
            ";
        // line 393
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Twig\Extension\CoreExtension']->formatDate("now", "Y"), "html", null, true);
        yield " Your Application Name. All rights reserved.
        </p>
    </div>

    <!-- 内联JavaScript用于折叠功能 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.getElementById('toggleTrace');
            const traceContent = document.getElementById('traceContent');
            const traceIcon = document.getElementById('traceIcon');

            toggleButton.addEventListener('click', function() {
                // 切换内容的显示/隐藏
                if (traceContent.style.display === 'block') {
                    traceContent.style.display = 'none';
                    traceIcon.classList.remove('rotate');
                } else {
                    traceContent.style.display = 'block';
                    traceIcon.classList.add('rotate');
                }
            });
        });
    </script>
</body>
</html>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "errors/500.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  478 => 393,  455 => 372,  442 => 367,  439 => 366,  433 => 363,  430 => 362,  424 => 359,  418 => 356,  415 => 355,  413 => 354,  409 => 352,  405 => 351,  386 => 335,  375 => 327,  361 => 316,  344 => 302,  42 => 2,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{# templates/error_500.html.twig #}
<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>500 - Internal Server Error</title>
    <!-- 引入我们自己的纯CSS文件 -->
    <style>

/* styles.css */

/* --- 基础重置和全局样式 --- */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;
    background-color: #f3f4f6; /* bg-gray-100 */
    color: #1f2937; /* text-gray-800 */
    line-height: 1.5;
}

/* --- 布局组件 --- */
.container {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1rem; /* p-4 */
}

.card {
    width: 100%;
    max-width: 56rem; /* max-w-4xl */
    background-color: #ffffff; /* bg-white */
    border-radius: 0.5rem; /* rounded-lg */
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); /* shadow-xl */
    overflow: hidden;
}

/* --- 卡片头部 --- */
.card-header {
    background-color: #dc2626; /* bg-red-600 */
    color: #ffffff; /* text-white */
    padding: 1.5rem; /* p-6 */
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.error-title {
    font-size: 1.875rem; /* text-3xl */
    font-weight: 700; /* font-bold */
    display: flex;
    align-items: center;
}

.error-code {
    background-color: #b91c1c; /* bg-red-700 */
    color: #ffffff; /* text-white */
    font-size: 0.75rem; /* text-xs */
    padding: 0.25rem 0.75rem; /* px-3 py-1 */
    border-radius: 9999px; /* rounded-full */
}

.error-subtitle {
    margin-top: 0.5rem; /* mt-2 */
    color: #fecdd3; /* text-red-100 */
}

/* --- 卡片主体 --- */
.card-body {
    padding: 1.5rem; /* p-6 */
}

/* --- 错误消息框 --- */
.error-message-box {
    margin-bottom: 1.5rem; /* mb-6 */
    padding: 1rem; /* p-4 */
    background-color: #fef2f2; /* bg-red-50 */
    border-left: 4px solid #ef4444; /* border-l-4 border-red-500 */
    border-radius: 0.375rem; /* rounded */
}

.error-message-title {
    font-size: 1.125rem; /* text-lg */
    font-weight: 600; /* font-semibold */
    color: #991b1b; /* text-red-800 */
    margin-bottom: 0.5rem; /* mb-2 */
}

.error-message-content {
    color: #4b5563; /* text-gray-700 */
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; /* font-mono */
    background-color: #fafafa; /* bg-gray-50 */
    padding: 0.75rem; /* p-3 */
    border-radius: 0.375rem; /* rounded */
    overflow-x: auto;
}

/* --- 错误位置 (使用Flexbox实现8:2比例) --- */
.error-location {
    display: flex;
    flex-wrap: wrap; /* 允许在小屏幕上换行 */
    gap: 1rem; /* gap-4 */
    margin-bottom: 1.5rem; /* mb-6 */
}

.location-item {
    flex: 1; /* 基础flex值 */
    min-width: 200px; /* 在非常小的屏幕上保证最小宽度 */
    padding: 1rem; /* p-4 */
    background-color: #f9fafb; /* bg-gray-50 */
    border-radius: 0.375rem; /* rounded */
}

.location-item.file {
    flex-grow: 8; /* 文件部分占据8份 */
}

.location-item.line {
    flex-grow: 2; /* 行号部分占据2份 */
}

.location-title {
    font-size: 1.125rem; /* text-lg */
    font-weight: 600; /* font-semibold */
    color: #1f2937; /* text-gray-800 */
    margin-bottom: 0.5rem; /* mb-2 */
    display: flex;
    align-items: center;
}

.location-content {
    color: #4b5563; /* text-gray-700 */
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; /* font-mono */
    background-color: #ffffff; /* bg-white */
    padding: 0.5rem; /* p-2 */
    border-radius: 0.375rem; /* rounded */
    overflow-x: auto;
}

.location-content.line-number {
    text-align: center; /* text-center */
    width: 5rem; /* w-20 */
}

/* --- 堆栈跟踪 (可折叠部分) --- */
.stack-trace {
    border: 1px solid #e5e7eb; /* border border-gray-200 */
    border-radius: 0.5rem; /* rounded-lg */
    overflow: hidden;
}

.toggle-button {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem; /* p-4 */
    background-color: #f9fafb; /* bg-gray-50 */
    cursor: pointer;
    border: none;
    font-size: 1.125rem; /* text-lg */
    font-weight: 600; /* font-semibold */
    color: #1f2937; /* text-gray-800 */
}

.toggle-button:hover {
    background-color: #f3f4f6; /* hover:bg-gray-100 */
}

.trace-content {
    display: none; /* 默认隐藏 */
    padding: 1rem; /* p-4 */
    background-color: #f9fafb; /* bg-gray-50 */
}

.trace-list {
    list-style-type: decimal;
    list-style-position: inside; /* list-inside */
    margin-left: 0.5rem; /* ml-2 */
}

.trace-item {
    margin-bottom: 1rem; /* space-y-4 */
    padding: 0.75rem; /* p-3 */
    background-color: #ffffff; /* bg-white */
    border-radius: 0.375rem; /* rounded */
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* shadow-sm */
    border-left: 2px solid #a855f7; /* border-l-2 border-purple-400 */
}

.trace-details {
    display: grid;
    grid-template-columns: 3fr 1fr; /* 在大屏幕上分为两列 */
    gap: 0.5rem; /* gap-2 */
    font-size: 0.875rem; /* text-sm */
}

.trace-file {
    grid-column: span 2; /* 文件信息占满两列 */
    font-size: 0.75rem; /* text-xs */
}

/* --- 卡片底部 --- */
.card-footer {
    background-color: #f9fafb; /* bg-gray-50 */
    padding: 1rem; /* p-4 */
    border-top: 1px solid #e5e7eb; /* border-t border-gray-200 */
    text-align: center; /* text-center */
}

.footer-links {
    color: #4b5563; /* text-gray-600 */
    font-size: 0.875rem; /* text-sm */
}

.footer-link {
    color: #2563eb; /* text-blue-500 */
    font-weight: 500; /* font-medium */
    text-decoration: none;
}

.footer-link:hover {
    color: #1d4ed8; /* hover:text-blue-700 */
}

.footer-divider {
    margin: 0 0.5rem; /* mx-2 */
    color: #d1d5db; /* text-gray-400 */
}

/* --- 页脚 --- */
.page-footer {
    margin-top: 1.5rem; /* mt-6 */
    color: #6b7280; /* text-gray-500 */
    font-size: 0.75rem; /* text-sm */
}

/* --- 自定义图标 (使用伪元素) --- */
.icon {
    margin-right: 0.75rem; /* mr-3 或 mr-2 */
    font-size: 1.25rem;
}

.icon-exclamation::before {
    content: \"⚠\";
}

.icon-file::before {
    content: \"📄\";
}

.icon-line::before {
    content: \"🔢\";
}

.icon-history::before {
    content: \"📜\";
}

.icon-refresh::before {
    content: \"↻\";
}

.icon-home::before {
    content: \"⌂\";
}

.icon-chevron::before {
    content: \"▼\";
    transition: transform 0.3s ease;
}

.icon-chevron.rotate::before {
    transform: rotate(180deg);
}

</style>
</head>
<body>
    <div class=\"container\">
        <!-- 错误卡片 -->
        <div class=\"card\">
            <!-- 卡片头部 - 红色背景 -->
            <div class=\"card-header\">
                <div class=\"header-content\">
                    <h1 class=\"error-title\">
                        <span class=\"icon icon-exclamation\"></span>
                        500 - Internal Server Error
                    </h1>
                    <span class=\"error-code\">
                        {{ exception.code }}
                    </span>
                </div>
                <p class=\"error-subtitle\">
                    Sorry, something went wrong on the server.
                </p>
            </div>

            <!-- 卡片主体 -->
            <div class=\"card-body\">
                <!-- 错误消息 -->
                <div class=\"error-message-box\">
                    <h2 class=\"error-message-title\">Error Message</h2>
                    <p class=\"error-message-content\">
                        {{ exception.message }}
                    </p>
                </div>

                <!-- 错误位置 (使用Flexbox实现8:2比例) -->
                <div class=\"error-location\">
                    <div class=\"location-item file\">
                        <h2 class=\"location-title\">
                            <span class=\"icon icon-file\"></span> File
                        </h2>
                        <p class=\"location-content\">
                            {{ exception.file }}
                        </p>
                    </div>
                    <div class=\"location-item line\">
                        <h2 class=\"location-title\">
                            <span class=\"icon icon-line\"></span> Line
                        </h2>
                        <p class=\"location-content line-number\">
                            {{ exception.line }}
                        </p>
                    </div>
                </div>

                <!-- 错误追踪 (Trace) - 可折叠 -->
                <div class=\"stack-trace\">
                    <button id=\"toggleTrace\" class=\"toggle-button\">
                        <h2 class=\"location-title\">
                            <span class=\"icon icon-history\"></span>
                            Stack Trace
                        </h2>
                        <span id=\"traceIcon\" class=\"icon icon-chevron\"></span>
                    </button>
                    <div id=\"traceContent\" class=\"trace-content\">
                        <ol class=\"trace-list\">
                            {% for trace in exception.trace %}
                                <li class=\"trace-item\">
                                    <div class=\"trace-details\">
                                        {% if trace.class %}
                                            <div>
                                                <strong>Class:</strong> {{ trace.class }}
                                            </div>
                                            <div>
                                                <strong>Function:</strong> {{ trace.function }}
                                            </div>
                                        {% else %}
                                            <div>
                                                <strong>Function:</strong> {{ trace.function }}
                                            </div>
                                        {% endif %}
                                        <div class=\"trace-file\">
                                            <strong>File:</strong> {{ trace.file }}:{{ trace.line }}
                                        </div>
                                    </div>
                                </li>
                            {% endfor %}
                        </ol>
                    </div>
                </div>
            </div>

            <!-- 卡片底部 -->
            <div class=\"card-footer\">
                <p class=\"footer-links\">
                    <a href=\"#\" class=\"footer-link\">
                        <span class=\"icon icon-refresh\"></span> Refresh Page
                    </a>
                    <span class=\"footer-divider\">|</span>
                    <a href=\"/\" class=\"footer-link\">
                        <span class=\"icon icon-home\"></span> Return to Home
                    </a>
                </p>
            </div>
        </div>

        <!-- 页脚 -->
        <p class=\"page-footer\">
            {{ 'now'|date('Y') }} Your Application Name. All rights reserved.
        </p>
    </div>

    <!-- 内联JavaScript用于折叠功能 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.getElementById('toggleTrace');
            const traceContent = document.getElementById('traceContent');
            const traceIcon = document.getElementById('traceIcon');

            toggleButton.addEventListener('click', function() {
                // 切换内容的显示/隐藏
                if (traceContent.style.display === 'block') {
                    traceContent.style.display = 'none';
                    traceIcon.classList.remove('rotate');
                } else {
                    traceContent.style.display = 'block';
                    traceIcon.classList.add('rotate');
                }
            });
        });
    </script>
</body>
</html>", "errors/500.html.twig", "C:\\Users\\Administrator\\Desktop\\project-root\\NovaPHP0.0.9\\resource\\view\\errors\\500.html.twig");
    }
}
