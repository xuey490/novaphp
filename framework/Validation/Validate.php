<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-10-16
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Validation;

use Valitron\Validator as ValitronValidator;

abstract class Validate
{
    /**
     * 验证规则
     * @var array
     */
    protected $rule = [];

    /**
     * 错误消息
     * @var array
     */
    protected $message = [];

    /**
     * 场景定义
     * @var array
     */
    protected $scene = [];

    /**
     * 当前场景
     * @var string|null
     */
    protected $currentScene = null;

    /**
     * 验证数据
     * @var array
     */
    protected $data = [];

    /**
     * 最后一次验证的错误
     * @var array
     */
    protected $error = [];

    /**
     * 设置语言（可选）
     * @var string
     */
    protected $lang = 'zh-cn';


	/**
	 * 执行验证
	 *
	 * @param array $data 待验证数据
	 * @param string|null $scene 场景名
	 * @return bool
	 */
	public function check(array $data, ?string $scene = null): bool
	{
		$this->data = $data;
		$this->currentScene = $scene;

		// 获取当前场景的字段规则
		$rules = $this->getSceneRules();
		if (empty($rules)) {
			$rules = $this->rule;
		}

		// 初始化 Valitron
		ValitronValidator::lang($this->lang);
		$v = new ValitronValidator($data, array_keys($rules));

		// 注册自定义规则（如 mobile, idcard, confirmed 等）
		$this->registerCustomRules($v);

		// 规则别名映射（ThinkPHP → Valitron）
		$ruleAlias = [
			// 基础
			'require'      => 'required',
			'number'       => 'numeric',
			'integer'      => 'integer',
			'float'        => 'float',
			'boolean'      => 'boolean',
			'array'        => 'array',
			'accepted'     => 'accepted',

			// 字符串长度
			'max'          => 'lengthMax',	// 字符串长度最大值
			'min'          => 'lengthMin',	// 字符串长度最小值
			'length'       => 'length',

			// 数值范围（Valitron 用 max/min 表示数值）
			'maxVal'       => 'max',		// 数值最大值（可选扩展语法）
			'minVal'       => 'min',		// 数值最小值
			'between'      => 'between', // Valitron 支持 [min, max]

			// 格式
			'email'        => 'email',
			'url'          => 'url',
			'ip'           => 'ip',
			'json'         => 'json', // 我们已自定义

			// 其他 Valitron 原生支持
			'alpha'        => 'alpha',
			'slug'         => 'slug',
		];
		

		// 遍历每个字段的规则
		foreach ($rules as $field => $ruleString) {
			$ruleList = is_string($ruleString) ? explode('|', $ruleString) : (array)$ruleString;

			foreach ($ruleList as $rule) {
				if (!is_string($rule)) {
					continue;
				}

				$param = null;
				$originalRuleName = $rule; // 用于查找 $message 的键

				// 解析带参数的规则，如 max:25, between:1,120
				if (strpos($rule, ':') !== false) {
					[$ruleName, $param] = explode(':', $rule, 2);
					$originalRuleName = $ruleName;
				} else {
					$ruleName = $rule;
				}

				// 映射为 Valitron 实际规则名
				$valitronRule = $ruleAlias[$ruleName] ?? $ruleName;

				// 特殊处理 between 参数（需转为数组）
				if ($valitronRule === 'between' && $param !== null) {
					$param = array_map('trim', explode(',', $param));
				}

				// 添加规则到 Valitron
				if ($param !== null) {
					$v->rule($valitronRule, $field, $param);
				} else {
					$v->rule($valitronRule, $field);
				}

				// 绑定自定义错误消息（使用原始规则名，如 'numeric'）
				$messageKey = $field . '.' . $originalRuleName;
				if (isset($this->message[$messageKey])) {
					$v->message($this->message[$messageKey], $field, $valitronRule);
				} elseif (isset($this->message[$field])) {
					// 字段级通用消息兜底
					$v->message($this->message[$field], $field);
				}
			}
		}

		$isValid = $v->validate();
		$this->error = $v->errors();

		return $isValid;
	}



