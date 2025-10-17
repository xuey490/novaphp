<?php

// src/Validate/Validation.php

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
			'require' => 'required',
			'number'  => 'numeric',
			'max'     => 'lengthMax',   // 字符串长度最大值
			'min'     => 'lengthMin',   // 字符串长度最小值
			'maxVal'  => 'max',         // 数值最大值（可选扩展语法）
			'minVal'  => 'min',         // 数值最小值
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
		static $registeredRules = [];

		// 手机号规则
		if (!in_array('mobile', $registeredRules, true)) {
			ValitronValidator::addRule('mobile', function ($field, $value, array $params, array $fields) {
				return is_string($value) && preg_match('/^1[3-9]\d{9}$/', $value);
			});
			$registeredRules[] = 'mobile';
		}

		// 身份证（简单18位校验）
		if (!in_array('idcard', $registeredRules, true)) {
			ValitronValidator::addRule('idcard', function ($field, $value, array $params, array $fields) {
				return is_string($value) && preg_match('/^\d{17}[\dXx]$/', $value);
			});
			$registeredRules[] = 'idcard';
		}

		// 字母、数字、下划线、中划线（类似 ThinkPHP 的 alphaDash）
		if (!in_array('alphaDash', $registeredRules, true)) {
			ValitronValidator::addRule('alphaDash', function ($field, $value, array $params, array $fields) {
				return is_string($value) && preg_match('/^[\w\-]+$/', $value);
			});
			$registeredRules[] = 'alphaDash';
		}

		// URL（Valitron 有内置 url，但可覆盖或增强）
		// 注意：Valitron 默认已有 'url' 规则，无需重复注册

		// JSON 格式
		if (!in_array('json', $registeredRules, true)) {
			ValitronValidator::addRule('json', function ($field, $value, array $params, array $fields) {
				if (!is_string($value)) {
					return false;
				}
				json_decode($value);
				return json_last_error() === JSON_ERROR_NONE;
			});
			$registeredRules[] = 'json';
		}
		
		// 日期格式验证：date:Y-m-d
		if (!in_array('date', $registeredRules, true)) {
			ValitronValidator::addRule('date', function ($field, $value, array $params, array $fields) {
				if (!isset($params[0]) || !is_string($value)) {
					return false;
				}
				$format = $params[0];
				$dateTime = \DateTime::createFromFormat($format, $value);
				return $dateTime && $dateTime->format($format) === $value;
			});
			$registeredRules[] = 'date';
		}

		// 日期在某日期之后：after:2020-01-01 或 after:today
		if (!in_array('after', $registeredRules, true)) {
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
			$registeredRules[] = 'after';
		}

		// 密码确认：confirmed（检查 field_confirmation 是否存在且相等）
		if (!in_array('confirmed', $registeredRules, true)) {
			ValitronValidator::addRule('confirmed', function ($field, $value, array $params, array $fields) {
				$confirmationField = $field . '_confirmation';
				return isset($fields[$confirmationField]) && $fields[$confirmationField] === $value;
			});
			$registeredRules[] = 'confirmed';
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