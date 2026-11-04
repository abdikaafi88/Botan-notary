<?php
require_once __DIR__.'/config/auth.php';
require_admin();
$title='Add User';
$err='';
$old=['full_name'=>'','username'=>'','role'=>'staff'];
if($_SERVER['REQUEST_METHOD']==='POST'){
    verify_csrf();
    $old['full_name']=trim($_POST['full_name']??'');
    $old['username']=trim($_POST['username']??'');
    $old['role']=($_POST['role']??'staff')==='admin'?'admin':'staff';
    $password=$_POST['password']??'';
    if($old['full_name']===''||$old['username']===''||$password===''){
        $err='Fadlan buuxi magaca, username-ka, iyo password-ka.';
    }elseif(strlen($password)<8){
        $err='Password-ku waa inuu ka koobnaadaa ugu yaraan 8 xaraf.';
    }else{
        $check=db()->prepare('SELECT id FROM users WHERE username=?');
        $check->execute([$old['username']]);
        if($check->fetch()){
            $err='Username-kan hore ayuu u jiraa. Fadlan dooro username kale.';
        }else{
            try{
                $s=db()->prepare('INSERT INTO users(full_name,username,password_hash,role,status) VALUES(?,?,?,?,?)');
                $s->execute([$old['full_name'],$old['username'],password_hash($password,PASSWORD_DEFAULT),$old['role'],'active']);
                flash('success','User cusub ayaa si guul leh loo sameeyay.');
                redirect('users_list.php');
            }catch(PDOException $e){
                $err=$e->getCode()==='23000'?'Username-kan hore ayuu u jiraa. Fadlan dooro username kale.':'User-ka lama samayn karin. Fadlan isku day mar kale.';
            }
        }
    }
}
require __DIR__.'/includes/header.php';
?><div class="page-head"><div><h1>Add user</h1><p class="muted">User cusub wuxuu noqonayaa active marka la sameeyo.</p></div></div><?php if($err):?><div class="alert error"><?=e($err)?></div><?php endif;?><div class="card"><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="grid grid-2"><div class="field"><label>Full name</label><input name="full_name" value="<?=e($old['full_name'])?>" required></div><div class="field"><label>Username</label><input name="username" value="<?=e($old['username'])?>" required></div><div class="field"><label>Password</label><input type="password" name="password" required minlength="8"><small class="muted">Ugu yaraan 8 xaraf.</small></div><div class="field"><label>Role</label><select name="role"><option value="staff" <?=$old['role']==='staff'?'selected':''?>>Staff</option><option value="admin" <?=$old['role']==='admin'?'selected':''?>>Admin</option></select></div></div><button class="btn" type="submit">Create user</button></form></div><?php require __DIR__.'/includes/footer.php';
