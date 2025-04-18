<?php
require_once STM_ZOOM_PATH . '/zoom-conference/StmZoomUpdates.php';
require_once STM_ZOOM_PATH . '/zoom-conference/StmZoomUpdatesCallbacks.php';
require_once STM_ZOOM_PATH . '/zoom-conference/StmZoom.php';
require_once STM_ZOOM_PATH . '/zoom-conference/StmZoomAdminMenus.php';
require_once STM_ZOOM_PATH . '/zoom-conference/StmZoomAdminNotices.php';
require_once STM_ZOOM_PATH . '/zoom-conference/StmZoomPostTypes.php';
require_once STM_ZOOM_PATH . '/zoom-conference/StmZoomAPITypes.php';

//call callback updates
StmZoomUpdates::init();

// Create objects
new StmZoom;
new StmZoomAdminMenus;
new StmZoomAdminNotices;
new StmZoomPostTypes;
