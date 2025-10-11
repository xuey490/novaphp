<?php

namespace App\Models;

use think\Model;

class Admin extends Model
{
    // 移除这行，让 ORM 自动根据模型类名推断表名并应用前缀
    // protected $table = 'admin';
    
    protected $pk = 'id'; // 主键名称
    
    // 可选：如果你需要自定义一些配置
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
}
