<?php

// Framework/Translation/TransHelper.php

namespace Framework\Translation;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\RequestStack;

class TransHelper
{
    private RequestStack $requestStack;
    private string $translationDir;
    private ?Translator $translator = null;

    public function __construct(RequestStack $requestStack, string $translationDir)
    {
        $this->requestStack = $requestStack;
        $this->translationDir = $translationDir;
    }

    private function getTranslator(): Translator
    {
        if ($this->translator === null) {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request) {
                // 安全 fallback：没有请求时用默认语言
                $locale = 'zh_CN';
            } else {
                $supported = ['en', 'zh_CN', 'zh_TW', 'ja'];
                $lang = $request->query->get('lang');
                if ($lang && in_array($lang, $supported)) {
                    $locale = $lang;
                    setcookie('user_locale', $locale, time() + 3600 * 24 * 30, '/', '', false, true);
                } elseif (isset($_COOKIE['user_locale']) && in_array($_COOKIE['user_locale'], $supported)) {
                    $locale = $_COOKIE['user_locale'];
                } else {
                    $locale = $request->getPreferredLanguage($supported) ?: 'en';
                }
            }

            $this->translator = new Translator($locale);
            $loader = new YamlFileLoader();
            $this->translator->addLoader('yaml', $loader);

            foreach (['en', 'zh_CN', 'zh_TW', 'ja'] as $loc) {
                $file = $this->translationDir . '/messages.' . $loc . '.yaml';
                if (file_exists($file)) {
                    $this->translator->addResource('yaml', $file, $loc);
                }
            }
        }

        return $this->translator;
    }

    public function trans(string $id, array $parameters = []): string
    {
        return $this->getTranslator()->trans($id, $parameters);
    }

    public function getLocale(): string
    {
        return $this->getTranslator()->getLocale();
    }
}