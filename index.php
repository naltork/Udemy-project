<?php
include('check-login.php');
require_once('includes/connect.php');
include('includes/header.php');
include('includes/navigation.php');

// Fetch user info for welcome banner
$usql = "SELECT username FROM users WHERE id=?";
$uresult = $db->prepare($usql);
$uresult->execute(array($_SESSION['id']));
$urow = $uresult->fetch(PDO::FETCH_ASSOC);
$display_name = ($urow !== false) ? htmlspecialchars($urow['username']) : 'Student';

// Calendar logic
$cal_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$cal_year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
if($cal_month < 1){ $cal_month = 12; $cal_year--; }
if($cal_month > 12){ $cal_month = 1; $cal_year++; }
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $cal_month, $cal_year);
$first_day_of_week = date('w', mktime(0, 0, 0, $cal_month, 1, $cal_year));
$month_name = date('F Y', mktime(0, 0, 0, $cal_month, 1, $cal_year));
$prev_month = $cal_month - 1;
$prev_year  = $cal_year;
$next_month = $cal_month + 1;
$next_year  = $cal_year;
if($prev_month < 1){ $prev_month = 12; $prev_year--; }
if($next_month > 12){ $next_month = 1; $next_year++; }
$today_day   = (int)date('j');
$today_month = (int)date('n');
$today_year  = (int)date('Y');

// User stats
$stat_activity = $db->prepare("SELECT COUNT(*) FROM user_activity WHERE uid=?");
$stat_activity->execute(array($_SESSION['id']));
$activity_count = $stat_activity->fetchColumn();

$stat_logins = $db->prepare("SELECT COUNT(*) FROM login_log WHERE uid=?");
$stat_logins->execute(array($_SESSION['id']));
$login_count = $stat_logins->fetchColumn();
?>
<div id="page-wrapper">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <h1>Welcome back, <?php echo $display_name; ?>!</h1>
        <p><i class="fa fa-calendar"></i> <?php echo date('l, F j, Y'); ?> &nbsp;&bull;&nbsp; UAFS Student Portal</p>
    </div>

    <!-- Quick Stats -->
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <i class="fa fa-book stat-icon"></i>
                <div class="stat-number">4</div>
                <div class="stat-label">Enrolled Courses</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <i class="fa fa-clock-o stat-icon"></i>
                <div class="stat-number">12</div>
                <div class="stat-label">Credit Hours</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <i class="fa fa-sign-in stat-icon"></i>
                <div class="stat-number"><?php echo $login_count; ?></div>
                <div class="stat-label">Total Logins</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <i class="fa fa-list stat-icon"></i>
                <div class="stat-number"><?php echo $activity_count; ?></div>
                <div class="stat-label">Activity Entries</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Courses Section -->
        <div class="col-lg-8">
            <div class="uafs-panel">
                <div class="panel-title"><i class="fa fa-graduation-cap"></i> My Courses &mdash; Fall <?php echo date('Y'); ?></div>
                <div class="panel-content">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="course-card">
                                <div class="course-code">CS 3513</div>
                                <div class="course-name">Database Management</div>
                                <div class="course-meta">
                                    <i class="fa fa-user"></i> Dr. Johnson<br>
                                    <i class="fa fa-clock-o"></i> MWF 9:00 - 9:50 AM<br>
                                    <i class="fa fa-map-marker"></i> Windgate Hall 204
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="course-card">
                                <div class="course-code">CS 4523</div>
                                <div class="course-name">Web Development</div>
                                <div class="course-meta">
                                    <i class="fa fa-user"></i> Prof. Williams<br>
                                    <i class="fa fa-clock-o"></i> TR 11:00 - 12:15 PM<br>
                                    <i class="fa fa-map-marker"></i> Baldor Tech 110
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="course-card">
                                <div class="course-code">MATH 2914</div>
                                <div class="course-name">Calculus I</div>
                                <div class="course-meta">
                                    <i class="fa fa-user"></i> Dr. Patel<br>
                                    <i class="fa fa-clock-o"></i> MWF 10:00 - 10:50 AM<br>
                                    <i class="fa fa-map-marker"></i> Echols Hall 301
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="course-card">
                                <div class="course-code">ENGL 1013</div>
                                <div class="course-name">Composition I</div>
                                <div class="course-meta">
                                    <i class="fa fa-user"></i> Ms. Carter<br>
                                    <i class="fa fa-clock-o"></i> TR 2:00 - 3:15 PM<br>
                                    <i class="fa fa-map-marker"></i> Fullerton Hall 115
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar Section -->
        <div class="col-lg-4">
            <div class="uafs-panel">
                <div class="panel-title"><i class="fa fa-calendar"></i> Calendar</div>
                <div class="panel-content">
                    <div class="cal-nav">
                        <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>">&laquo; Prev</a>
                        <span class="cal-month"><?php echo $month_name; ?></span>
                        <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>">Next &raquo;</a>
                    </div>
                    <table class="uafs-calendar">
                        <thead>
                            <tr>
                                <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            $day_counter = 1;
                            $started = false;
                            for($row = 0; $row < 6; $row++){
                                if($day_counter > $days_in_month) break;
                                echo "<tr>";
                                for($col = 0; $col < 7; $col++){
                                    if(!$started && $col == $first_day_of_week){
                                        $started = true;
                                    }
                                    if($started && $day_counter <= $days_in_month){
                                        $is_today = ($day_counter == $today_day && $cal_month == $today_month && $cal_year == $today_year);
                                        $class = $is_today ? 'today' : '';
                                        echo "<td class='{$class}'>{$day_counter}</td>";
                                        $day_counter++;
                                    }else{
                                        echo "<td class='empty'></td>";
                                    }
                                }
                                echo "</tr>";
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="page-footer">
        University of Arkansas - Fort Smith &bull; Student Portal &bull; <?php echo date('Y'); ?>
    </div>
</div>
<?php include('includes/footer.php'); ?>