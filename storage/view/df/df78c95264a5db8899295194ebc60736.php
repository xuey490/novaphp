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

/* upload/index.html.twig */
class __TwigTemplate_ffcfc286539c70efa0164b5c7d9f7a4b extends Template
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
        // line 1
        yield "<form action=\"/home/upload\" enctype=\"multipart/form-data\" method=\"post\">

";
        // line 3
        yield $this->extensions['App\Twig\AppTwigExtension']->renderCsrfField();
        yield "

";
        // line 6
        yield "
<input type=\"text\" name=\"title\" /> <br> 
<input type=\"file\" name=\"image\" /> <br> 
<input type=\"submit\" value=\"上传\" /> 
</form> ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "upload/index.html.twig";
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
        return array (  51 => 6,  46 => 3,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<form action=\"/home/upload\" enctype=\"multipart/form-data\" method=\"post\">

{{ csrf_field() }}

{# <input type='hidden' name='_token' value='{{token}}'> #}

<input type=\"text\" name=\"title\" /> <br> 
<input type=\"file\" name=\"image\" /> <br> 
<input type=\"submit\" value=\"上传\" /> 
</form> ", "upload/index.html.twig", "C:\\Users\\Administrator\\Desktop\\project-root\\NovaPHP0.0.9\\resource\\view\\upload\\index.html.twig");
    }
}
