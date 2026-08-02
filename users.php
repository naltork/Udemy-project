<?php
require_once('includes/connect.php');
require_once('check-admin.php');
include('includes/header.php');
include('includes/navigation.php');

// Handle role change
if(isset($_POST['toggle_role']) && isset($_POST['user_id'])){
    $target_id = (int)$_POST['user_id'];
    // Prevent admin from demoting themselves
    if($target_id == $_SESSION['id']){
        $errors[] = "You cannot change your own role.";
    }else{
        // Get current role
        $rolesql = "SELECT role FROM users WHERE id=?";
        $roleresult = $db->prepare($rolesql);
        $roleresult->execute(array($target_id));
        $rolerow = $roleresult->fetch(PDO::FETCH_ASSOC);
        if($rolerow !== false){
            $new_role = ($rolerow['role'] == 'admin') ? '' : 'admin';
            $updsql = "UPDATE users SET role=:role, updated=NOW() WHERE id=:id";
            $updresult = $db->prepare($updsql);
            $updresult->execute(array(':role' => $new_role, ':id' => $target_id));
            $action = ($new_role == 'admin') ? 'Promoted to Admin' : 'Removed from Admin';
            $messages[] = "User #{$target_id} {$action}.";

            // Log the activity
            $actsql = "INSERT INTO user_activity (uid, activity) VALUES (:uid, :activity)";
            $actresult = $db->prepare($actsql);
            $actresult->execute(array(':uid' => $target_id, ':activity' => $action . ' by admin'));
        }else{
            $errors[] = "User not found.";
        }
    }
}
?>
<div id="page-wrapper" style="min-height: 100vh;">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Users</h1>
        </div>
    </div>
    <?php if(!empty($messages)){ ?>
    <div class="alert alert-success">
        <?php foreach($messages as $msg){ echo "<span class='glyphicon glyphicon-ok'></span>&nbsp;" . htmlspecialchars($msg) . "<br>"; } ?>
    </div>
    <?php } ?>
    <?php if(!empty($errors)){ ?>
    <div class="alert alert-danger">
        <?php foreach($errors as $err){ echo "<span class='glyphicon glyphicon-remove'></span>&nbsp;" . htmlspecialchars($err) . "<br>"; } ?>
    </div>
    <?php } ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="uafs-panel">
                <div class="panel-title"><i class="fa fa-users"></i> All Registered Users</div>
                <div class="panel-content">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $usersql = "SELECT * FROM users ORDER BY id ASC";
                                    $userresult = $db->prepare($usersql);
                                    $userresult->execute();
                                    $userres = $userresult->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($userres as $user) {
                                        $is_self = ($user['id'] == $_SESSION['id']);
                                        $is_admin_user = ($user['role'] == 'admin');
                                ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if($is_admin_user){ ?>
                                            <span style="color:#C8991D; font-weight:bold;"><i class="fa fa-shield"></i> Admin</span>
                                        <?php }else{ ?>
                                            <span style="color:#555;">Student</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if($user['activate'] == 1){ ?>
                                            <span style="color:green;"><i class="fa fa-check-circle"></i> Active</span>
                                        <?php }else{ ?>
                                            <span style="color:#999;"><i class="fa fa-clock-o"></i> Pending</span>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo $user['created']; ?></td>
                                    <td>
                                        <?php if($is_self){ ?>
                                            <span style="color:#999; font-size:12px;">You</span>
                                        <?php }else{ ?>
                                            <form method="post" style="display:inline; margin:0;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="toggle_role" value="1">
                                                <?php if($is_admin_user){ ?>
                                                    <button type="submit" class="btn btn-xs" style="background-color:#c62828; color:#fff;">Remove Admin</button>
                                                <?php }else{ ?>
                                                    <button type="submit" class="btn btn-xs btn-uafs">Make Admin</button>
                                                <?php } ?>
                                            </form>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('includes/footer.php'); ?>