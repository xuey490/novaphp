<?php
// Framework/Translation/TranslationService.php

namespace Framework\Translation;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\RequestStack;

class TranslationService
{
    private Translator $translator;

    public function __construct(
        private RequestStack $requestStack,
        private string $translationDir
    ) {
        $this->translator = $this->buildTranslator();
    }

    private function buildTranslator(): Translator
    {
        $request = $this->requestStack->getCurrentRequest();
        $supported = ['en', 'zh_CN', 'zh_TW', 'ja'];

        if ($request) {
            $lang = $request->get('lang');
            if ($lang && in_array($lang, $supported)) {
                $locale = $lang;
                setcookie('user_locale', $locale, time() + 3600 * 24 * 30, '/', '', false, true);
            } elseif (isset($_COOKIE['user_locale']) && in_array($_COOKIE['user_locale'], $supported)) {
                $locale = $_COOKIE['user_locale'];
            } else {
                $locale = $request->getPreferredLanguage($supported) ?: 'en';
            }
        } else {
            $locale = 'en';
        }
		// 可选：将 locale 存入 request attributes，便于后续使用
		$request->attributes->set('_locale', $locale);

        $translator = new Translator($locale);
        $loader = new YamlFileLoader();
        $translator->addLoader('yaml', $loader);

        foreach ($supported as $loc) {
            $file = $this->translationDir . "/messages.$loc.yaml";
            if (file_exists($file)) {
                $translator->addResource('yaml', $file, $loc);
            }
        }

        return $translator;
    }

    // 实例方法
    public function trans(string $id, array $parameters = [], string $domain = 'messages', ?string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }
	
    public function getLocale(): string
    {
        return $this->translator->getLocale();
    }
}