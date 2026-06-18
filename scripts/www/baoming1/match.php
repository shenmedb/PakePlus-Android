<?php
error_reporting(0);
@ini_set('display_errors', 0);
session_start();
$isAdmin = $_SESSION['admin_login'] ?? false;

$dataFile = 'data.json';
$matchFile = 'match.json';
$loop3File = 'loop3.json';
$finalFile = 'final.json';
$thirdFile = 'third.json';

if (isset($_GET['reset']) && $_GET['reset'] === 'ok' && $isAdmin) {
    file_put_contents($matchFile, '[]');
    file_put_contents($loop3File, 'null');
    file_put_contents($finalFile, 'null');
    file_put_contents($thirdFile, 'null');
    header('Location:match.php'); exit;
}

if (isset($_GET['auto']) && $_GET['auto'] === 'ok' && $isAdmin) {
    $all = json_decode(file_get_contents($dataFile), true) ?: [];
    $players = [];
    foreach ($all as $item) {
        if ($item['activity'] === '套马比赛') $players[] = $item;
    }
    shuffle($players);
    $match = [];
    for ($i=0;$i<count($players);$i+=2) {
        $a = $players[$i];
        $b = isset($players[$i+1]) ? $players[$i+1] : null;
        $match[] = [
            'id' => uniqid(),
            'a' => $a,
            'b' => $b,
            'result' => '待比赛'
        ];
    }
    file_put_contents($matchFile, json_encode($match, JSON_UNESCAPED_UNICODE));
    header('Location:match.php'); exit;
}

if (isset($_GET['next']) && $_GET['next'] === 'ok' && $isAdmin) {
    $last = json_decode(file_get_contents($matchFile), true) ?: [];
    $winners = [];
    foreach ($last as $m) {
        if (!$m['b']) {
            $winners[] = $m['a'];
            continue;
        }
        $winA = $m['a']['gameName'].' 获胜';
        $winB = $m['b']['gameName'].' 获胜';
        if ($m['result'] === $winA) $winners[] = $m['a'];
        if ($m['result'] === $winB) $winners[] = $m['b'];
    }

    if (count($winners) == 3) {
        $p1 = $winners[0];
        $p2 = $winners[1];
        $p3 = $winners[2];
        $loopMatch = [
            ['id'=>uniqid(),'a'=>$p1,'b'=>$p2,'result'=>'待比赛'],
            ['id'=>uniqid(),'a'=>$p2,'b'=>$p3,'result'=>'待比赛'],
            ['id'=>uniqid(),'a'=>$p1,'b'=>$p3,'result'=>'待比赛'],
        ];
        file_put_contents($loop3File, json_encode($loopMatch, JSON_UNESCAPED_UNICODE));
        file_put_contents($matchFile, '[]');
        header('Location:match.php'); exit;
    }

    shuffle($winners);
    $nextMatch = [];
    for ($i=0;$i<count($winners);$i+=2) {
        $a = $winners[$i];
        $b = isset($winners[$i+1]) ? $winners[$i+1] : null;
        $nextMatch[] = ['id'=>uniqid(),'a'=>$a,'b'=>$b,'result'=>'待比赛'];
    }
    file_put_contents($matchFile, json_encode($nextMatch, JSON_UNESCAPED_UNICODE));
    header('Location:match.php'); exit;
}

$matchList = json_decode(file_get_contents($matchFile), true) ?: [];
$loop3 = json_decode(file_get_contents($loop3File), true);
$finalData = json_decode(file_get_contents($finalFile), true);
$thirdData = json_decode(file_get_contents($thirdFile), true);

if (!empty($_POST['code']) && !empty($_POST['mid']) && !empty($_POST['win']) && empty($loop3) && empty($finalData)) {
    $code = trim($_POST['code']);
    $mid  = trim($_POST['mid']);
    $win  = trim($_POST['win']);
    foreach ($matchList as &$m) {
        if ($m['id'] !== $mid) continue;
        $isA = ($m['a']['tmCode'] === $code);
        $isB = ($m['b'] && $m['b']['tmCode'] === $code);
        if (!$isA && !$isB) { echo "❌ 编号错误，无权提交"; exit; }
        $m['result'] = $win === 'a' ? $m['a']['gameName'].' 获胜' : $m['b']['gameName'].' 获胜';
    }
    unset($m);
    file_put_contents($matchFile, json_encode($matchList, JSON_UNESCAPED_UNICODE));
    header('Location:match.php'); exit;
}

