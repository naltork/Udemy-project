<!-- Navigation -->
<?php
    require_once(__DIR__ . '/base-url.php');

    // This file renders in two modes:
    //   logged in -> top navbar + sidebar (dashboard, profile, admin section)
    //   guest     -> top navbar only, with Login / Register links
    // Guest mode must not touch $_SESSION['id'] or the database, because
    // login.php / register.php / reset.php have no authenticated user.
    $nav_logged_in = isset($_SESSION['login']) && ($_SESSION['login'] == true)
                     && isset($_SESSION['id']) && !empty($_SESSION['id']);

    $profile_username = '';
    $nav_is_admin = false;
    if($nav_logged_in){
        $navsql = "SELECT username, role FROM users WHERE id=?";
        $navresult = $db->prepare($navsql);
        $navresult->execute(array($_SESSION['id']));
        $navuser = $navresult->fetch(PDO::FETCH_ASSOC);
        if($navuser !== false){
            $profile_username = $navuser['username'];
            $nav_is_admin = ($navuser['role'] == 'admin');
        }else{
            // Session points at a user that no longer exists - treat as guest.
            $nav_logged_in = false;
        }
    }

    // Current script name, used to mark the active menu item.
    $nav_current = basename($_SERVER['PHP_SELF']);
    if(!function_exists('nav_active')){
        function nav_active($page, $current){
            return ($page === $current) ? 'active' : '';
        }
    }
?>
<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
    <div class="navbar-header">
        <a class="navbar-brand" href="<?php echo $base_url; ?>index.php">Student Portal</a>
    </div>
    <!-- /.navbar-header -->

    <ul class="nav navbar-top-links navbar-right">
        <?php if($nav_logged_in){ ?>
            <li>
                <a href="<?php echo $base_url; ?>profile/<?php echo urlencode($profile_username); ?>">
                    <i class="fa fa-user fa-fw"></i> <?php echo htmlspecialchars($profile_username); ?>
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>logout.php"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
            </li>
        <?php }else{ ?>
            <li>
                <a class="<?php echo trim(nav_active('login.php', $nav_current)); ?>" href="<?php echo $base_url; ?>login.php">
                    <i class="fa fa-sign-in fa-fw"></i> Login
                </a>
            </li>
            <li>
                <a class="<?php echo trim(nav_active('register.php', $nav_current)); ?>" href="<?php echo $base_url; ?>register.php">
                    <i class="fa fa-edit fa-fw"></i> Register
                </a>
            </li>
        <?php } ?>
    </ul>
    <!-- /.navbar-top-links -->

    <?php if($nav_logged_in){ ?>
    <div class="navbar-default sidebar" role="navigation">
        <div class="sidebar-nav navbar-collapse">
            <ul class="nav" id="side-menu">
                <li>
                    <a class="<?php echo trim(nav_active('index.php', $nav_current)); ?>" href="<?php echo $base_url; ?>index.php"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
                </li>
                <li>
                    <a class="<?php echo trim(nav_active('edit-profile.php', $nav_current)); ?>" href="<?php echo $base_url; ?>edit-profile.php"><i class="fa fa-user fa-fw"></i> Edit Profile</a>
                </li>
                <li>
                    <a class="<?php echo trim(nav_active('activity.php', $nav_current)); ?>" href="<?php echo $base_url; ?>activity.php"><i class="fa fa-list fa-fw"></i> My Activity</a>
                </li>
                <li>
                    <a class="<?php echo trim(nav_active('permissions.php', $nav_current)); ?>" href="<?php echo $base_url; ?>permissions.php"><i class="fa fa-lock fa-fw"></i> My Permissions</a>
                </li>
                <?php if($nav_is_admin){ ?>
                <li>
                    <span class="nav-header"><i class="fa fa-cogs fa-fw"></i> Admin</span>
                    <ul class="nav nav-second-level">
                        <li>
                            <a class="<?php echo trim(nav_active('users.php', $nav_current)); ?>" href="<?php echo $base_url; ?>users.php">Users</a>
                        </li>
                        <li>
                            <a class="<?php echo trim(nav_active('login-activity-admin.php', $nav_current)); ?>" href="<?php echo $base_url; ?>login-activity-admin.php">Login Activity</a>
                        </li>
                        <li>
                            <a class="<?php echo trim(nav_active('user-activity-admin.php', $nav_current)); ?>" href="<?php echo $base_url; ?>user-activity-admin.php">User Activity</a>
                        </li>
                    </ul>
                    <!-- /.nav-second-level -->
                </li>
                <?php } ?>
            </ul>
        </div>
        <!-- /.sidebar-collapse -->
    </div>
    <!-- /.navbar-static-side -->
    <?php } ?>
</nav>