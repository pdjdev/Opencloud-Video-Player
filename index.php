<?php
setlocale(LC_ALL, 'ko_KR.UTF-8');

$urlchk = true;
$startloc = '../files/'; # 클라우드 디렉토리 시작 위치
$video = $_GET['video']; # 파일 위치


# 요청 유효성 검사

if ($video == '') {
    $urlchk = false;
} elseif ($video == '.') {
    $urlchk = false;
} elseif ($video == '..') {
    $urlchk = false;
} else {
    $chk = strpos($video, '../');
    if ($chk !== false and $chk == 0) {
        $urlchk = false;
    }

    $chk = strpos($video, '/../');
    if ($chk !== false) {
        $urlchk = false;
    }
}
if ($urlchk) {
    $urlchk = file_exists($startloc.$video);
}
if (!$urlchk) {
    echo "<script>alert('잘못된 주소 또는 삭제 처리된 영상입니다.');history.back();</script>";
    exit();
}


$nplayer = $_GET['np'];
$nplayer_c = $_COOKIE['np'];

if ($nplayer == null) { # get nplayer 값 없을때
    if ($nplayer_c == null) { # 쿠키값도 없을때
        $nplayer = 0; setcookie('np', 0, time()+3600*24*365, '/');
    } else { $nplayer = $nplayer_c; }
} else { setcookie('np', $nplayer, time()+3600*24*365, '/'); }

function endsWith($string, $endString)
{
    $len = mb_strlen($endString, 'utf-8');
    if ($len == 0) {
        return true;
    }
    return (mb_substr($string, -$len, NULL, 'utf-8') === $endString);
}



# ====================== DB(SQL)을 사용하지 않는 경우 이 파트를 지워주세요 ======================
# 코드를 지울 경우 조회수, 짧은 링크 기능을 사용하지 못합니다
# 비디오ID 체크 및 등록