if (!empty($_POST['code']) && !empty($_POST['mid']) && !empty($_POST['win']) && !empty($loop3)) {
    $code = trim($_POST['code']);
    $mid  = trim($_POST['mid']);
    $win  = trim($_POST['win']);
    foreach ($loop3 as &$m) {
        if ($m['id'] !== $mid) continue;
        $isA = ($m['a']['tmCode'] === $code);
        $isB = ($m['b']['tmCode'] === $code);
        if (!$isA && !$isB) { echo "❌ 编号错误，无权提交"; exit; }
        $m['result'] = $win === 'a' ? $m['a']['gameName'].' 获胜' : $m['b']['gameName'].' 获胜';
    }
    unset($m);
    file_put_contents($loop3File, json_encode($loop3, JSON_UNESCAPED_UNICODE));

    $allDone = true;
    foreach ($loop3 as $m) { if ($m['result'] == '待比赛') $allDone = false; }
    if ($allDone) {
        $p1 = $loop3[0]['a'];$p2 = $loop3[0]['b'];$p3 = $loop3[1]['b'];
        $winCount = [$p1['gameName']=>0,$p2['gameName']=>0,$p3['gameName']=>0];
        foreach ($loop3 as $m) {
            $wa = $m['a']['gameName'].' 获胜';
            $wb = $m['b']['gameName'].' 获胜';
            if ($m['result']==$wa) $winCount[$m['a']['gameName']]++;
            if ($m['result']==$wb) $winCount[$m['b']['gameName']]++;
        }
        arsort($winCount);
        $rank = array_keys($winCount);
        $f1=$f2=$third=null;
        foreach ([$p1,$p2,$p3] as $pp){
            if($pp['gameName']==$rank[0]) $f1=$pp;
            if($pp['gameName']==$rank[1]) $f2=$pp;
            if($pp['gameName']==$rank[2]) $third=$pp;
        }
        file_put_contents($finalFile, json_encode(['a'=>$f1,'b'=>$f2,'finish'=>false],JSON_UNESCAPED_UNICODE));
        file_put_contents($thirdFile, json_encode($third,JSON_UNESCAPED_UNICODE));
    }
    header('Location:match.php'); exit;
}

