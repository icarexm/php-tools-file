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

use \Exception;

class Request
{

    /**
     * 获取上传的文件信息
     * @access public
     * @param  string $name 名称
     * @return null|array|UploadedFile
     */
    public static function file($name = '')
    {
        $files = &$_FILES;
        if (!empty($files)) {

            if (strpos($name, '.')) {
                list($name, $sub) = explode('.', $name);
            }

            // 处理上传文件
            $array = self::dealUploadFile($files, $name);

            if ('' === $name) {
                // 获取全部文件
                return $array;
            } elseif (isset($sub) && isset($array[$name][$sub])) {
                return $array[$name][$sub];
            } elseif (isset($array[$name])) {
                return $array[$name];
            }
        }
    }

    protected static function dealUploadFile($files, $name)
    {
        $array = [];
        foreach ($files as $key => $file) {
            if (is_array($file['name'])) {
                $item  = [];
                $keys  = array_keys($file);
                $count = count($file['name']);

                for ($i = 0; $i < $count; $i++) {
                    if ($file['error'][$i] > 0) {
                        if ($name == $key) {
                            self::throwUploadFileError($file['error'][$i]);
                        } else {
                            continue;
                        }
                    }

                    $temp['key'] = $key;

                    foreach ($keys as $_key) {
                        $temp[$_key] = $file[$_key][$i];
                    }

                    $item[] = new UploadedFile($temp['tmp_name'], $temp['name'], $temp['type'], $temp['error']);
                }

                $array[$key] = $item;
            } else {

                if ($file['error'] > 0) {
                    if ($key == $name) {
                        self::throwUploadFileError($file['error']);
                    } else {
                        continue;
                    }
                }

                $array[$key] = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['error']);

            }
        }

        return $array;
    }

    protected static function throwUploadFileError($error)
    {
        static $fileUploadErrors = [
            1 => 'upload File size exceeds the maximum value',
            2 => 'upload File size exceeds the maximum value',
            3 => 'only the portion of file is uploaded',
            4 => 'no file to uploaded',
            6 => 'upload temp dir not found',
            7 => 'file write error',
        ];

        $msg = $fileUploadErrors[$error];
        throw new Exception($msg, $error);
    }
}