$conn= mysqli_connect('localhost', '', '', '');
$floc = mysqli_real_escape_string($conn, $video);
$query = mysqli_query($conn, "SELECT id, views, last_checked
                              FROM videos WHERE file_loc = '".$floc."'");

$views = 0;
$last_chk = 'N/A';

# =============================================================================================


# 비디오 위치값 정리

# PHP를 통해 스트림을 원할 경우 아래 주석을 해제하세요
# (저사양에서는 그냥 바로 스트림하고 대역폭을 제한하는 것을 추천함)
# $video = "./stream.php?v=".$video;

$video = $startloc.$video;

$path_parts = pathinfo($video);
$currdir = mb_substr($path_parts['dirname'],  mb_strlen($startloc, 'utf-8'), NULL, 'utf-8');



# ====================== DB(SQL)을 사용하지 않는 경우 아래 코드를 지워주세요 ======================
# 비디오가 등록되어 있지 않은 경우
if (mysqli_num_rows($query) < 1) {
    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    $var_size = strlen($chars);
    $vidid = '';
    for( $x = 0; $x < 6; $x++ ) { 
        $vidid .= $chars[ rand( 0, $var_size - 1 ) ]; 
    }
    
    while(1) {
        # 우선 중복체크
        $id_check = mysqli_query($conn, "SELECT id FROM videos WHERE id = '$vidid'");
        if (mysqli_num_rows($id_check) < 1) {
            break;
        }
    }

    $sql = "INSERT INTO `videos` (`file_loc`, `id`, `views`) VALUES ('$floc', '$vidid', '1')";
    $result = mysqli_query($conn,$sql);
    $views = 1;

# 등록되어 있는 경우
} else {
    $query = mysqli_fetch_array($query);
    $vidid = $query['id'];
    $views = $query['views'] + 1;
    $last_chk = $query['last_checked'];
    $sql = "UPDATE videos SET views = IFNULL(views, 0) + 1, last_checked = NOW() WHERE id = '$vidid'";
    mysqli_query($conn,$sql);
}

# =============================================================================================


# vtt(srt) 자막이 존재하는지 체크
# (자막 파일은 동일 디렉토리의 ./SUB 폴더 안에
# 비디오 파일명과 똑같은 이름으로 vtt 형식으로 넣으시면 됩니다)
$caption = '';
if (file_exists($path_parts['dirname'].'/.SUB/'.$path_parts['filename'].'.vtt')) {
    $caption = $path_parts['dirname'].'/.SUB/'.$path_parts['filename'].'.vtt';
}



# 현재 디렉토리에서 비디오 목록 추출

$isitfirst = false;
$isitlast = false;

$files = array_values(array_filter(scandir($path_parts['dirname']), function($item) {
    return (endsWith($item, '.mp4') or endsWith($item, '.webm'));
}));


if (count($files) == 1) {

    $isitfirst = true;
    $isitlast = true;

} else {
    $order = array_search($path_parts['basename'], $files);

    if ($order == 0) {
        $isitfirst = true;
    } elseif ($order == count($files)-1) {
        $isitlast = true;
    }

}
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#1c2c3b">
    <link rel="icon" sizes="192x192" href="../img.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link href="./css/style.css?rev=0.10" rel="stylesheet">
    <script src="https://kit.fontawesome.com/b435844d6f.js" crossorigin="anonymous"></script>

    <!-- video.js 사용시 -->
    <?php
        if ($nplayer == false) {
            ?> <link href="https://vjs.zencdn.net/7.18.1/video-js.css" rel="stylesheet" /> <?php
        }
    ?>
    
    <title><?=$path_parts['filename']?></title>
  </head>
  <body>
    
    <div class="bg-black">
        
        <?php
        if ($nplayer == false) { ?>
        <div class="container" style="padding-left: 0; padding-right: 0;">
            <video id="my_video_1" class="video-js vjs-big-play-centered" width="160px" height="90px" controls controlsList="nodownload" preload="true" data-setup='{ "aspectRatio":"16:9"}' oncontextmenu="return false;" autoplay>
                <source src="<?=$video?>" type='video/mp4' />
                <?=($caption==''?'':'<track kind="subtitles" label="Caption" src="'.$caption.'" srclang="ko" default="">')?>
            </video>
            <script src="https://vjs.zencdn.net/7.18.1/video.min.js"></script>
        </div> <?php
        } else { ?>
        <div class="container ratio ratio-16x9" style="padding-left: 0; padding-right: 0;">
            <video autoplay controls oncontextmenu="return false;" controlsList="nodownload">
                <source src="<?=$video?>" type='video/mp4' />
                <?=($caption==''?'':'<track kind="subtitles" label="Caption" src="'.$caption.'" srclang="ko" default="">')?>
            </video>
        </div>
        <?php }
        ?>
        
    </div>

    <div class="main-control shadow-sm">
        <div class="container py-1">
            <div class="d-flex flex-wrap">

                <!-- 제목 -->
                <div class="p-2 fw-bolder title">
                    <span class="fs-5">
                        <?=$path_parts['filename'];?>
                    </span>
                </div>

                <!-- 컨트롤 -->
                <div class="p-2 flex-fill d-flex justify-content-end align-items-center">
                    <!-- 다음 버튼부터는 ms-2 넣기 -->
                    <span type="button" class="btn btn-sm" style="background-color: #3f3f3f; color: #cdcdcd;" disabled
                    data-bs-toggle="tooltip" data-bs-placement="bottom" title="이 영상을 열람한 횟수">
                    <i class="fa-solid fa-eye me-1"></i> <?=number_format($views)?></span>

                    <a class="btn btn-sm btn-dark ms-2" href="?video=<?=urlencode($_GET['video'])?>&np=<?=($nplayer?0:1)?>"
                    data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="bottom" title="<?php
                        if ($nplayer) {
                            echo '자체 플레이어로 재생합니다.</br><sub>PC 버전에서 추천합니다.</sub>';
                        } else {
                            echo '브라우저에 내장된 플레이어로 재생합니다.</br><sub>모바일/스트림 환경에서 추천합니다.</sub>';
                        }
                        ?>"><?php
                        if ($nplayer) {
                            echo '<i class="fa-solid fa-circle-play me-1"></i>자체 플레이어';
                        } else {
                            echo '<i class="fas fa-tv me-1"></i> 기본 플레이어';
                        }
                    ?></a>

                    <button type="button" class="btn btn-sm btn-dark ms-2"
                    onclick="copyClipboard('https://[yourserver]/videoplayer/v/?id=<?=$vidid?>')"
                    data-bs-toggle="tooltip" data-bs-placement="bottom" title="이 영상으로 들어갈 수 있는 짧은 링크를 복사합니다.">
                    <i class="fas fa-link me-1"></i> 링크</button>

                    <a class="btn btn-sm btn-outline-light ms-2" onclick="window.close();"><i class="fa-solid fa-door-open me-1"></i> 나가기</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container mt-3">
        <div class="row">
            <!-- 이전 비디오 -->
            <div class="col-sm-6">
                <div class="shadow px-3 py-2 mb-2 switch-bt"<?php
                    if(!$isitfirst) {
                        ?> onclick="location.href='./?video=<?=urlencode($currdir.'/'.$files[$order-1])?>';" <?php
                    }?>>
                    <div class="fw-bold switch-title">이전 비디오</div>
                    <div class="text-wrap switch-content"><?php
                    if ($isitfirst) {
                        echo "<span style='color: rgb(40,40,40);'>첫번째 비디오입니다</span>";
                    } else {
                        echo $files[$order-1];
                    }
                    ?></div>
                </div>
            </div>

            <!-- 다음 비디오 -->
            <div class="col-sm-6">
                <div class="shadow px-3 py-2 mb-2 switch-bt"<?php
                    if(!$isitlast) {
                        ?> onclick="location.href='./?video=<?=urlencode($currdir.'/'.$files[$order+1])?>';" <?php
                    }?>>
                    <div class="fw-bold switch-title">다음 비디오</div>
                    <div class="text-wrap switch-content"><?php
                    if ($isitlast) {
                        echo "<span style='color: rgb(40,40,40);'>마지막 비디오입니다</span>";
                    } else {
                        echo $files[$order+1];
                    }
                    ?></div>
                </div>
            </div>
        </div>

        <!-- 비디오 정보 -->
        <div class="card mt-2">
            <div class="card-body">
            <h6 class="card-subtitle mb-2 text-muted">비디오 정보</h6>
            <div style="font-size:small; color: gray;">
            파일 형식: <?=$path_parts['extension']?> | 내장 자막 존재 여부: <?=($caption==''?'N':'Y')?></br>
            비디오 용량: <?=filesize($video)?>B (<?=round(filesize($video)/1024/1024,2)?>MB) </br>
            마지막으로 수정된 날짜: <?=date("Y-m-d H:i:s", filemtime($video));?> </br>
            마지막으로 열람된 날짜: <?=$last_chk?>
            </div>
            </div>
        </div>
    </div>

    <div class="fixed mt-3">
		<p style="text-align: center;"><span style="color: #ffffff; opacity: 0.3;">by <a href="https://github.com/pdjdev">@PDJDEV</a><br>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script src="./js/script.js?rev=0.1"></script>
  </body>
</html>