if (!empty($_POST['code']) && !empty($_POST['win']) && !empty($finalData) && $finalData['finish']===false) {
    $code = trim($_POST['code']);
    $win  = trim($_POST['win']);
    $isA = ($finalData['a']['tmCode'] === $code);
    $isB = ($finalData['b']['tmCode'] === $code);
    if (!$isA && !$isB) { die("❌ 编号错误"); }

    if ($win == 'a') {
        $finalData['champion'] = $finalData['a'];
        $finalData['runner']  = $finalData['b'];
    } else {
        $finalData['champion'] = $finalData['b'];
        $finalData['runner']  = $finalData['a'];
    }
    $finalData['finish'] = true;
    file_put_contents($finalFile, json_encode($finalData, JSON_UNESCAPED_UNICODE));
    header('Location:match.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<title>🏆 套马赛事对战公示</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:sans-serif}
body{background:#000;color:#fff;padding:30px 20px}
.container{max-width:1200px;margin:0 auto}
h1{color:#ffcc00;text-align:center;margin-bottom:30px;font-size:28px}

.admin-btn{
    background:#ffcc00;color:#000;border:none;padding:15px 30px;border-radius:12px;
    font-size:16px;font-weight:bold;margin:0 10px 25px 10px;cursor:pointer;
}
.reset-btn{background:#ff3333;color:#fff;border:none;padding:15px 30px;border-radius:12px;font-size:16px;font-weight:bold;margin:0 10px 25px 10px;}

.match-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px;margin-top:20px;}
.match-item{background:#1a1a1a;border:3px solid #ffcc00;padding:25px;border-radius:12px;}
.vs{color:#ffcc00;font-size:24px;text-align:center;margin:12px 0;font-weight:bold}
.result{color:#ffcc00;font-size:18px;font-weight:bold;margin:15px 0;text-align:center}
input{background:#111;border:2px solid #ffcc00;color:#fff;padding:12px;width:100%;border-radius:8px;margin-bottom:10px}
.btn-group{display:flex;gap:10px;justify-content:center}
button{background:#ffcc00;color:#000;border:none;padding:12px 20px;border-radius:8px;font-weight:bold}
.empty{text-align:center;color:#999;font-size:18px;grid-column:span 2;margin-top:40px}

.nav-buttons{margin-top:30px;display:flex;gap:15px;justify-content:center;flex-wrap:wrap;}
.nav-btn{width:180px;padding:14px;background:#333;color:#ffcc00;border:2px solid #ffcc00;border-radius:8px;cursor:pointer;font-size:16px;}

#customModal{
    position:fixed;top:0;left:0;width:100%;height:100%;
    background:rgba(0,0,0,0.85);display:none;
    justify-content:center;align-items:center;z-index:9999;
}
.modal-box{
    background:#1a1a1a;border:3px solid #ffcc00;padding:40px 35px;border-radius:16px;
    width:90%;max-width:420px;text-align:center;
}
.modal-text{color:#ffcc00;font-size:20px;margin-bottom:30px}
.modal-btn-wrap{display:flex;gap:15px;justify-content:center}
.modal-btn{padding:12px 35px;border-radius:8px;border:none;font-size:16px;font-weight:bold;cursor:pointer;}
.btn-confirm{background:#ffcc00;color:#000}
.btn-cancel{background:#333;color:#ffcc00;border:2px solid #ffcc00}

.rank-box{
    border:4px solid #ffcc00;background:#221a00;border-radius:16px;
    padding:45px 30px;text-align:center;margin:30px auto;max-width:550px;
}
.rank-title{font-size:32px;color:#ffcc00;font-weight:bold;margin-bottom:35px}
.rank-item{margin:20px 0}
.rank1{color:#ffcc00;font-size:28px;font-weight:bold}
.rank2{color:#d4af37;font-size:24px}
.rank3{color:#cd7f32;font-size:22px}
.rank-name{font-size:24px;color:#fff;margin:6px 0}

.final-box{
    border:4px solid #ffcc00;background:#221a00;border-radius:16px;
    padding:40px 30px;text-align:center;margin:30px auto;max-width:550px;
}
.final-title{font-size:32px;color:#ffcc00;font-weight:bold;margin-bottom:25px}

.flow-footer {
  text-align: center;
  color: #fff;
  font-size: 12px;
  margin-top: 30px;
  padding-bottom: 10px;
  line-height: 1.4;
}
.flow-line {
  height: 15px;
  line-height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
}
.flow-line a {
  color: #fff !important;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}
.flow-line img {
  width: 13px;
  height: 13px;
  display: block;
}

@media (max-width:768px){
    .match-grid{grid-template-columns:repeat(1,1fr);}
    .admin-btn,.reset-btn{margin:5px 0;display:block;width:90%;margin-left:auto;margin-right:auto}
    .nav-btn{width:100%;max-width:280px}
}
</style>
</head>
<body>
<div class="container">
<h1>🏆 套马比赛 完整公平赛事</h1>

<?php if($isAdmin): ?>
<button class="admin-btn" onclick="showModal('确定开启首轮全员随机匹配？','auto')">首轮自动匹配</button>
<button class="admin-btn" onclick="showModal('确认晋级下一轮？','next')">晋级赛自动匹配</button>
<button class="reset-btn" onclick="showModal('⚠️ 警告！重置后所有赛程、排名全部清空，从头开赛！','reset')">一键重置赛事</button>
<?php endif; ?>

<?php
if ($finalData && $finalData['finish'] === true): ?>
<div class="rank-box">
    <div class="rank-title">🎉 赛事圆满结束 · 最终排名</div>
    <div class="rank-item">
        <div class="rank1">🏆 总冠军</div>
        <div class="rank-name"><?=$finalData['champion']['gameName']?></div>
    </div>
    <div class="rank-item">
        <div class="rank2">🥈 亚军</div>
        <div class="rank-name"><?=$finalData['runner']['gameName']?></div>
    </div>
    <div class="rank-item">
        <div class="rank3">🥉 季军</div>
        <div class="rank-name"><?=$thirdData['gameName']?></div>
    </div>
</div>

<?php
elseif ($finalData && $finalData['finish'] === false): ?>
<div class="final-box">
    <div class="final-title">🏁 总决赛对决</div>
    <div style="font-size:22px">选手A：<?=$finalData['a']['gameName']?></div>
    <div class="vs">VS</div>
    <div style="font-size:22px">选手B：<?=$finalData['b']['gameName']?></div>
    <div style="margin:20px 0;color:#cd7f32">🥉 季军已定：<?=$thirdData['gameName']?></div>
    <div style="margin:15px 0;color:#ffcc00">请输入本人专属编号提交决赛胜负</div>
    <form method="post">
        <input type="text" name="code" placeholder="输入你的专属tm编号" required style="width:100%;padding:12px;background:#222;border:2px solid #ffcc00;color:#fff;border-radius:8px;margin-bottom:15px">
        <div class="btn-group">
            <button type="submit" name="win" value="a">(A)获胜</button>
            <button type="submit" name="win" value="b">(B)获胜</button>
        </div>
    </form>
</div>

<?php
elseif ($loop3): ?>
<div class="final-box">
    <div class="final-title">⚔️ 三人循环半决赛（全员互打）</div>
</div>
<div class="match-grid">
<?php foreach ($loop3 as $m): ?>
<div class="match-item">
<div style="text-align:center">选手A：<?=$m['a']['gameName']?></div>
<div class="vs">VS</div>
<div style="text-align:center">选手B：<?=$m['b']['gameName']?></div>
<div class="result">当前对战结果：<?=$m['result']?></div>

<?php if($m['result'] === '待比赛'): ?>
<div style="margin:15px 0;color:#ffcc00;text-align:center">请输入本人专属编号提交本局胜负</div>
<form method="post">
    <input type="hidden" name="mid" value="<?=$m['id']?>">
    <input type="text" name="code" placeholder="输入你的专属tm编号" required style="width:100%;padding:12px;background:#222;border:2px solid #ffcc00;color:#fff;border-radius:8px;margin-bottom:10px">
    <div class="btn-group">
        <button type="submit" name="win" value="a">(A)获胜</button>
        <button type="submit" name="win" value="b">(B)获胜</button>
    </div>
</form>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>

<?php
elseif (!empty($matchList)): ?>
<div class="match-grid">
<?php foreach ($matchList as $m): ?>
<div class="match-item">
<div style="text-align:center">选手A：<?=$m['a']['gameName']?></div>
<div class="vs">VS</div>
<?php if($m['b']): ?>
<div style="text-align:center">选手B：<?=$m['b']['gameName']?></div>
<?php else: ?>
<div style="color:#ffcc00;text-align:center">轮空 → 晋级半决赛</div>
<?php endif; ?>
<div class="result">当前对战结果：<?=$m['result']?></div>

<?php if($m['result'] === '待比赛' && $m['b']): ?>
<div style="margin:15px 0;color:#ffcc00;text-align:center">请输入本人专属编号提交本局胜负</div>
<form method="post">
    <input type="hidden" name="mid" value="<?=$m['id']?>">
    <input type="text" name="code" placeholder="输入你的专属tm编号" required style="width:100%;padding:12px;background:#222;border:2px solid #ffcc00;color:#fff;border-radius:8px;margin-bottom:10px">
    <div class="btn-group">
        <button type="submit" name="win" value="a">(A)获胜</button>
        <button type="submit" name="win" value="b">(B)获胜</button>
    </div>
</form>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>

<?php
else: ?>
<div class="empty">赛程暂未开启，请等待管理员开赛</div>
<?php endif; ?>

<div class="nav-buttons">
    <button class="nav-btn" onclick="location.href='taoma.html'">返回名单</button>
    <button class="nav-btn" onclick="location.href='baoming.html'">返回报名</button>
</div>

<div class="flow-footer">
  <div class="flow-line">本站内容仅供娱乐交流使用，禁止商用</div>
  <div class="flow-line">© 2026 起源联盟 版权所有</div>
  <div class="flow-line">
    <a href="https://beian.miit.gov.cn/" target="_blank">
      <img src="foot-icp.png" alt="备案">
      浙ICP备2026025846号-1
    </a>
  </div>
  <div class="flow-line">
    <a href="http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=33052202000916" target="_blank">
      <img src="foot-gongan.png" alt="公安备案">
      浙公网安备33052202000916号
    </a>
  </div>
</div>

</div>

<div id="customModal">
    <div class="modal-box">
        <div class="modal-text" id="modalTip"></div>
        <div class="modal-btn-wrap">
            <button class="modal-btn btn-confirm" id="confirmBtn">确定</button>
            <button class="modal-btn btn-cancel" id="cancelBtn">取消</button>
        </div>
    </div>
</div>

<script>
let jumpUrl = '';
function showModal(text, url) {
    document.getElementById('modalTip').innerText = text;
    document.getElementById('customModal').style.display = 'flex';
    jumpUrl = url;
}
document.getElementById('cancelBtn').onclick = function(){
    document.getElementById('customModal').style.display = 'none';
    jumpUrl = '';
}
document.getElementById('confirmBtn').onclick = function(){
    document.getElementById('customModal').style.display = 'none';
    if(jumpUrl) location.href = '?'+jumpUrl+'=ok';
}
</script>
</body>
</html>