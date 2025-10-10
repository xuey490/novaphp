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

/* errors/csrf_error.html.twig */
class __TwigTemplate_44cb6c8cfd4adfe127182ee3b8aee376 extends Template
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
        yield "
<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Session Expired - Please Refresh</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;
            text-align: center; 
            padding: 50px; 
            background-color: #f8fafc;
            color: #334155;
        }
        .error-card { 
            max-width: 500px; 
            margin: 50px auto; 
            background: white; 
            padding: 2.5rem; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }
        h1 { 
            color: #ef4444; 
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .subtitle {
            font-size: 1.25rem;
            color: #64748b;
            margin-bottom: 2rem;
        }
        p { 
            font-size: 1.1rem; 
            margin-bottom: 2.5rem; 
            line-height: 1.6;
        }
        .refresh-button { 
            display: inline-block; 
            padding: 0.85rem 1.75rem; 
            background-color: #3b82f6; 
            color: white; 
            text-decoration: none; 
            border-radius: 8px; 
            font-weight: 600;
            transition: background-color 0.2s ease;
        }
        .refresh-button:hover { 
            background-color: #2563eb; 
        }
    </style>
</head>
<body>
    <div class=\"error-card\">
        <h1>Session Expired</h1>
        <div class=\"subtitle\">CSRF Token Validation Failed</div>
        <p>
            ";
        // line 60
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(($context["message"] ?? null), "html", null, true);
        yield " This usually happens if the page has been open for too long.
        </p>
        <p>
            Please refresh the page to get a new token and try your action again.
        </p>
        <a href=\"";
        // line 65
        yield ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["app"] ?? null), "request", [], "any", false, false, false, 65), "headers", [], "any", false, false, false, 65), "get", ["referer"], "method", false, false, false, 65)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["app"] ?? null), "request", [], "any", false, false, false, 65), "headers", [], "any", false, false, false, 65), "get", ["referer"], "method", false, false, false, 65), "html", null, true)) : ("/"));
        yield "\" class=\"refresh-button\">
            Refresh Page
        </a>
    </div>
</body>
</html>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "errors/csrf_error.html.twig";
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
        return array (  110 => 65,  102 => 60,  42 => 2,);
    }

    public function getSourceContext(): Source
    {
        return new Source("{# templates/errors/csrf_error.html.twig #}

<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Session Expired - Please Refresh</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, \"Helvetica Neue\", Arial, sans-serif;
            text-align: center; 
            padding: 50px; 
            background-color: #f8fafc;
            color: #334155;
        }
        .error-card { 
            max-width: 500px; 
            margin: 50px auto; 
            background: white; 
            padding: 2.5rem; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }
        h1 { 
            color: #ef4444; 
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .subtitle {
            font-size: 1.25rem;
            color: #64748b;
            margin-bottom: 2rem;
        }
        p { 
            font-size: 1.1rem; 
            margin-bottom: 2.5rem; 
            line-height: 1.6;
        }
        .refresh-button { 
            display: inline-block; 
            padding: 0.85rem 1.75rem; 
            background-color: #3b82f6; 
            color: white; 
            text-decoration: none; 
            border-radius: 8px; 
            font-weight: 600;
            transition: background-color 0.2s ease;
        }
        .refresh-button:hover { 
            background-color: #2563eb; 
        }
    </style>
</head>
<body>
    <div class=\"error-card\">
        <h1>Session Expired</h1>
        <div class=\"subtitle\">CSRF Token Validation Failed</div>
        <p>
            {{ message }} This usually happens if the page has been open for too long.
        </p>
        <p>
            Please refresh the page to get a new token and try your action again.
        </p>
        <a href=\"{{ app.request.headers.get('referer') ?: '/' }}\" class=\"refresh-button\">
            Refresh Page
        </a>
    </div>
</body>
</html>", "errors/csrf_error.html.twig", "C:\\Users\\Administrator\\Desktop\\project-root\\NovaPHP0.0.9\\resource\\view\\errors\\csrf_error.html.twig");
    }
}
