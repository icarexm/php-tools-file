<?php
// +----------------------------------------------------------------------
// | zaihukeji [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2020 http://icarexm.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MrYe    <email：55585190@qq.com>
// +----------------------------------------------------------------------

namespace icarexm\file\upload;

use SplFileInfo;
use \Exception;

/**
 * 文件上传类
 * @package think
 */
class File extends SplFileInfo
{

    /**
     * 文件hash规则
     * @var array
     */
    protected $hash = [];

    protected $hashName;

    protected $rootPath;

    public function __construct($path, $checkPath = true, $rootPath = null)
    {
        if ($checkPath && !is_file($path)) {
            throw new Exception(sprintf('The file "%s" does not exist', $path));
        }

        $this->rootPath = $rootPath;

        parent::__construct($path);
    }

    /**
     * 获取文件的哈希散列值
     * @access public
     * @param string $type
     * @return string
     */
    public function hash($type = 'sha1')
    {
        if (!isset($this->hash[$type])) {
            $this->hash[$type] = hash_file($type, $this->getPathname());
        }

        return $this->hash[$type];
    }

    /**
     * 获取文件的MD5值
     * @access public
     * @return string
     */
    public function md5()
    {
        return $this->hash('md5');
    }

    /**
     * 获取文件的SHA1值
     * @access public
     * @return string
     */
    public function sha1()
    {
        return $this->hash('sha1');
    }

    /**
     * 获取文件类型信息
     * @access public
     * @return string
     */
    public function getMime()
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        return finfo_file($finfo, $this->getPathname());
    }

    /**
     * 移动文件
     * @access public
     * @param string      $directory 保存路径
     * @param string|null $name      保存的文件名
     * @return File
     */
    public function move($directory, $name = null)
    {
        $target = $this->getTargetFile($directory, $name);

        set_error_handler(function ($type, $msg) use (&$error) {
            $error = $msg;
        });
        $renamed = rename($this->getPathname(), (string) $target);
        restore_error_handler();
        if (!$renamed) {
            throw new Exception(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $target, strip_tags($error)));
        }

        @chmod((string) $target, 0666 & ~umask());

        return $target;
    }

    /**
     * 实例化一个新文件
     * @param string      $directory
     * @param null|string $name
     * @param string|null $rootPath  根路径
     * @return File
     */
    protected function getTargetFile($directory, $name = null, $rootPath = null)
    {

        if (!is_dir($directory)) {
            if (false === @mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new Exception(sprintf('Unable to create the "%s" directory', $directory));
            }
        } elseif (!is_writable($directory)) {
            throw new Exception(sprintf('Unable to write in the "%s" directory', $directory));
        }

        $target = rtrim($directory, '/\\') . \DIRECTORY_SEPARATOR . (null === $name ? $this->getBasename() : $this->getName($name));

        return new self($target, false, $rootPath);
    }

    /**
     * 获取文件名
     * @param string $name
     * @return string
     */
    protected function getName($name)
    {
        $originalName = str_replace('\\', '/', $name);
        $pos          = strrpos($originalName, '/');
        $originalName = false === $pos ? $originalName : substr($originalName, $pos + 1);

        return $originalName;
    }

    /**
     * 文件扩展名
     * @return string
     */
    public function extension()
    {
        return $this->getExtension();
    }

    /**
     * 自动生成文件名
     * @access protected
     * @param string|\Closure $rule
     * @return string
     */
    public function hashName($rule = 'date')
    {
        if (!$this->hashName) {
            if ($rule instanceof \Closure) {
                $this->hashName = call_user_func_array($rule, [$this]);
            } else {
                switch (true) {
                    case in_array($rule, hash_algos()):
                        $hash           = $this->hash($rule);
                        $this->hashName = substr($hash, 0, 2) . DIRECTORY_SEPARATOR . substr($hash, 2);
                        break;
                    case is_callable($rule):
                        $this->hashName = call_user_func($rule);
                        break;
                    default:
                        if(strpos($rule, '.') !== false) {
                            //带后缀的规则名称
                            $this->hashName = $rule;
                        } else {
                            //不带任何规则的名称
                            $this->hashName = date('Ymd') . DIRECTORY_SEPARATOR . md5((string) microtime(true));
                        }
                        break;
                }
            }
        }

        return strpos($rule, '.') !== false ? $this->hashName : $this->hashName . '.' . $this->extension();
    }

    /**
     * 获取相对路径
     * @return bool
     */
    public function getSrcname($rootPath = null)
    {

        $rootPath = !empty($rootPath) ? $rootPath : $this->rootPath;
        $pathName = $this->getPathname();
        if(empty($pathName)) {
            return false;
        }
        //解析path
        $pathArr = explode($rootPath, $pathName);
        list(, $srcName) = $pathArr;

        return '/'.$srcName;
    }
}
