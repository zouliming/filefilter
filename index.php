<?
    $content = "";
    $output = "";
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST)) {
        $content = trim($_POST['input']);
        /**
         * 先取得所有版本号:
         * 
         * 匹配了两种模式,甚至包含了混合模式
         * 
         * ihush-v3/tpl/main/brand_story.html    3116
         * 
         * Sending content: D:\wwwroot\ihush-v3\tpl\main\brand_story.html  
         * Completed: At revision: 3179  
         */
        preg_match_all("/(?|Completed: At revision: |.+\s+)(\d+)/", $content, $matches);
        
        $reversions = $matches[1];//版本号数组
        if($reversions){
            //过滤掉垃圾字符，节省开销
            $content = preg_replace(
                array(
                    '/Sending content: D:\\\\wwwroot\\\\/',
                    '/D:\\\\wwwroot\\\\/',
                    '/Sending content: /',
                    '/\\\/'
                ),
                array(
                    '',
                    '',
                    '',
                    '/'
                ), $content);

            $contentArray = preg_split("/\s*(?|Completed: At revision: |\s+)\s*\d+\s*/", $content,-1,PREG_SPLIT_NO_EMPTY);
            $allSnippet = array();
            foreach ($contentArray as $key => $value) {
                $snippet = preg_split("/\s+/", $value,-1,PREG_SPLIT_NO_EMPTY);
                foreach ($snippet as $filepath) {
                    $allSnippet[$reversions[$key]][] = $filepath;
                }
            }
            ksort($allSnippet);
            $outputArray = getMaxVersion($allSnippet);
            foreach ($outputArray as $file => $maxVersion) {
                $output.=$file." ".$maxVersion."\r";
            }
        }
    }
/**
 * 将以版本号为key,文件列表为值的数组转换成以文件列表为key，最大版本号为值的数组
 * @param  [array] $reversionFiles 例如；array(103=>array(a.php,b.php),104=>array(a.php))
 * @return [array] 例如；array('a.php'=>104,'b.php'=>103)
 */
function getMaxVersion($reversionFiles){
    $outputArray = array();
    foreach ($reversionFiles as $reversion => $files) {
        foreach ($files as $key => $filename) {
            if(array_key_exists($filename, $outputArray)){
                if($reversion>$outputArray[$filename]){
                    $outputArray[$filename] = $reversion;
                }
            }else{
                $outputArray[$filename] = $reversion;
            }
        }
    }
    return $outputArray;
}
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="Keywords" content="处理版本列表">
	<title>版本列表优化程序</title>
    <style type="text/css">
        html{
            background: url("glow.png") no-repeat scroll center center #0D7BD5;
            height: 100%;
            color: white;
        }
        #container h1{
            font:30px Microsoft Yahei;
        }
        .mark{
            cursor: pointer;
            position: absolute;
            right: 40px;
            top: 30px;
        }
        .notice {
            background: none repeat scroll 0 0 rgba(0, 0, 0, 0.1);
            border-radius: 50px 50px 50px 50px;
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.15), 0 1px 3px rgba(0, 0, 0, 0.2) inset;
            color: rgba(255, 255, 255, 0.8);
            display: inline-block;
            font-size: 14px;
            margin-bottom: 5px;
            padding: 3px 15px;
            text-decoration: none;
        }
        #message{
            display: none;
            position: fixed;
            right: 30px;
            top: 70px;
            width: 400px;
            height: 200px;
            background: none repeat scroll 0 0 rgba(0, 0, 0, 0.2);
            padding: 10px;
            overflow: hidden;
			border-radius:7px;
        }
        #input{
            width: 600px;
            height: 320px;
        }
        #output{
            width: 600px;
            height: 320px;
        }
        .submit{
            height:100px;
            width:200px;
            font-size: 20px;
            font-family: Microsoft yahei;
        }
    </style>
</head>
<body>
<div id="container">
    <h1>处理文件列表版本号程序</h1>
    <div class="mark" onclick="toggleNotice()">
        <a class="notice" href="#">通知</a>
    </div>
    <div id="message">
        已经支持了这两种模式,混合也支持;<br/>
        ihush-v3/tpl/main/brand_story.html    3178<br/>
        <br/>
        ihush-v3/tpl/main/brand_story.html  <br/>
        Completed: At revision: 3179<br/>
    </div>
    <form action="" method="post" onsubmit="return checkInput()">
        <textarea name="input" id="input" cols="80" rows="20" style="vertical-align:middle;"><?=$content?></textarea>
        <input type="submit" value="开始处理" class="submit">
    </form>
    <br/>
    处理结果：<br/>
    <textarea id="output"><?=$output?></textarea>
</div>
<script type="text/javascript" language="JavaScript">
    function checkInput(){
        var inputContent = document.getElementById("input").value;
        if(inputContent==""){
            alert("你啥子都没写?我处理个毛线啊!");
            return false;
        }else{
            return true;
        }
    }
    function toggleNotice(){
        var max = 30;
        var min = -430;
        var speed = 4;
        var seperate = 60;
        var ele = document.getElementById("message");
        var displayAttr = getCurrentStyle(ele,"display")
        var rightPx = parseInt(getCurrentStyle(ele,"right"));
        var width = parseInt(getCurrentStyle(ele,"width"));
        if(displayAttr=="none" || displayAttr==""){
            var path = min;
            ele.style.display = "block";
            var time1 = setInterval(function(){
                path+=speed;
                if(path>=max){
                    clearInterval(time1);
                }
                if(path>=min+seperate){
                    speed = 18;
                }
                ele.style.right = path+"px";
            },1);
        }else{
            var path = max;
            var time2 = setInterval(function(){
                path-=speed;
                if(path<=min){
                    ele.style.display = "none";
                    clearInterval(time2);
                }
                if(path<=max-seperate){
                    speed = 18;
                }
                ele.style.right = path+"px";
            },1);
        }
    }
    /**
     * 根据Dom得到它的style样式属性值
     * @param {[type]} obj  [Dom]
     * @param {[type]} prop [属性值]
     */
    function getCurrentStyle(obj, prop){
        if (obj.currentStyle) { //IE浏览器
            return obj.currentStyle[prop];
        } else if (window.getComputedStyle) { //W3C标准浏览器
            propprop = prop.replace(/([A-Z])/g, "-$1");//将骆驼命名的第一个大写字母前加上"-"
            propprop = prop.toLowerCase();//将所有的字母都转换成小写的
            return document.defaultView.getComputedStyle(obj, null)[propprop];
        }
        return null;
    }
    window.onload = function(){
        document.getElementById("input").focus();
    }
</script>
</body>
</html>