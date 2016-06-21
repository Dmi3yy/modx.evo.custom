<?php
require_once('MODx.php');

class modUsers extends MODxAPI
{

    protected $default_field = array(
        'user' => array(
            'username' => null,
            'password' => null,
            'cachepwd' => null
        ),
        'attribute' => array(
            'fullname' => null,
            'role' => null,
            'email' => null,
            'phone' => null,
            'mobilephone' => null,
            'blocked' => null,
            'blockeduntil' => null,
            'blockedafter' => null,
            'logincount' => null,
            'lastlogin' => null,
            'thislogin' => null,
            'failedlogincount' => null,
            'sessionid' => null,
            'dob' => null,
            'gender' => null,
            'country' => null,
            'state' => null,
            'zip' => null,
            'fax' => null,
            'photo' => null,
            'comment' => null
        ),
        'hidden' => array(
            'internalKey'
        )
    );

    protected $givenPassword = '';

    public function issetField($key)
    {
        return (array_key_exists($key, $this->default_field['user']) || array_key_exists($key, $this->default_field['attribute']) || in_array($key, $this->default_field['hidden']));
    }

    /**
     * @param $data
     * @return bool|string
     */
    protected function findUser($data)
    {
        switch (true) {
            case (is_int($data) || ((int)$data > 0 && (string)intval($data) === $data)):
                $find = 'attribute.internalKey';
                break;
            case filter_var($data, FILTER_VALIDATE_EMAIL):
                $find = 'attribute.email';
                break;
            case is_scalar($data):
                $find = 'user.username';
                break;
            default:
                $find = false;
        }
        return $find;
    }

