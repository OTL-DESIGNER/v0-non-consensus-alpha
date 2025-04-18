<?php

class StmZoomUpdatesCallbacks {
    /**
     * Admin notification set transient
     */
    public static function eroom_admin_notification_transient() {
        $data = [ 'show_time' => DAY_IN_SECONDS * 3 + time(), 'step' => 0, 'prev_action' => '' ];
        set_transient( 'stm_eroom-zoom-meetings-webinar_notice_setting', $data );
    }
}