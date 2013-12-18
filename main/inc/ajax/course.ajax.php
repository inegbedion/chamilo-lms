<?php
/* For licensing terms, see /license.txt */
/**
 * Responses to AJAX calls
 */

$language_file[] = 'admin';
require_once '../global.inc.php';

$action = $_REQUEST['a'];
$user_id = api_get_user_id();

switch ($action) {
    case 'add_course_vote':
        $course_id = intval($_REQUEST['course_id']);
        $star      = intval($_REQUEST['star']);

        if (!api_is_anonymous()) {
            CourseManager::add_course_vote($user_id, $star, $course_id, 0);
        }
        $point_info = CourseManager::get_course_ranking($course_id, 0);
        $ajax_url = api_get_path(WEB_AJAX_PATH).'course.ajax.php?a=add_course_vote';
        $rating = Display::return_rating_system('star_'.$course_id, $ajax_url.'&amp;course_id='.$course_id, $point_info, false);
        echo $rating;

        break;
    case 'get_user_courses':
        if (api_is_platform_admin()) {
            $user_id = intval($_POST['user_id']);
            $list_course_all_info = CourseManager::get_courses_list_by_user_id($user_id, false);
            if (!empty($list_course_all_info)) {
                foreach ($list_course_all_info as $course_item) {
                    $course_info = api_get_course_info($course_item['code']);
                    echo $course_info['title'].'<br />';
                }
            } else {
                echo get_lang('UserHasNoCourse');
            }
        }
        break;
    case 'search_category':
        require_once api_get_path(LIBRARY_PATH).'course_category.lib.php';
        if (api_is_platform_admin() || api_is_allowed_to_create_course()) {
            $results = searchCategoryByKeyword($_REQUEST['q']);
            if (!empty($results)) {
                foreach ($results as &$item) {
                    $item['id'] = $item['code'];
                }
                echo json_encode($results);
            } else {
                echo json_encode(array());
            }
        }
        break;
    case 'search_course':
        if (api_is_platform_admin()) {
            $courseList = Coursemanager::get_courses_list(
                0,
                10,
                1, //$orderby = 1,
                'ASC',
                -1,
                $_REQUEST['q']
            );
            $results = array();

            require_once api_get_path(LIBRARY_PATH).'course_category.lib.php';

            foreach ($courseList as $courseInfo) {
                $title = $courseInfo['title'];

                if (!empty($courseInfo['category_code'])) {
                    $parents = getParentsToString($courseInfo['category_code']);
                    $title = $parents.$courseInfo['title'];
                }

                $results[] = array(
                    'id' => $courseInfo['id'],
                    'text' => $title
                );
            }

            if (!empty($results)) {
                /*foreach ($results as &$item) {
                    $item['id'] = $item['code'];
                }*/
                echo json_encode($results);
            } else {
                echo json_encode(array());
            }
        }
        break;
    case 'search_course_by_session':
        if (api_is_platform_admin())
        {
            $results = SessionManager::get_course_list_by_session_id($_GET['session_id'], $_GET['q']);

            //$results = SessionManager::get_sessions_list(array('s.name LIKE' => "%".$_REQUEST['q']."%"));
            $results2 = array();
            if (!empty($results)) {
                foreach ($results as $item) {
                    $item2 = array();
                    foreach ($item as $id => $internal) {
                        if ($id == 'id') {
                            $item2[$id] = $internal;
                        }
                        if ($id == 'title') {
                            $item2['text'] = $internal;
                        }
                    }
                    $results2[] = $item2;
                }
                echo json_encode($results2);
            } else {
                echo json_encode(array());
            }
        }
        break;
    default:
        echo '';
}
exit;