    /**
     * @param $id
     * @return $this
     */
    public function edit($id)
    {
        $id = is_scalar($id) ? trim($id) : '';
        if ($this->getID() != $id) {
            $this->close();
            $this->newDoc = false;

            if (!$find = $this->findUser($id)) {
                $this->id = null;
            }else {
                $result = $this->query("
                    SELECT * from {$this->makeTable('web_user_attributes')} as attribute
                    LEFT JOIN {$this->makeTable('web_users')} as user ON user.id=attribute.internalKey
                    WHERE BINARY {$find}='{$this->escape($id)}'
                ");
                $this->field = $this->modx->db->getRow($result);

                $this->id = empty($this->field['internalKey']) ? null : $this->get('internalKey');
                unset($this->field['id']);
                unset($this->field['internalKey']);
            }
        }
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        if (is_scalar($value) && is_scalar($key) && !empty($key)) {
            switch ($key) {
                case 'password':
                {
                    $this->givenPassword = $value;
                    $value = $this->getPassword($value);
                    break;
                }
            }
            $this->field[$key] = $value;
        }
        return $this;
    }

    /**
     * @param $pass
     * @return string
     */
    public function getPassword($pass)
    {
        return md5($pass);
    }

    /**
     * @param null $fire_events
     * @param bool $clearCache
     * @return bool|int|null|void
     */
    public function save($fire_events = null, $clearCache = false)
    {
        if ($this->get('email') == '' || $this->get('username') == '' || $this->get('password') == '') {
            $this->log['EmptyPKField'] = 'Email, username or password is empty <pre>' . print_r($this->toArray(), true) . '</pre>';
            return false;
        }

        if (!$this->checkUnique('web_users', 'username')) {
            $this->log['UniqueUsername'] = 'username not unique <pre>' . print_r($this->get('username'), true) . '</pre>';
            return false;
        }

        if (!$this->checkUnique('web_user_attributes', 'email', 'internalKey')) {
            $this->log['UniqueEmail'] = 'Email not unique <pre>' . print_r($this->get('email'), true) . '</pre>';
            return false;
        }

        /*$this->invokeEvent('OnBeforeDocFormSave',array (
            "mode" => $this->newDoc ? "new" : "upd",
            "id" => $this->id ? $this->id : ''
        ),$fire_events);*/

        $fld = $this->toArray();
        foreach ($this->default_field['user'] as $key => $value) {
            $tmp = $this->get($key);
            if ($this->newDoc && ( !is_int($tmp) && $tmp=='')) {
                if($tmp == $value){
                    //take default value from global config
                }
                $this->field[$key] = $value;
            }
            $this->Uset($key, 'user');
            unset($fld[$key]);
        }
        if (!empty($this->set['user'])) {
            if ($this->newDoc) {
                $SQL = "INSERT into {$this->makeTable('web_users')} SET " . implode(', ', $this->set['user']);
            } else {
                $SQL = "UPDATE {$this->makeTable('web_users')} SET " . implode(', ', $this->set['user']) . " WHERE id = " . $this->id;
            }
            $result = $this->query($SQL);
        }

        if ($this->newDoc) {
            $this->id = $this->modx->db->getInsertId();
        }

        foreach ($this->default_field['attribute'] as $key => $value) {
            $tmp = $this->get($key);
            if ($this->newDoc && ( !is_int($tmp) && $tmp=='')) {
                if($tmp == $value){
                    //take default value from global config
                }
                $this->field[$key] = $value;
            }
            $this->Uset($key, 'attribute');
            unset($fld[$key]);
        }
        if (!empty($this->set['attribute'])) {
            if ($this->newDoc) {
                $this->set('internalKey', $this->id)->Uset('internalKey', 'attribute');
                $SQL = "INSERT into {$this->makeTable('web_user_attributes')} SET " . implode(', ', $this->set['attribute']);
            } else {
                $SQL = "UPDATE {$this->makeTable('web_user_attributes')} SET " . implode(', ', $this->set['attribute']) . " WHERE  internalKey = " . $this->getID();
            }
            $this->query($SQL);
        }

        foreach ($fld as $key => $value) {
            if ($value == '') continue;
            $result = $this->query("SELECT `setting_value` FROM {$this->makeTable('web_user_settings')} WHERE `webuser` = '{$this->id}' AND `setting_name` = '{$key}'");
            if ($this->modx->db->getRecordCount($result) > 0) {
                $result = $this->query("UPDATE {$this->makeTable('web_user_settings')} SET `setting_value` = '{$value}' WHERE `webuser` = '{$this->id}' AND `setting_name` = '{$key}';");
            } else {
                $result = $this->query("INSERT into {$this->makeTable('web_user_settings')} SET `webuser` = {$this->id},`setting_name` = '{$key}',`setting_value` = '{$value}';");
            }
        }

        $this->invokeEvent('OnWebSaveUser',array (
            "mode" => $this->newDoc ? "new" : "upd",
            "id" => $this->id,
            "user" => $this->toArray()
        ),$fire_events);

        if ($clearCache) {
            $this->clearCache($fire_events);
        }
        return $this->id;
    }

    public function delete($ids, $fire_events = null)
    {
        if ($this->edit($ids)) {
            $flag = $this->query("
          DELETE user,attribute FROM {$this->makeTable('web_user_attributes')} as attribute
            LEFT JOIN {$this->makeTable('web_users')} as user ON user.id=attribute.internalKey
            WHERE attribute.internalKey='{$this->escape($this->getID())}'");
            $this->query("DELETE FROM {$this->makeTable('web_user_settings')} WHERE webuser='{$this->getID()}'");
            $this->invokeEvent('OnWebDeleteUser', array(
                'userid'        => $this->getID(),
                'internalKey'   => $this->getID(),
                'username'      => $this->get('username'),
                'timestamp'     => time()
            ), $fire_events);
        } else {
            $flag = false;
        }
        $this->close();
        return $flag;
    }

    /**
     * @param int $id
     * @param bool $fulltime
     * @param string $cookieName
     * @param null $fire_events
     * @return bool
     */
    public function authUser($id = 0, $fulltime = true, $cookieName = 'WebLoginPE', $fire_events = null)
    {
        $flag = false;
        if (!$this->getID() && $id) $this->edit($id);
        if ($this->getID()) {
            //$this->logOut($cookieName);
            $flag = true;
            $this->SessionHandler('start', $cookieName, $fulltime);
            $this->invokeEvent("OnWebLogin", array(
                "userid"        => $this->getID(),
                "username"      => $this->get('username'),
                "userpassword"  => $this->givenPassword,
                "rememberme"    => $fulltime
            ),$fire_events);
        }
        return $flag;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function checkBlock($id = 0)
    {
        $tmp = clone $this;
        if ($id && $tmp->getID() != $id) {
            $tmp->edit($id);
        }
        $now = time();

        $b = $tmp->get('blocked');
        $bu = $tmp->get('blockeduntil');
        $ba = $tmp->get('blockedafter');
        $flag = (($b && !$bu && !$ba) || ($bu && $now < $bu) || ($ba && $now > $ba));
        unset($tmp);
        return $flag;
    }

    /**
     * @param $id
     * @param $password
     * @param $blocker
     * @param null $fire_events
     * @return bool
     */
    public function testAuth($id, $password, $blocker, $fire_events = null)
    {
        $tmp = clone $this;
        if ($id && $tmp->getID() != $id) {
            $tmp->edit($id);
        }
        $flag = $pluginFlag = false;
        if (
            ($tmp->getID()) && (!$blocker || ($blocker && !$tmp->checkBlock($id)))
        ) {
            $this->invokeEvent('OnWebAuthentication',array(
                "userid"        => $tmp->getID(),
                "username"      => $tmp->get('username'),
                "userpassword"  => $password,
                "savedpassword" => $tmp->get('password')
            ),$fire_events);
            $pluginFlag = $this->modx->event->output_;
            if ($pluginFlag == false || (is_array($pluginFlag) && !in_array(true, $pluginFlag))) {
                $pluginFlag = false;
                $flag = ($tmp->get('password') == $tmp->getPassword($password));
            } else {
                $pluginFlag = true;
            }
        }
        unset($tmp);
        return $flag || $pluginFlag;
    }

    /**
     * @param bool $fulltime
     * @param string $cookieName
     * @return bool
     */
    public function AutoLogin($fulltime = true, $cookieName = 'WebLoginPE')
    {
        $flag = false;
        if (isset($_COOKIE[$cookieName])) {
            $cookie = explode('|', $_COOKIE[$cookieName], 2);
            if (isset($cookie[0], $cookie[1]) && strlen($cookie[0]) == 32 && strlen($cookie[1]) == 32) {
                $this->close();
                $q = $this->modx->db->query("SELECT id FROM " . $this->makeTable('web_users') . " WHERE md5(username)='{$this->escape($cookie[0])}'");
                $id = $this->modx->db->getValue($q);
                if ($this->edit($id) && $this->getID() && $this->get('password') == $cookie[1] && $this->testAuth($this->getID(), $cookie[1], true)) {
                    $flag = $this->authUser($this->getID(), $fulltime, $cookieName);

                }
            }
        }
        return $flag;
    }

    /**
     * @param string $cookieName
     * @param null $fire_events
     */
    public function logOut($cookieName = 'WebLoginPE', $fire_events = null)
    {
        if (!$uid = $this->modx->getLoginUserID('web')) return;
        $params = array(
            'username'      => $_SESSION['webShortname'],
            'internalKey'   => $uid,
            'userid'        => $uid // Bugfix by TS
        );
        $this->invokeEvent('OnBeforeWebLogout', $params, $fire_events);
        $this->SessionHandler('destroy', $cookieName ? $cookieName : 'WebLoginPE');
        $this->invokeEvent('OnWebLogout', $params, $fire_events);
    }

    /**
     * SessionHandler
     * Starts the user session on login success. Destroys session on error or logout.
     *
     * @param string $directive ('start' or 'destroy')
     * @return void
     * @author Raymond Irving
     * @author Scotty Delicious
     *
     * remeber может быть числом в секундах
     */
    protected function SessionHandler($directive, $cookieName, $remember = true)
    {
        switch ($directive) {
            case 'start':
            {
                if ($this->getID()) {
                    $_SESSION['webShortname'] = $this->get('username');
                    $_SESSION['webFullname'] = $this->get('fullname');
                    $_SESSION['webEmail'] = $this->get('email');
                    $_SESSION['webValidated'] = 1;
                    $_SESSION['webInternalKey'] = $this->getID();
                    $_SESSION['webValid'] = base64_encode($this->get('password'));
                    $_SESSION['webUser'] = base64_encode($this->get('username'));
                    $_SESSION['webFailedlogins'] = $this->get('failedlogincount');
                    $_SESSION['webLastlogin'] = $this->get('lastlogin');
                    $_SESSION['webnrlogins'] = $this->get('logincount');
                    $_SESSION['webUsrConfigSet'] = array();
                    $_SESSION['webUserGroupNames'] = $this->getUserGroups();
                    $_SESSION['webDocgroups'] = $this->getDocumentGroups();
                    if ($remember) {
                        $cookieValue = md5($this->get('username')) . '|' . $this->get('password');
                        $cookieExpires = time() + (is_bool($remember) ? (60 * 60 * 24 * 365 * 5) : (int)$remember);
                        setcookie($cookieName, $cookieValue, $cookieExpires, '/');
                    }
                }
                break;
            }
            case 'destroy':
            {
                if (isset($_SESSION['mgrValidated'])) {
                    unset($_SESSION['webShortname']);
                    unset($_SESSION['webFullname']);
                    unset($_SESSION['webEmail']);
                    unset($_SESSION['webValidated']);
                    unset($_SESSION['webInternalKey']);
                    unset($_SESSION['webValid']);
                    unset($_SESSION['webUser']);
                    unset($_SESSION['webFailedlogins']);
                    unset($_SESSION['webLastlogin']);
                    unset($_SESSION['webnrlogins']);
                    unset($_SESSION['webUsrConfigSet']);
                    unset($_SESSION['webUserGroupNames']);
                    unset($_SESSION['webDocgroups']);

                    setcookie($cookieName, '', time() - 60, '/');
                } else {
                    if (isset($_COOKIE[session_name()])) {
                        setcookie(session_name(), '', time() - 60, '/');
                    }
                    setcookie($cookieName, '', time() - 60, '/');
                    session_destroy();
                }
                break;
            }
        }
        return $this;
    }

    /**
     * @param int $userID
     * @return array
     */
    public function getDocumentGroups($userID = 0){
        $out = array();
        $user = $this->switchObject($userID);
        if($user->getID()){
            $web_groups = $this->modx->getFullTableName('web_groups');
            $webgroup_access = $this->modx->getFullTableName('webgroup_access');

            $sql = "SELECT `uga`.`documentgroup` FROM {$web_groups} as `ug`
                INNER JOIN {$webgroup_access} as `uga` ON `uga`.`webgroup`=`ug`.`webgroup`
                WHERE `ug`.`webuser` = ".$this->getID();
            $sql = $this->modx->db->makeArray($this->modx->db->query($sql));

            foreach($sql as $row){
                $out[] = $row['documentgroup'];
            }
        }
        unset($user);
        return $out;
    }

    /**
     * @param int $userID
     * @return array
     */
    public function getUserGroups($userID = 0){
        $out = array();
        $user = $this->switchObject($userID);
        if($user->getID()){
            $web_groups = $this->modx->getFullTableName('web_groups');
            $webgroup_names = $this->modx->getFullTableName('webgroup_names');

            $sql = "SELECT `ugn`.`name` FROM {$web_groups} as `ug`
                INNER JOIN {$webgroup_names} as `ugn` ON `ugn`.`id`=`ug`.`webgroup`
                WHERE `ug`.`webuser` = ".$user->getID();
            $sql = $this->modx->db->makeArray($this->modx->db->query($sql));

            foreach($sql as $row){
                $out[] = $row['name'];
            }
        }
        unset($user);
        return $out;
    }
}
