<?php

/**
 * DokuWiki Plugin fksdbauth (Auth Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Koutný <michal@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC'))
    die();

class auth_plugin_fksdbauth extends DokuWiki_Auth_Plugin {

    private static $contestMaps = array(
        'fykos' => 1,
        'vyfuk' => 2,
    );

    /**
     * @var PDO
     */
    private $connection;

    /**
     * @var array items with array keys: name, mail, grps, hash, login_id
     */
    private $usersCache;

    /**
     * @var array
     */
    private $groupsCache;

    /**
     * @var email to user mapping
     */
    private $emailKey;

    /**
     * @var email to user mapping
     */
    private $loginKey;

    /**
     * @var DokuWiki_Auth_Plugin
     */
    private $fallback;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(); // for compatibility

        $this->success = $this->connectToDatabase() && $this->cacheUsers();
        if (!$this->success && $this->getConf('fallback_enabled')) {
            $this->fallback = plugin_load('auth', $this->getConf('fallback_plugin'));
            $this->success = (bool)$this->fallback;
            /*
             * Using fallback auth plugin may potentially escalate privilegies
             * of the authenticated user (if the same user has different ACLs
             * there). However, see auth_setup that eventully calls auth_login,
             * i.e. re-checking user credentials, which should mitigate this
             * problem.
             */
        }

        $this->cando['addUser'] = false; // can Users be created?
        $this->cando['delUser'] = false; // can Users be deleted?
        $this->cando['modLogin'] = false; // can login names be changed?
        $this->cando['modPass'] = false; // can passwords be changed?
        $this->cando['modName'] = false; // can real names be changed?
        $this->cando['modMail'] = false; // can emails be changed?
        $this->cando['modGroups'] = false; // can groups be changed?
        $this->cando['getUsers'] = true; // can a (filtered) list of users be retrieved?
        $this->cando['getUserCount'] = true; // can the number of users be retrieved?
        $this->cando['getGroups'] = true; // can a list of available groups be retrieved?
        $this->cando['external'] = false; // does the module do external auth checking?
        $this->cando['logout'] = true; // can the user logout again? (eg. not possible with HTTP auth)

    }

    /**
     * Log off the current user [ OPTIONAL ]
     */
    //public function logOff() {
    //}

    /**
     * Do all authentication [ OPTIONAL ]
     *
     * @param   string  $user    Username
     * @param   string  $pass    Cleartext Password
     * @param   bool    $sticky  Cookie should not expire
     * @return  bool             true on successful auth
     */
    //public function trustExternal($user, $pass, $sticky = false) {
    /* some example:

      global $USERINFO;
      global $conf;
      $sticky ? $sticky = true : $sticky = false; //sanity check

      // do the checking here

      // set the globals if authed
      $USERINFO['name'] = 'FIXME';
      $USERINFO['mail'] = 'FIXME';
      $USERINFO['grps'] = array('FIXME');
      $_SERVER['REMOTE_USER'] = $user;
      $_SESSION[DOKU_COOKIE]['auth']['user'] = $user;
      $_SESSION[DOKU_COOKIE]['auth']['pass'] = $pass;
      $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
      return true;

     */
    //}

    /**
     * Check user+password
     *
     * May be ommited if trustExternal is used.
     *
     * @param   string $user the user name
     * @param   string $pass the clear text password
     * @return  bool
     */
    public function checkPass($user, $pass) {
        if ($this->fallback) {
            return $this->fallback->checkPass($user, $pass);
        }

        // first search by email, then by login
        $userData = $this->getUserData($user);
        if (!$userData) {
            return false;
        }

        $hash = sha1($userData['login_id'] . md5($pass));
        return $hash == $userData['hash'];
    }

    /**
     * Return user info
     *
     * Returns info about the given user needs to contain
     * at least these fields:
     *
     * name string  full name of the user
     * mail string  email addres of the user
     * grps array   list of groups the user is in
     *
     * @param   string $user the user name
     * @return  array containing user data or false
     */
    public function getUserData($user, $requiregroups=true) {
        if ($this->fallback) {
            return $this->fallback->getUserData($user);
        }

        if (array_key_exists($user, $this->emailKey)) {
            return $this->usersCache[$this->emailKey[$user]];
        } else if (array_key_exists($user, $this->loginKey)) {
            return $this->usersCache[$this->loginKey[$user]];
        } else {
            return false;
        }
    }

    /**
     * Create a new User [implement only where required/possible]
     *
     * Returns false if the user already exists, null when an error
     * occurred and true if everything went well.
     *
     * The new user HAS TO be added to the default group by this
     * function!
     *
     * Set addUser capability when implemented
     *
     * @param  string     $user
     * @param  string     $pass
     * @param  string     $name
     * @param  string     $mail
     * @param  null|array $grps
     * @return bool|null
     */
    //public function createUser($user, $pass, $name, $mail, $grps = null) {
    // FIXME implement
    //    return null;
    //}

    /**
     * Modify user data [implement only where required/possible]
     *
     * Set the mod* capabilities according to the implemented features
     *
     * @param   string $user    nick of the user to be changed
     * @param   array  $changes array of field/value pairs to be changed (password will be clear text)
     * @return  bool
     */
    //public function modifyUser($user, $changes) {
    // FIXME implement
    //    return false;
    //}

    /**
     * Delete one or more users [implement only where required/possible]
     *
     * Set delUser capability when implemented
     *
     * @param   array  $users
     * @return  int    number of users deleted
     */
    //public function deleteUsers($users) {
    // FIXME implement
    //    return false;
    //}

    /**
     * Bulk retrieval of user data [implement only where required/possible]
     *
     * Set getUsers capability when implemented
     *
     * @param   int   $start     index of first user to be returned
     * @param   int   $limit     max number of users to be returned
     * @param   array $filter    array of field/pattern pairs, null for no filter
     * @return  array list of userinfo (refer getUserData for internal userinfo details)
     */
    public function retrieveUsers($start = 0, $limit = -1, $filter = null) {
        if ($this->fallback) {
            return $this->fallback->retrieveUsers($start, $limit, $filter);
        }

        $filtered = array_values($this->filterUsers($filter));

        $result = array();
        $lower = $start;
        $upper = min(count($filtered), $start + ($limit < 0 ? count($filtered) : $limit));
        for ($i = $lower; $i < $upper; ++$i) {
            $result[] = $filtered[$i];
        }
        return $result;
    }

    /**
     * Return a count of the number of user which meet $filter criteria
     * [should be implemented whenever retrieveUsers is implemented]
     *
     * Set getUserCount capability when implemented
     *
     * @param  array $filter array of field/pattern pairs, empty array for no filter
     * @return int
     */
    public function getUserCount($filter = array()) {
        if ($this->fallback) {
            return $this->fallback->getUserCount($filter);
        }

        return count($this->filterUsers($filter));
    }

    /**
     * Define a group [implement only where required/possible]
     *
     * Set addGroup capability when implemented
     *
     * @param   string $group
     * @return  bool
     */
    //public function addGroup($group) {
    // FIXME implement
    //    return false;
    //}

    /**
     * Retrieve groups [implement only where required/possible]
     *
     * Set getGroups capability when implemented
     *
     * @param   int $start
     * @param   int $limit
     * @return  array
     */
    public function retrieveGroups($start = 0, $limit = 0) {
        if ($this->fallback) {
            return $this->fallback->retrieveGroups($start, $limit);
        }

        $result = array();
        $groups = array_values($this->groupsCache);
        for ($i = $start; $i < $start + ($limit == 0 ? count($this->groupsCache) : min(count($this->groupsCache), $limit)); ++$i) {
            $result[] = $groups[$i];
        }
        return $result;
    }

    /**
     * Return case sensitivity of the backend
     *
     * When your backend is caseinsensitive (eg. you can login with USER and
     * user) then you need to overwrite this method and return false
     *
     * @return bool
     */
    public function isCaseSensitive() {
        if ($this->fallback) {
            return $this->fallback->isCaseSensitive();
        }

        return true;
    }

    /**
     * Sanitize a given username
     *
     * This function is applied to any user name that is given to
     * the backend and should also be applied to any user name within
     * the backend before returning it somewhere.
     *
     * This should be used to enforce username restrictions.
     *
     * @param string $user username
     * @return string the cleaned username
     */
    public function cleanUser($user) {
        if ($this->fallback) {
            return $this->fallback->cleanUser($user);
        }

        return $user;
    }

    /**
     * Sanitize a given groupname
     *
     * This function is applied to any groupname that is given to
     * the backend and should also be applied to any groupname within
     * the backend before returning it somewhere.
     *
     * This should be used to enforce groupname restrictions.
     *
     * Groupnames are to be passed without a leading '@' here.
     *
     * @param  string $group groupname
     * @return string the cleaned groupname
     */
    public function cleanGroup($group) {
        if ($this->fallback) {
            return $this->fallback->cleanGroup($group);
        }

        return $group;
    }

    /**
     * Check Session Cache validity [implement only where required/possible]
     *
     * DokuWiki caches user info in the user's session for the timespan defined
     * in $conf['auth_security_timeout'].
     *
     * This makes sure slow authentication backends do not slow down DokuWiki.
     * This also means that changes to the user database will not be reflected
     * on currently logged in users.
     *
     * To accommodate for this, the user manager plugin will touch a reference
     * file whenever a change is submitted. This function compares the filetime
     * of this reference file with the time stored in the session.
     *
     * This reference file mechanism does not reflect changes done directly in
     * the backend's database through other means than the user manager plugin.
     *
     * Fast backends might want to return always false, to force rechecks on
     * each page load. Others might want to use their own checking here. If
     * unsure, do not override.
     *
     * @param  string $user - The username
     * @return bool
     */
    //public function useSessionCache($user) {
    // FIXME implement
    //}

    private function connectToDatabase() {
        $dsn = 'mysql:host=' . $this->getConf('mysql_host') . ';dbname=' . $this->getConf('mysql_database');
        $username = $this->getConf('mysql_user');
        $passwd = $this->getConf('mysql_password');
        $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            PDO::ATTR_TIMEOUT => 3,
        );
        try {
            $this->connection = new PDO($dsn, $username, $passwd, $options);
            return true;
        } catch (PDOException $e) {
            msg($e->getMessage(), -1);
            return false;
        }
    }

    private function cacheUsers() {
        $contestId = self::$contestMaps[$this->getConf('contest')];
        /*
         * Cache users
         */
        $stmt = $this->connection->prepare('select *
            from v_dokuwiki_user u
            inner join v_dokuwiki_user_group ug on u.login_id = ug.login_id and ug.contest_id = :contest_id
            order by name_lex');
        $stmt->bindValue('contest_id', $contestId);
        $stmt->execute();
        $users = $stmt->fetchAll();

        $this->usersCache = array();
        $this->emailKey = array();
        $this->loginKey = array();
        foreach ($users as $row) {
            $loginId = $row['login_id'];
            if ($row['email']) {
                $this->emailKey[$row['email']] = $loginId;
            }
            $this->loginKey[$row['login']] = $loginId;

            $this->usersCache[$loginId] = array(
                'name' => $row['name'],
                'mail' => $row['email'],
                'hash' => $row['hash'],
                'login_id' => $row['login_id'],
                'user' => $row['login'],
                'grps' => array(),
            );
            // name, mail, (grps), hash, login_id
        }

        /*
         * Cache group names
         */
        $stmt = $this->connection->prepare('select * from v_dokuwiki_group');
        $stmt->execute();
        $this->groupsCache = array();
        foreach ($stmt->fetchAll() as $row) {
            $this->groupsCache[$row['role_id']] = $row['name'];
        }

        $stmt = $this->connection->prepare('select * from v_dokuwiki_user_group where contest_id = :contest_id');
        $stmt->bindValue('contest_id', $contestId);
        $stmt->execute();
        foreach ($stmt->fetchAll() as $row) {
            $loginId = $row['login_id'];
            if(!isset($this->usersCache[$loginId])) {
                continue;
            }
            $groupName = $this->groupsCache[$row['role_id']];
            $this->usersCache[$loginId]['grps'][] = $groupName;
        }

        return true;
    }

    private function filterUsers($filter) {
        $filtered = array();
        if ($filter) {
            foreach ($this->usersCache as $userData) {
                foreach ($filter as $field => $value) {
                    $searched = $userData[$field];
                    if (is_array($searched)) {
                        $searched = implode(':', $searched);
                    }
                    if (preg_match('/' . preg_quote($value, '/') . '/i', $searched)) {
                        $filtered[] = $userData;
                        break;
                    }
                }
            }
        } else {
            $filtered = array_values($this->usersCache);
        }
        return $filtered;
    }

}

// vim:ts=4:sw=4:et:
