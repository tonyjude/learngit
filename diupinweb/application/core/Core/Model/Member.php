<?php

class Core_Model_Member extends Core_Model_Abstract
{
	
	/**
	 * 将用户资料更新至阿里云
	 * @param obj $userinfos 
	 * @return boolean
	 */
	public function updateUserinfoToOpenIM(Userinfos $userinfos)
	{
		$c = TopClient::getInstance();
		$req = new OpenimUsersUpdateRequest;
		$req->setUserinfos(json_encode($userinfos));
		$resp = $c->execute($req);
		$resp = Core_Utils::object2array($resp);
		if (isset($resp['uid_succ']) && $resp['uid_succ']) {
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * 将用户资料更新至阿里云
	 * @param obj $userinfo 
	 * @return boolean
	 */
	public function addUserinfoToOpenIM(Userinfos $userinfos)
	{
		$c = TopClient::getInstance();
		$req = new OpenimUsersAddRequest;
		$req->setUserinfos(json_encode($userinfos));
		$resp = $c->execute($req);
		$resp = Core_Utils::object2array($resp);
				
		if (isset($resp['uid_succ']) && $resp['uid_succ']) {
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * 将用户资料从阿里云删除
	 * @param string $uids 
	 * @return boolean
	 */
	public function deleteUserinfoToOpenIM($uids)
	{
		$c = TopClient::getInstance();
		$req = new OpenimUsersDeleteRequest;
		$req->setUserids($uids);
		$c->execute($req);
		return TRUE;
	}
	
}