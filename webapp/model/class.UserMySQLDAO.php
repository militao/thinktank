<?php
/**
 * User Data Access Object MySQL Implementation
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */

require_once 'model/class.PDODAO.php';
require_once 'model/interface.UserDAO.php';

class UserMySQLDAO extends PDODAO implements UserDAO {
    /**
     * Get the SQL to generate average_tweets_per_day number
     * @TODO rename "tweets" "posts"
     * @return str SQL calcuation
     */
    private function getAverageTweetCount() {
        return "round(post_count/(datediff(curdate(), joined)), 2) as avg_tweets_per_day";
    }

    public function isUserInDB($user_id) {
        $q = "SELECT user_id ";
        $q .= "FROM #prefix#users ";
        $q .= "WHERE user_id = :user_id;";
        $vars = array(
            ':user_id'=>$user_id
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataIsReturned($ps);
    }

    public function isUserInDBByName($username) {
        $q = "SELECT user_id ";
        $q .= "FROM #prefix#users ";
        $q .= "WHERE user_name = :username";
        $vars = array(
            ':username'=>$username
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataIsReturned($ps);
    }

    public function updateUsers($users_to_update) {
        $count = 0;
        $status_message = "";
        if (sizeof($users_to_update) > 0) {
            $status_message .= count($users_to_update)." users queued for insert or update; ";
            foreach ($users_to_update as $user) {
                $count += $this->updateUser($user);
            }
            $status_message .= "$count users affected.";
        }
        $this->logger->logStatus($status_message, get_class($this));
        $status_message = "";
        return $count;
    }

    public function updateUser($user) {
        $status_message = "";
        $has_friend_count = $user->friend_count != '' ? true : false;
        $has_last_post = $user->last_post != '' ? true : false;
        $has_last_post_id = $user->last_post_id != '' ? true : false;
        $network = $user->network != '' ? $user->network : 'twitter';
        $user->follower_count = $user->follower_count != '' ? $user->follower_count : 0;
        $user->post_count = $user->post_count != '' ? $user->post_count : 0;

        $q = "INSERT INTO #prefix#users (
                user_id, user_name, full_name, avatar, location,
                description, url, is_protected, follower_count, post_count, ".($has_friend_count ? "friend_count, " : "")."
                ".($has_last_post ? "last_post, " : "")." found_in, joined, network  ".($has_last_post_id ? ", last_post_id" : "").")
            VALUES (
                :user_id, 
                :username, :full_name,
                :avatar, :location,  
                :description, :url, :is_protected, 
                :follower_count, :post_count, 
                ".($has_friend_count ? ":friend_count, " : "")."
                ".($has_last_post ? ":last_post, " : "")." 
                :found_in, :joined, :network
                 ".($has_last_post_id ? ", :last_post_id " : "")."
                )
            ON DUPLICATE KEY UPDATE 
                full_name = :full_name,
                avatar =  :avatar,
                location = :location,
                description = :description,
                url = :url,
                is_protected = :is_protected,
                follower_count = :follower_count,
                post_count = :post_count,
                ".($has_friend_count ? "friend_count= :friend_count, " : "")."
                ".($has_last_post ? "last_post= :last_post, " : "")."
                last_updated = NOW(),
                found_in = :found_in, 
                joined = :joined,
                network = :network
                ".($has_last_post_id ? ", last_post_id = :last_post_id" : "").";";
        $vars = array(
            ':user_id'=>$user->user_id,
            ':username'=>$user->username,
            ':full_name'=>$user->full_name,
            ':avatar'=>$user->avatar,
            ':location'=>$user->location,
            ':description'=>$user->description,
            ':url'=>$user->url,
            ':is_protected'=>$user->is_protected,
            ':follower_count'=>$user->follower_count,
            ':post_count'=>$user->post_count,
            ':found_in'=>$user->found_in,
            ':joined'=>$user->joined,
            ':network'=>$network
        );
        if ($has_friend_count) {
            $vars[':friend_count'] = $user->friend_count;
        }
        if ($has_last_post) {
            $vars[':last_post'] = $user->last_post;
        }
        if ($has_last_post_id) {
            $vars[':last_post_id'] = $user->last_post_id;
        }
        $ps = $this->execute($q, $vars);
        $results = $this->getUpdateCount($ps);
        if ($results > 0) {
            $this->logger->logStatus("User ".$user->username." updated in system.", get_class($this));
        }
        return $results;
    }

    public function getDetails($user_id) {
        $q = "SELECT * , ".$this->getAverageTweetCount()." ";
        $q .= "FROM #prefix#users u ";
        $q .= "WHERE u.user_id = :user_id;";
        $vars = array(
            ':user_id'=>$user_id
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsObject($ps, "User");
    }

    public function getUserByName($user_name) {
        $q = "SELECT * , ".$this->getAverageTweetCount()." ";
        $q .= "FROM #prefix#users u ";
        $q .= "WHERE u.user_name = :user_name;";
        $vars = array(
            ':user_name'=>$user_name
        );
        $ps = $this->execute($q, $vars);
        return $this->getDataRowAsObject($ps, "User");
    }
}