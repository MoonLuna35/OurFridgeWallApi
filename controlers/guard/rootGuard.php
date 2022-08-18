<?php
    if (!$current_user->get_is_root()) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }
