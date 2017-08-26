<?php
 $data = $_POST['img'];
//$data = [
//  'name' => '董槐斌',
//  'amt' => num2rmb(200000, '圆'),
//  'year' => '2017',
//  'month' => '08',
//  'day' => '26'
//];
$src = 'timg-ep.jpg';
list($src_w, $src_h) = getimagesize($src);
$font = 'STHeiti Medium.ttc';
$image = imagecreatefromjpeg($src);
imagealphablending($image, true);
$black = imagecolorallocate($image, 0, 0, 0);
imagefttext($image, 30, 0, 15, 90, $black, $font, $data['name']);
imagefttext($image, 20, 0, 125, 178, $black, $font, num2rmb($data['amt'], "圆"));
imagefttext($image, 12, 0, 345, 315, $black, $font, $data['year']);
imagefttext($image, 12, 0, 395, 315, $black, $font, $data['month']);
imagefttext($image, 12, 0, 425, 315, $black, $font, $data['day']);

// header('Content-type: image/png');
$filename = $data['name'].'.png';
imagepng($image, $filename);
imagedestroy($image);




/**
 * 人民币小写转大写
 *
 * @param string $number 数值
 * @param string $int_unit 币种单位，默认"元"，有的需求可能为"圆"
 * @param bool $is_round 是否对小数进行四舍五入
 * @param bool $is_extra_zero 是否对整数部分以0结尾，小数存在的数字附加0,比如1960.30，
 *             有的系统要求输出"壹仟玖佰陆拾元零叁角"，实际上"壹仟玖佰陆拾元叁角"也是对的
 * @return string
 */
function num2rmb($number = 0, $int_unit = '元', $is_round = TRUE, $is_extra_zero = FALSE)
{
    // 将数字切分成两段
    $parts = explode('.', $number, 2);
    $int = isset($parts[0]) ? strval($parts[0]) : '0';
    $dec = isset($parts[1]) ? strval($parts[1]) : '';

    // 如果小数点后多于2位，不四舍五入就直接截，否则就处理
    $dec_len = strlen($dec);
    if (isset($parts[1]) && $dec_len > 2)
    {
        $dec = $is_round
                ? substr(strrchr(strval(round(floatval("0.".$dec), 2)), '.'), 1)
                : substr($parts[1], 0, 2);
    }

    // 当number为0.001时，小数点后的金额为0元
    if(empty($int) && empty($dec))
    {
        return '零';
    }

    // 定义
    $chs = array('0','壹','贰','叁','肆','伍','陆','柒','捌','玖');
    $uni = array('','拾','佰','仟');
    $dec_uni = array('角', '分');
    $exp = array('', '万');
    $res = '';

    // 整数部分从右向左找
    for($i = strlen($int) - 1, $k = 0; $i >= 0; $k++)
    {
        $str = '';
        // 按照中文读写习惯，每4个字为一段进行转化，i一直在减
        for($j = 0; $j < 4 && $i >= 0; $j++, $i--)
        {
            $u = $int{$i} > 0 ? $uni[$j] : ''; // 非0的数字后面添加单位
            $str = $chs[$int{$i}] . $u . $str;
        }
        //echo $str."|".($k - 2)."<br>";
        $str = rtrim($str, '0');// 去掉末尾的0
        $str = preg_replace("/0+/", "零", $str); // 替换多个连续的0
        if(!isset($exp[$k]))
        {
            $exp[$k] = $exp[$k - 2] . '亿'; // 构建单位
        }
        $u2 = $str != '' ? $exp[$k] : '';
        $res = $str . $u2 . $res;
    }

    // 如果小数部分处理完之后是00，需要处理下
    $dec = rtrim($dec, '0');

    // 小数部分从左向右找
    if(!empty($dec))
    {
        $res .= $int_unit;

        // 是否要在整数部分以0结尾的数字后附加0，有的系统有这要求
        if ($is_extra_zero)
        {
            if (substr($int, -1) === '0')
            {
                $res.= '零';
            }
        }

        for($i = 0, $cnt = strlen($dec); $i < $cnt; $i++)
        {
            $u = $dec{$i} > 0 ? $dec_uni[$i] : ''; // 非0的数字后面添加单位
            $res .= $chs[$dec{$i}] . $u;
        }
        $res = rtrim($res, '0');// 去掉末尾的0
        $res = preg_replace("/0+/", "零", $res); // 替换多个连续的0
    }
    else
    {
        $res .= $int_unit . '整';
    }
    return $res;
}
?>


<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=utf-8"  />

</head>
<body onload="doPrint()">
<div>
<!--startprint-->
  <img src="<?=$filename?>"  id="printer" />
<!--endprint-->

</div>
<script type="text/javascript" >


    function doPrint() {
        bdhtml=window.document.body.innerHTML;
        sprnstr="<!--startprint-->"; //开始打印标识字符串有17个字符
        eprnstr="<!--endprint-->"; //结束打印标识字符串
        prnhtml=bdhtml.substr(bdhtml.indexOf(sprnstr)+17); //从开始打印标识之后的内容
        prnhtml=prnhtml.substring(0,prnhtml.indexOf(eprnstr)); //截取开始标识和结束标识之间的内容
        window.document.body.innerHTML=prnhtml; //把需要打印的指定内容赋给body.innerHTML
        window.print(); //调用浏览器的打印功能打印指定区域
        window.document.body.innerHTML=bdhtml; // 最后还原页面
    }
</script>
</body>
</html>
