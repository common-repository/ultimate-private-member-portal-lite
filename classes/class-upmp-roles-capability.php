<?php
/* Manage user role and capability functions */
class UPMP_Roles_Capability {

    private $user_roles;

    public function __construct() { }

    public function upmp_user_roles(){
        global $wp_roles;

        $roles = $wp_roles->get_names();
        return $roles;
    }

    public function upmp_user_capabilities(){
        global $wp_roles;

        $upmp_user_capabilities = array();
        foreach ($wp_roles->roles as $role_key => $role_data) {

            foreach ($role_data['capabilities'] as $capability => $val) {
                if(!in_array($capability, $upmp_user_capabilities) && $capability != ''){
                    array_push($upmp_user_capabilities, $capability);
                }
            }
        }

        return $upmp_user_capabilities;
    }
    
    /* Get the roles of the given user */
    public function get_user_roles_by_id($user_id) {
        $user = new WP_User($user_id);
        if (!empty($user->roles) && is_array($user->roles)) {
            $this->user_roles = $user->roles;
            return $user->roles;
        } else {
            $this->user_roles = array();
            return array();
        }
    }

}
