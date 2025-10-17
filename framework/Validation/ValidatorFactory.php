<?php

namespace Framework\Validation;

use Valitron\Validator;

class ValidatorFactory
{
    public function create(array $data = [], array $fields = [], string $lang = 'en'): Validator
    {
        if ($lang === 'zh-cn') {
            Validator::lang('zh-cn');
        }

        return new Validator($data, $fields, $lang);
    }
}