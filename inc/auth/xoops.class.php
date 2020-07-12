<?php
/**
 * XOOPS auth backend
 *
 * Uses XOOPS' global $xoopsUser variables.
 *
 * @author    D.J.
 */
define('DOKU_AUTH', dirname(__FILE__));
require_once(DOKU_AUTH.'/basic.class.php');

class auth_xoops extends auth_basic {

  /**
   * Constructor.
   *
   * Sets additional capabilities and config strings
   */
  function auth_xoops(){
    global $conf;
    $this->cando['external'] = true;
  }

  /**
   * Just checks against the $xoopsUser variable
   */
  function getUserData($user = null){
	  $this->trustExternal($user);
	  return $GLOBALS['USERINFO'];
  }

  /**
   * Just checks against the $xoopsUser variable
   */
  function trustExternal($user = null, $pass = null, $sticky = false){
    global $USERINFO;
    global $conf;

	if(is_object($GLOBALS["xoopsUser"])){
	    $xoops_uname = xoops2doku($GLOBALS['xoopsUser']->getVar("uname"));
		if(empty($user) || $user == $xoops_uname){
			//$USERINFO['pass'] = $GLOBALS["xoopsUser"]->getVar("password");
			$USERINFO['name'] = $xoops_uname;
			$USERINFO['mail'] = $GLOBALS["xoopsUser"]->getVar("email");
			$USERINFO['uid'] = $GLOBALS["xoopsUser"]->getVar("uid");
			$USERINFO['grps'] = $GLOBALS['xoopsUser']->groups();
			
			$_SERVER['REMOTE_USER'] = $GLOBALS["xoopsUser"]->getVar("uid");
			$_SESSION[$conf['title']]['auth']['user'] = $xoops_uname;
			$_SESSION[$conf['title']]['auth']['info'] = $USERINFO;
			$USERINFO['perm'] = doku_get_userlevel($GLOBALS['xoopsUser']);
			return true;
		}
	}
    $USERINFO['uid'] = 0;
    $USERINFO['name'] = "";
    $USERINFO['mail'] = "";
    $USERINFO['grps'] = array(XOOPS_GROUP_ANONYMOUS);
    $xoops_user = null;
	$USERINFO['perm'] = doku_get_userlevel($xoops_user);

    return false;
  }
}

/*
 * Convert XOOPS group permission to DOKUwiki permission
 *
 */
function doku_get_userlevel(&$xoops_user) {
	static $levels;

	/*
	define('AUTH_NONE',0);
	define('AUTH_READ',1);
	define('AUTH_EDIT',2);
	define('AUTH_CREATE',4);
	define('AUTH_UPLOAD',8);
	define('AUTH_DELETE',16);
	define('AUTH_ADMIN',255);
	*/
		
  // start edit jayjay -  allow both anonymous and registered users to see images - thanks peekay!
	if(!is_object($xoops_user)) {
            $groups = array(XOOPS_GROUP_ANONYMOUS);
            $uid = 0;
            return AUTH_READ;
    }else{
            $uid = $xoops_user->getVar("uid");
            $groups = $xoops_user->groups();
            if($xoops_user->isAdmin()) return AUTH_ADMIN;
            else return AUTH_UPLOAD;
    }
  // end edit jayjay
	
	if(isset($levels[$uid])) return $levels[$uid];
    $groupstring = "(" . implode(',', $groups) . ")";
    $criteria = new CriteriaCompo(new Criteria('gperm_modid', $GLOBALS["xoopsModule"]->getVar('mid')));
    $criteria->add(new Criteria('gperm_groupid', $groupstring, 'IN'));
    $gperm_handler = &xoops_gethandler('groupperm');
    $perms = $gperm_handler->getObjects($criteria, true);
    
    $perm_levels = array(
    	1=>AUTH_READ,
    	2=>AUTH_EDIT,
    	3=>AUTH_CREATE,
    	4=>AUTH_UPLOAD,
    	5=>AUTH_DELETE
    );
    $level = AUTH_NONE;
    
    foreach ($perms as $gperm_id => $gperm) {
	    if($perm_levels[$gperm->getVar('gperm_itemid')]>$level) $level = $perm_levels[$gperm->getVar('gperm_itemid')];
    }
    $levels[$uid] = $level;
    unset($perms);
    return $level;
	
	  /**
   * Retrieves the user data of the user identified by                                                                                                                                                        
   * username $user. This is used, e.g., by dokuwiki's
   * email notification feature.
   */
  function getUserData( $user ) {
    $userData = false;
    $mantis_uid = user_get_id_by_name( $user );
    if ( $mantis_uid ) {
      $userData['username'] = user_get_field( $mantis_uid, 'username' );
      $userData['mail'] = user_get_field( $mantis_uid, 'email' );
 
      $t_project_name = getNS( getID() );
      $t_project_id = project_get_id_by_name( $t_project_name );
      $t_access_level = access_get_project_level( $t_project_id , $mantis_uid );
      $t_access_level_string = strtoupper( get_enum_to_string( config_get( 'access_levels_enum_string' ),  $t_access_level ) );
 
      $userData['grps'] = array( $t_access_level_string );
    }
 
    return $userData;
  }
}
?>
