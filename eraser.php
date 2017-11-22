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

function jsonKeyClear($json) {  //去掉jsonKey的引号，适应js数据格式
    $json = preg_replace('/"(\w+)"(\s*:\s*)/is', '$1$2', $json);   //去掉key的双引号
    return $json;
}

switch (getParam('type')) {
    case 'search':  // 搜索歌曲
        $s = getParam('name');  // 歌名
        $limit = getParam('count', 15);  // 每页显示数量
        $pages = getParam('pages', 1);  // 页码

        $data = $Eraser->search($s, $pages, $limit);

        $arr = json_decode($data, true);

        $songList = array();

        foreach ($arr as $value) {
            $url = json_decode($Eraser->url($value['url_id']), true)['url'];
            $pic = json_decode($Eraser->pic($value['pic_id']), true)['url'];
            $lrc = json_decode($Eraser->lrc($value['lrc_id']), true)['lyric'];

            $name = $value['name'];
            $artist = $value['artist'][0];

            $song = ['title'=>$name,'author'=>$artist,'url'=>$url,'pic'=>$pic,'lrc'=>$lrc];

            array_push($songList,$song);
        }

        echo jsonKeyClear(json_encode($songList));

        break;
    default:
        echo '橡皮音乐-接口未匹配';
}

function getParam($key, $value = '')
{
    return trim($key && is_string($key) ? (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $value)) : $value);
}