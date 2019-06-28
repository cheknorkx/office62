<?php
/**
 * @filesource modules/index/controllers/index.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Index\Index;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Http\Response;
use Kotchasan\Language;
use Kotchasan\Template;

/**
 * Controller สำหรับแสดงหน้าเว็บ.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * หน้าหลักเว็บไซต์ (index.html)
     * ให้ผลลัพท์เป็น HTML.
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        // ตัวแปรป้องกันการเรียกหน้าเพจโดยตรง
        define('MAIN_INIT', 'indexhtml');
        // session cookie
        $request->initSession();
        // ตรวจสอบการ login
        Login::create();
        // กำหนด skin ให้กับ template
        Template::init(self::$cfg->skin);
        // View
        self::$view = new \Gcms\View();
        // Javascript
        self::$view->addScript('var WEB_URL="'.WEB_URL.'";');
        // Login
        $login = Login::isMember();
        // โหลดเมนู
        self::$menus = \Index\Menu\Controller::init($login);
        // Javascript
        self::$view->addScript('var FIRST_MODULE="'.self::$menus->home().'";');
        // โหลดค่าติดตั้งโมดูล
        $dir = ROOT_PATH.'modules/';
        $f = @opendir($dir);
        if ($f) {
            while (false !== ($text = readdir($f))) {
                if ($text != '.' && $text != '..' && $text != 'index' && $text != 'css' && $text != 'js' && is_dir($dir.$text)) {
                    if (is_file($dir.$text.'/controllers/init.php')) {
                        require_once $dir.$text.'/controllers/init.php';
                        $className = '\\'.ucfirst($text).'\Init\Controller';
                        if (method_exists($className, 'execute')) {
                            $className::execute($request, self::$menus, $login);
                        }
                    }
                }
            }
            closedir($f);
        }
        // Controller หลัก
        $page = createClass('Index\Main\Controller')->execute($request);
        $languages = '';
        foreach (Language::installedLanguage() as $item) {
            $languages .= '<li><a id=lang_'.$item.' href="'.$page->canonical()->withParams(array('lang' => $item), true).'" title="{LNG_Language} '.strtoupper($item).'" style="background-image:url('.WEB_URL.'language/'.$item.'.gif)" tabindex=1>&nbsp;</a></li>';
        }
        if (is_file(ROOT_PATH.DATA_FOLDER.'bg_image.png')) {
            $bg_image = WEB_URL.DATA_FOLDER.'bg_image.png';
        } else {
            $bg_image = '';
        }
        if (is_file(ROOT_PATH.DATA_FOLDER.'logo.png')) {
            $logo = '<img src="'.WEB_URL.DATA_FOLDER.'logo.png" alt="{WEBTITLE}">&nbsp;{WEBTITLE}';
        } else {
            $logo = '<span class="'.self::$cfg->default_icon.'">{WEBTITLE}</span>';
        }
        if ($login) {
            $loginname = '{LNG_Welcome} <a href="index.php?module=editprofile" title="{LNG_Editing your account}">'.(empty($login['name']) ? $login['username'] : $login['name']).'</a>';
        } else {
            $loginname = '<a href="index.php?module=welcome&amp;action=login">{LNG_Please log in}</a>';
        }
        // เนื้อหา
        self::$view->setContents(array(
            // main template
            '/{MAIN}/' => $page->detail(),
            // โลโก
            '/{LOGO}/' => $logo,
            // language menu
            '/{LANGUAGES}/' => $languages,
            // title
            '/{TITLE}/' => $page->title(),
            // รูปภาพพื้นหลัง
            '/{BGIMAGE}/' => $bg_image,
            // เมนู
            '/{MENUS}/' => self::$menus->render($page->menu(), $login),
            // แสดงชื่อคน Login
            '/{LOGINNAME}/' => $loginname,
        ));
        // ส่งออก เป็น HTML
        $response = new Response();
        if ($page->status() == 404) {
            $response = $response->withStatus(404)->withAddedHeader('Status', '404 Not Found');
        }
        $response->withContent(self::$view->renderHTML())->send();
    }
}
