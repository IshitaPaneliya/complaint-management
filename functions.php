<?php
// Utility functions

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function set_flash_message($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

function get_flash_message() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function generate_id($prefix, $last_id = 0) {
    $year = date('Y');
    $next = str_pad($last_id + 1, 5, '0', STR_PAD_LEFT);
    return "$prefix-$year-$next";
}

function get_status_badge($status) {
    $colors = [
        'Pending' => 'warning',
        'Assigned' => 'info',
        'In Progress' => 'primary',
        'On Hold' => 'secondary',
        'Resolved' => 'success',
        'Closed' => 'dark',
        'Rejected' => 'danger'
    ];
    
    $color = isset($colors[$status]) ? $colors[$status] : 'secondary';
    return "<span class='badge status-badge badge-$color'>$status</span>";
}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function add_notification($pdo, $user_id, $title, $message, $type, $related_id = null) {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, related_id) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $title, $message, $type, $related_id]);
}

function get_unread_notifications_count($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}
function get_display_category($category_name, $custom_category) {
    if (strtolower($category_name) === 'other' && !empty($custom_category)) {
        return htmlspecialchars($custom_category);
    }
    return htmlspecialchars($category_name);
}
?>
