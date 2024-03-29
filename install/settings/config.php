<?php

/* config.php */

return array(
    'version' => '2.0.5',
    'web_title' => 'E-Office',
    'web_description' => 'ระบบจองห้องประชุม แจ้งซ่อม งานสารบรรณ',
    'timezone' => 'Asia/Bangkok',
    'member_status' => array(
        0 => 'สมาชิก',
        1 => 'ผู้ดูแลระบบ',
        2 => 'บุคลากร',
    ),
    'color_status' => array(
        0 => '#259B24',
        1 => '#FF0000',
        2 => '#0000FF',
    ),
    'user_forgot' => 0,
    'user_register' => 0,
    'welcome_email' => 0,
    'booking_w' => 500,
    'inventory_w' => 500,
    'repair_first_status' => 1,
    'repair_send_mail' => 0,
    'edocument_format_no' => 'DOC-%04d',
    'edocument_send_mail' => 1,
    'edocument_file_typies' => array(
        0 => 'doc',
        1 => 'ppt',
        2 => 'pptx',
        3 => 'docx',
        4 => 'rar',
        5 => 'zip',
        6 => 'jpg',
        7 => 'jpeg',
        8 => 'pdf',
    ),
    'edocument_upload_size' => 2097152,
    'edocument_download_action' => 0,
    'booking_line_id' => 1,
    'booking_send_mail' => 0,
    'personnel_w' => 500,
    'personnel_h' => 500,
    'personnel_status' => array(
        0 => 1,
        1 => 2,
    ),
);
