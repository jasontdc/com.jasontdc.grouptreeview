<?php

require_once 'CRM/Core/Page.php';

class CRM_Grouptreeview_Page_GroupTreeView extends CRM_Core_Page {
  public function run() {
    $id = CRM_Utils_Request::retrieve('id', 'String');
    $json = CRM_Utils_Request::retrieve('snippet', 'String');
    $filter = CRM_Utils_Request::retrieve('filter', 'String');

    $ids = explode("-", $id);
    if(sizeof($ids) > 1) {
      $groupid = $ids[1];
    } else if($filter) {
      $groupid = $filter;
    }

    if($json == 'json') {
      $data = array();
      if($groupid) {
        //get the groups with this as their parent
        $groups = civicrm_api3('Group', 'get', array(
          'sequential' => 1,
          'parents' => array('LIKE' => "%$groupid%"), $groupid,
        ));
        if($groups['is_error'] == 0 && $groups['count'] > 0) {
          foreach($groups['values'] as $group) {
            array_push($data, array(
              'id' => "$groupid-" . $group['id'],
              'text' => implode(" - ", array_filter(array($group['title'],$group['description']))),
              'type' => 'Group',
              'children' => true
            ));
          }
        }
        //get the contacts in this group
        $contacts = civicrm_api3('Contact', 'get', array(
          'sequential' => 1,
          'group' => $groupid,
        ));
        if($contacts['is_error'] == 0 && $contacts['count'] > 0) {
          foreach($contacts['values'] as $contact) {
            array_push($data, array(
              'id' => "contact-$groupid-" . $contact['id'],
              'type' => $contact['contact_type'],
              'text' => implode(' - ', array_filter(array($contact['display_name'], $contact['phone'], $contact['email']))),
              'a_attr' => array(
                'href' => CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid=" . $contact['id']),
                'class' => 'contact-link'
              )
            ));
          }
        }
      } else {
        $groups = civicrm_api3('Group', 'get', array(
          'sequential' => 1,
        ));
        if($groups['is_error'] == 0 && $groups['count'] > 0) {
          foreach($groups['values'] as $group) {
            if(!isset($group['parents'])) {
              array_push($data, array(
                'id' => "0-" . $group['id'],
                'text' => implode(" - ", array_filter(array($group['title'],$group['description']))),
                'type' => 'Group',
                'children' => true
              ));
            }
          }
        }
      }
      echo json_encode($data);
      CRM_Utils_System::civiExit();
    } else {
      //jstree requires jquery version 1.9.0 or later
      //if using Drupal, the jQuery Update module must be used, or some other method of upgrading jQuery must be used
      CRM_Core_Resources::singleton()->addStyleUrl('//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.3/themes/default/style.min.css');
      CRM_Core_Resources::singleton()->addScriptUrl('//cdnjs.cloudflare.com/ajax/libs/jstree/3.3.3/jstree.min.js');
      CRM_Core_Resources::singleton()->addStyleFile('com.jasontdc.grouptreeview', 'grouptreeview.css');
      CRM_Core_Resources::singleton()->addScriptFile('com.jasontdc.grouptreeview', 'grouptreeview.js');

      $title = "Groups";
      //if we're filtering by a specific group, change the page title
      if($filter) {
        try {
          $group = civicrm_api3('Group', 'getsingle', array(
            'sequential' => 1,
            'id' => $filter,
          ));
          if($group['id'] == $filter) {
            $title = $group['title'];
            CRM_Core_Resources::singleton()->addVars('grouptreeviewfilter', array('filter' => $filter));
          }
        } catch(Exception $e) {
          //do nothing
        }
      } else {
        CRM_Core_Resources::singleton()->addVars('grouptreeviewfilter', array('filter' => ''));
      }
      CRM_Utils_System::setTitle($title);
    }
  parent::run();
  }
}
