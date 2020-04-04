<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 20-4-4
 * Time: 下午4:48
 */

namespace XBlock\Helper\FileService;


class FileUpload
{
    public function upload()
    {
        if (($data = $this->save()) == false) return message(false, '文件上传失败');
        return message(true)->data($data);
    }

    public function remove()
    {
        $url = request('url');
        if ($url) {
            $info = parse_url($url);
            if (isset($info['path'])) {
                $file = base_path('public' . $info['path']);
                if (is_file($file)) unlink($file);
            }
        }
    }


    public function save()
    {
        $request = request();
        if (!$request->hasFile('file')) return false;
        $file = $request->file('file');
        if (!$file->isValid()) return false;
        $ext = $file->getClientOriginalExtension();
        $name = date('Y-m-d') . time() . rand(1000, 9999) . '.' . $ext;
        $savePath = '/' . 'uploads' . '/';
        $file->move('.' . $savePath, $name);
        return [
            'file_path' => $savePath . $name,
            'file_name' => $name,
            'server' => env('APP_URL'),
            'path' => env('APP_URL') . $savePath . $name
        ];
    }
}