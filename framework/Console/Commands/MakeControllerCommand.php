<?php

namespace Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class MakeControllerCommand extends Command
{
    /**
     * 命令名称 - 必须定义
     * 这是修复错误的关键
     */
    protected static $defaultName = 'make:controller';
    
    /**
     * 命令描述
     */
    protected static $defaultDescription = '创建一个新的控制器类';

    /**
     * 配置命令
     */
    protected function configure(): void
    {
        $this
            // 命令名称（冗余定义，确保兼容性）
            ->setName(self::$defaultName)
            // 命令描述
            ->setDescription(self::$defaultDescription)
            // 添加控制器名称参数
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                '控制器名称（例如：Users 会生成 UsersController）'
            );
    }

    /**
     * 执行命令
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $controllerBaseName = $input->getArgument('name');
        $controllerName = ucfirst($controllerBaseName) . 'Controller';
        $directory = __DIR__ . '/../../../app/Controllers';
        $filePath = $directory . '/' . $controllerName . '.php';

        // 检查文件是否已存在
        if (file_exists($filePath)) {
            $output->writeln("<error>错误：控制器 $controllerName 已存在于 $filePath</error>");
            return Command::FAILURE;
        }

        // 创建文件系统实例
        $filesystem = new Filesystem();

        try {
            // 确保目录存在
            $filesystem->mkdir($directory);

            // 生成控制器内容
            $content = $this->generateControllerContent($controllerName);

            // 写入文件
            $filesystem->dumpFile($filePath, $content);

            $output->writeln("<info>成功生成控制器：</info>");
            $output->writeln("路径：$filePath");
            return Command::SUCCESS;

        } catch (IOExceptionInterface $e) {
            $output->writeln("<error>文件操作错误：" . $e->getMessage() . "</error>");
            return Command::FAILURE;
        } catch (\Exception $e) {
            $output->writeln("<error>发生错误：" . $e->getMessage() . "</error>");
            return Command::FAILURE;
        }
    }

    /**
     * 生成控制器类内容
     */
    private function generateControllerContent(string $controllerName): string
    {
        return <<<PHP
<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class $controllerName
{
    /**
     * 显示资源列表
     */
    public function index(Request \$request): Response
    {
        // 实现列表展示逻辑
        return new Response('$controllerName index');
    }

    /**
     * 显示创建资源表单
     */
    public function create(Request \$request): Response
    {
        // 实现创建表单展示逻辑
        return new Response('$controllerName create');
    }

    /**
     * 保存新创建的资源
     */
    public function save(Request \$request): Response
    {
        // 实现资源保存逻辑
        return new Response('$controllerName save');
    }

    /**
     * 显示指定资源
     */
    public function show(Request \$request): Response
    {
        // 实现资源详情展示逻辑
        return new Response('$controllerName show');
    }

    /**
     * 显示编辑资源表单
     */
    public function edit(Request \$request): Response
    {
        // 实现编辑表单展示逻辑
        return new Response('$controllerName edit');
    }

    /**
     * 更新指定资源
     */
    public function update(Request \$request): Response
    {
        // 实现资源更新逻辑
        return new Response('$controllerName update');
    }

    /**
     * 删除指定资源
     */
    public function delete(Request \$request): Response
    {
        // 实现资源删除逻辑
        return new Response('$controllerName delete');
    }
}
PHP;
    }
}
    