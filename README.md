使用`Composer`安装`icarexm`的文件处理类库：

~~~
composer require icarexm/file:dev-master

~~~

## 上传文件

> 上传方式有两种可选，上传至本地服务器或者七牛云，开发者根据自己需求选择

假设表单代码如下：

~~~
<input type="file" name="image" />

~~~

然后在控制器中添加如下的代码：

~~~
    // {date}代表目录占位符，image代表上传表单的name值
    $upload = new icarexm\file\Upload(ROOT_PATH);
    $file = $upload->putFile('uploads/{date}', 'image');
    //获取文件完整路径
    echo $file->getPathname();
    //获取文件相对路径
    echo $file->getSrcname();

~~~


默认情况下是上传到本地服务器，以微秒时间的`md5`编码为文件名的文件，例如上面生成的文件名可能是：

~~~
/uploads/2020-04-23/783b566c940ff4a95888a9ef1d9413.png

~~~

`{date}`变量就是`2020-04-23`目录的站位符，你可以动态的改变上传目录，占位符如下：
| 规则 | 描述 |
| --- | --- |
| {type} | 文件类型 |
| {time} | 时间搓 |
| {md5_time} | md5后的时间搓 |
| {date} | 日期 |


## 上传文件到七牛云
假设表单代码如下：

~~~
<input type="file" name="image" />

~~~

然后在控制器中添加如下的代码：

~~~
    //上传文件到本地
    $upload = new icarexm\file\Upload(ROOT_PATH);
    $file = $upload->putFile('uploads/{date}', 'image');
    //获取文件完整路径
    $pathname = $file->getPathname();
    //设置七牛配置信息并上传
    $qiniuUpload = new icarexm\file\QiuniuUpload();
    $qiniu = $qiniuUpload->setConfig([
        'access'    => '***',
        'secret'    => '***',
        'bucket'    => '***',
        'domain'    => '***',
    ])->upload($pathname);
    //七牛文件名称
    echo $qiniu->getPathname();
    //七牛资源访问路径
    echo $qiniu->getSrcname();

~~~

