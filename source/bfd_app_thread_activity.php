<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_activity.php 28709 2012-03-08 08:53:48Z liulanbo $
 */


require_once libfile('function/forumlist');
require_once libfile('function/forum');
require_once libfile('function/group');
require_once libfile('function/post');


loadforum();

$isverified = $applied = 0;
$ufielddata = $applyinfo = '';

if(!$_G['uid']) {
        BfdApp::display_result('not_loggedin',null,'','','1');
    }

if($_G['uid']) {
	$applyinfo = C::t('forum_activityapply')->fetch_info_for_user($_G['uid'], $_G['tid']);
	if($applyinfo) {
		$isverified = $applyinfo['verified'];
		if($applyinfo['ufielddata']) {
			$ufielddata = dunserialize($applyinfo['ufielddata']);
		}
		$applied = 1;
	}
}
/*if($applied == 1)
{
	BfdApp::display_result('activity_repeat_apply',null,'','','1');
}
*/
$applylist = array();
$activity = C::t('forum_activity')->fetch($_G['tid']);
$activityclose = $activity['expiration'] ? ($activity['expiration'] > TIMESTAMP ? 0 : 1) : 0;
if($activityclose == 1)
{
	BfdApp::display_result('activity_stop',null,'','','1');
}

$applynumbers = $activity['applynumber'];
$aboutmembers = $activity['number'] >= $applynumbers ? $activity['number'] - $applynumbers : 0;
if($aboutmembers < 1 && $activity['number'] > 0)
{
	BfdApp::display_result('activity_stop',null,'','','1');
}
$activity['starttimefrom'] = dgmdate($activity['starttimefrom'], 'u');
$activity['starttimeto'] = $activity['starttimeto'] ? dgmdate($activity['starttimeto']) : 0;
$activity['expiration'] = $activity['expiration'] ? dgmdate($activity['expiration']) : 0;
$activity['attachurl'] = $activity['thumb'] = '';

if($activity['ufield']) {
	$activity['ufield'] = dunserialize($activity['ufield']);
	if($activity['ufield']['userfield']) {
		$htmls = $settings = array();
		require_once libfile('function/profile');
		foreach($activity['ufield']['userfield'] as $fieldid) {
			if(empty($ufielddata['userfield'])) {
				$memberprofile = C::t('common_member_profile')->fetch($_G['uid']);
				foreach($activity['ufield']['userfield'] as $val) {
					$ufielddata['userfield'][$val] = $memberprofile[$val];
				}
				unset($memberprofile);
			}
			$html = profile_setting($fieldid, $ufielddata['userfield'], false, true);
			if($html) {
				$settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
				$htmls[$fieldid] = $html;
			}
		}
	}
} else {
	$activity['ufield'] = '';
}
/*
if($activity['aid']) {
	$attach = C::t('forum_attachment_n')->fetch('tid:'.$_G['tid'], $activity['aid']);
	if($attach['isimage']) {
		$activity['attachurl'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/'.$attach['attachment'];
		$activity['thumb'] = $attach['thumb'] ? getimgthumbname($activity['attachurl']) : $activity['attachurl'];
		$activity['width'] = $attach['thumb'] && $_G['setting']['thumbwidth'] < $attach['width'] ? $_G['setting']['thumbwidth'] : $attach['width'];
	}
	$skipaids[] = $activity['aid'];
}


$applylistverified = array();
$noverifiednum = 0;
$query = C::t('forum_activityapply')->fetch_all_for_thread($_G['tid'], 0, 0, 0, 1);
foreach($query as $activityapplies) {
	$activityapplies['dateline'] = dgmdate($activityapplies['dateline'], 'u');
	if($activityapplies['verified'] == 1) {
		$activityapplies['ufielddata'] = dunserialize($activityapplies['ufielddata']);
		if(count($applylist) < $_G['setting']['activitypp']) {
			$activityapplies['message'] = preg_replace("/(".lang('forum/misc', 'contact').".*)/", '', $activityapplies['message']);
			$applylist[] = $activityapplies;
		}
	} else {
		if(count($applylistverified) < 8) {
			$applylistverified[] = $activityapplies;
		}
		$noverifiednum++;
	}

}

$applynumbers = $activity['applynumber'];
$aboutmembers = $activity['number'] >= $applynumbers ? $activity['number'] - $applynumbers : 0;
$allapplynum = $applynumbers + $noverifiednum;
*/
if($_G['forum']['status'] == 3) {
	$isgroupuser = groupperm($_G['forum'], $_G['uid']);
	if( helper_access::check_module('group') && $isgroupuser != 'isgroupuser' )
	{
		//尚未加入该小组
	}
}

$activity_apply_url = 'http://'.$_SERVER['HTTP_HOST'].'/appapi/index.php?mod=forum_misc';

header('Content-type: text/html; charset=utf-8');

include template('thread_activity_form',0,'./appapi/source/include/');
function dz_change_charset($msg)
{
    if(BFD_APP_CHARSET != BFD_APP_CHARSET_OUTPUT)
    {
        $msg = iconv(BFD_APP_CHARSET,BFD_APP_CHARSET_OUTPUT,$msg);
    }
    return $msg;
}
function dz_delete_qqstring($msg)
{
	$pattern = '/<p><a[^>]*>[^<]*<\/a><\/p><div class="rq mtn" id="showerror_qq"><\/div>/ies';
	$msg = preg_replace($pattern,'',$msg);
	return $msg;
}

?>
