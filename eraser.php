<?php
/**
 * Created by PhpStorm.
 * User: lifanko  lee
 * Date: 2017/10/1
 * Time: 21:25
 */
require_once 'Meting.php';
use Metowolf\Meting;

$source = getParam('source', 'netease');  //源
if ($source == 'kugou' || $source == 'baidu') define('NO_HTTPS', true);    // 酷狗和百度音乐源暂不支持 https

$Eraser = new Meting($source);

$Eraser->format(true); // 启用格式化功能

switch (getParam('type')) {
    case 'lyric':       // 获取歌词
        $id = getParam('id');  // 歌曲ID
        $data = $Eraser->lyric($id);

        output($data, true);
        break;
    case 'search':  // 搜索歌曲
        $s = getParam('name');  // 歌名
        $limit = getParam('count', 8);  // 每页显示数量
        $pages = getParam('pages', 1);  // 页码

        $data = $Eraser->search($s, $pages, $limit);

        $arr = json_decode($data, true);
        if ($source == 'kugou' || $source == 'baidu' || $source == 'xiami') {
            echo "<table><tr><th>歌曲</th><th>演唱</th><th>源</th><th>封面</th></tr>";
        } else {
            echo "<table><tr><th>歌曲</th><th>演唱</th><th>源</th></tr>";
        }

        foreach ($arr as $value) {
            $url = json_decode($Eraser->url($value['id']), true)['url'];
            $pic = json_decode($Eraser->pic($value['id']), true)['url'];

            $name = $value['name'];
            $sou = $value['source'];
            $artist = $value['artist'][0];
            echo "<tr>";
            echo "<td><a href='play.php?url=$url&name=$name'>$name</a></td>";
            echo "<td>{$artist}</td>";
            echo "<td>{$sou}</td>";

            if ($source == 'kugou' || $source == 'baidu' || $source == 'xiami') {//图片解密算法改了，所以网易和QQ都获取不到图片，等待新的解密算法~
                echo "<td><img src='$pic' width='40px'></td>";
            }
            echo "</tr>";
        }
        echo "</table>";

        break;
    default:
        echo '橡皮音乐-接口未匹配';
}

function getParam($key, $value = '')
{
    return trim($key && is_string($key) ? (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $value)) : $value);
}

function output($json, $out = false)
{
    $arr = $json;
    if ($out) {
        $arr = json_decode($json, true);
        print_r($arr);
    }

    echo $arr;
}