    /**
     * 获取场景规则
     */
    protected function getSceneRules(): array
    {
        if ($this->currentScene && isset($this->scene[$this->currentScene])) {
            $sceneFields = $this->scene[$this->currentScene];
            if (is_string($sceneFields)) {
                $sceneFields = explode(',', $sceneFields);
            }

            $rules = [];
            foreach ($sceneFields as $field) {
                if (isset($this->rule[$field])) {
                    $rules[$field] = $this->rule[$field];
                }
            }
            return $rules;
        }
        return [];
    }

    /**
     * 注册自定义规则（子类可重写）
     */
	protected function registerCustomRules(ValitronValidator $v): void
	{
		static $registeredRulesRules = [];

		// 手机号规则
		if (!in_array('mobile', $registeredRulesRules, true)) {
			ValitronValidator::addRule('mobile', function ($field, $value, array $params, array $fields) {
				return is_string($value) && preg_match('/^1[3-9]\d{9}$/', $value);
			});
			$registeredRulesRules[] = 'mobile';
		}

		// 身份证（简单18位校验）
		if (!in_array('idcard', $registeredRulesRules, true)) {
			ValitronValidator::addRule('idcard', function ($field, $value, array $params, array $fields) {
				return is_string($value) && preg_match('/^\d{17}[\dXx]$/', $value);
			});
			$registeredRulesRules[] = 'idcard';
		}

		// 字母、数字、下划线、中划线（类似 ThinkPHP 的 alphaDash）
		if (!in_array('alphaDash', $registeredRulesRules, true)) {
			ValitronValidator::addRule('alphaDash', function ($field, $value, array $params, array $fields) {
				return is_string($value) && preg_match('/^[\w\-]+$/', $value);
			});
			$registeredRulesRules[] = 'alphaDash';
		}
		
		// alphaNum: 字母和数字
		if (!in_array('alphaNum', $registeredRulesRules, true)) {
			ValitronValidator::addRule('alphaNum', function ($field, $value, $params, $fields) {
				return is_string($value) && ctype_alnum($value);
			});
			$registeredRulesRules[] = 'alphaNum';
		}

		// URL（Valitron 有内置 url，但可覆盖或增强）
		// 注意：Valitron 默认已有 'url' 规则，无需重复注册

		// JSON 格式
		if (!in_array('json', $registeredRulesRules, true)) {
			ValitronValidator::addRule('json', function ($field, $value, array $params, array $fields) {
				if (!is_string($value)) {
					return false;
				}
				json_decode($value);
				return json_last_error() === JSON_ERROR_NONE;
			});
			$registeredRulesRules[] = 'json';
		}
		
		// 日期格式验证：date:Y-m-d
		if (!in_array('date', $registeredRulesRules, true)) {
			ValitronValidator::addRule('date', function ($field, $value, array $params, array $fields) {
				if (!isset($params[0]) || !is_string($value)) {
					return false;
				}
				$format = $params[0];
				$dateTime = \DateTime::createFromFormat($format, $value);
				return $dateTime && $dateTime->format($format) === $value;
			});
			$registeredRulesRules[] = 'date';
		}
		
		// dateFormat:Y-m-d
		if (!in_array('dateFormat', $registeredRulesRules, true)) {
			ValitronValidator::addRule('dateFormat', function ($field, $value, $params, $fields) {
				if (!isset($params[0]) || !is_string($value)) {
					return false;
				}
				$format = $params[0];
				$date = \DateTime::createFromFormat($format, $value);
				return $date && $date->format($format) === $value;
			});
			$registeredRulesRules[] = 'dateFormat';
		}

		// 日期在某日期之后：after:2020-01-01 或 after:today
		if (!in_array('after', $registeredRulesRules, true)) {
			ValitronValidator::addRule('after', function ($field, $value, array $params, array $fields) {
				if (!is_string($value) || !isset($params[0])) {
					return false;
				}
				$target = $params[0];

				// 支持 after:today / after:now
				if ($target === 'today' || $target === 'now') {
					$targetDate = new \DateTime();
					$targetDate->setTime(0, 0, 0); // today 通常指日期部分
					if ($target === 'now') {
						$targetDate = new \DateTime(); // 精确到秒
					}
				} else {
					// 尝试解析为日期（支持 Y-m-d, Y/m/d 等）
					$targetDate = \DateTime::createFromFormat('Y-m-d', $target);
					if (!$targetDate) {
						$targetDate = \DateTime::createFromFormat('Y/m/d', $target);
					}
					if (!$targetDate) {
						// 尝试通用解析
						$targetDate = new \DateTime($target);
					}
				}

				$valueDate = new \DateTime($value);
				return $valueDate > $targetDate;
			});
			$registeredRulesRules[] = 'after';
		}

		// 密码确认：confirmed（检查 field_confirmation 是否存在且相等）
		// confirm: 等价于 confirmed
		if (!in_array('confirm', $registeredRulesRules, true)) {
			ValitronValidator::addRule('confirm', function ($field, $value, $params, $fields) {
				$other = $params[0] ?? $field . '_confirm';
				return isset($fields[$other]) && $fields[$other] === $value;
			});
			$registeredRulesRules[] = 'confirm';
		}

		// confirmed: password_confirmation
		if (!in_array('confirmed', $registeredRulesRules, true)) {
			ValitronValidator::addRule('confirmed', function ($field, $value, $params, $fields) {
				$confirmation = $field . '_confirmation';
				return isset($fields[$confirmation]) && $fields[$confirmation] === $value;
			});
			$registeredRulesRules[] = 'confirmed';
		}

		// in:a,b,c
		if (!in_array('in', $registeredRulesRules, true)) {
			ValitronValidator::addRule('in', function ($field, $value, $params, $fields) {
				if (empty($params)) return false;
				$allowed = is_array($params[0]) ? $params[0] : explode(',', $params[0]);
				return in_array($value, $allowed, true);
			});
			$registeredRulesRules[] = 'in';
		}

		// notIn:a,b,c
		if (!in_array('notIn', $registeredRulesRules, true)) {
			ValitronValidator::addRule('notIn', function ($field, $value, $params, $fields) {
				if (empty($params)) return true;
				$disallowed = is_array($params[0]) ? $params[0] : explode(',', $params[0]);
				return !in_array($value, $disallowed, true);
			});
			$registeredRulesRules[] = 'notIn';
		}

		// different:field
		if (!in_array('different', $registeredRulesRules, true)) {
			ValitronValidator::addRule('different', function ($field, $value, $params, $fields) {
				if (!isset($params[0]) || !isset($fields[$params[0]])) {
					return false;
				}
				return $value !== $fields[$params[0]];
			});
			$registeredRulesRules[] = 'different';
		}

		// same:field
		if (!in_array('same', $registeredRulesRules, true)) {
			ValitronValidator::addRule('same', function ($field, $value, $params, $fields) {
				if (!isset($params[0]) || !isset($fields[$params[0]])) {
					return false;
				}
				return $value === $fields[$params[0]];
			});
			$registeredRulesRules[] = 'same';
		}

		// requireIf:field,value
		if (!in_array('requireIf', $registeredRulesRules, true)) {
			ValitronValidator::addRule('requireIf', function ($field, $value, $params, $fields) {
				if (count($params) < 2) return true; // 不满足条件时不验证
				[$conditionField, $expectedValue] = $params;
				if (isset($fields[$conditionField]) && $fields[$conditionField] == $expectedValue) {
					return $value !== null && $value !== ''; // 必须存在且非空
				}
				return true; // 条件不满足，跳过
			});
			$registeredRulesRules[] = 'requireIf';
		}

	}
    /**
     * 获取错误信息
     */
    public function getError(): array
    {
        return $this->error;
    }

    /**
     * 设置语言
     */
    public function lang(string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }
}