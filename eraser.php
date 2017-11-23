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

function jsonKeyClear($json)
{  //去掉jsonKey的引号，适应js数据格式
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

            $song = ['title' => $name, 'author' => $artist, 'url' => $url, 'pic' => $pic, 'lrc' => $lrc];

            array_push($songList, $song);
        }

        echo jsonKeyClear(json_encode($songList));

        recordHotSearch($s);    //搜索词记录

        break;
    case 'hotSearch':  //获取热搜词
        if(empty($max = getParam('max'))){ //显示的关键词数量
            $max = 10;
        }

        $jsonHotSearch = hotSearch();
        if (!empty($jsonHotSearch)) {
            $arrHotSearch = json_decode($jsonHotSearch, true);  //解析为数组格式
            arsort($arrHotSearch);  //按从多到少排序
            $arrHotSearch = array_keys($arrHotSearch);  //将关键词（键）保存为新数组

            $hotWordNum = count($arrHotSearch);
            for ($i = 0; $i < ($hotWordNum > $max ? $max : $hotWordNum); $i++) {    //最多显示$max个热搜词
                echo "<li>{$arrHotSearch[$i]}</li>";
            }
        } else {  //文件为空
            echo "<li>无</li>";
        }

        break;
    default:
        echo '橡皮音乐-接口未匹配';
}

function getParam($key, $value = '')
{
    return trim($key && is_string($key) ? (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $value)) : $value);
}

function recordHotSearch($hotWord)
{
    $jsonHotSearch = hotSearch();

    if (!empty($jsonHotSearch)) {
        $arrHotSearch = json_decode($jsonHotSearch, true);  //解析为数组格式
        if (array_key_exists($hotWord, $arrHotSearch)) { //有记录则加一
            $arrHotSearch[$hotWord] += 1;
        } else {  //无记录则在数组中创建
            $arrHotSearch[$hotWord] = 1;
        }

        $jsonHotSearch = json_encode($arrHotSearch);

        hotSearch($jsonHotSearch);
    } else {  //文件为空
        $arrHotSearch = [$hotWord => 1];
        $jsonHotSearch = json_encode($arrHotSearch);
        hotSearch($jsonHotSearch);
    }
}

function hotSearch($new = '')
{
    $filePath = "hotSearch.txt";

    if (file_exists($filePath)) {
        $fp = fopen($filePath, "r");
        $str = fread($fp, filesize($filePath));     //指定读取大小，这里把整个文件内容读取出来

        if (empty($new)) {  //$new为空时是读取状态，不为空时为写入状态
            fclose($fp);

            return $str;
        } else {
            $fp = fopen($filePath, "w");
            flock($fp, LOCK_EX);
            fwrite($fp, $new);
            flock($fp, LOCK_UN);
            fclose($fp);

            return true;
        }
    }
    return false;
}