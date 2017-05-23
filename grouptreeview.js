jQuery(document).ready(function ($) {
  $('#jstree-groups').jstree({
    'core' : {
      'data' : {
        "url" : window.location.pathname + '?snippet=json' + '&filter=' + CRM.vars.grouptreeviewfilter.filter,
        "dataType" : "json", 
        "data" : function (node) {
          return { "id" : node.id };
        }
      }
    },
    "types" : {
      "Group" : {
        "valid_children" : [ "Group", "Organization", "Household", "Individual" ]
      },
      "Organization" : {
        "icon" : "icon crm-icon Organization-icon",
        "valid_children" : []
      },
      "Household" : {
        "icon" : "icon crm-icon Household-icon",
        "valid_children" : []
      },
      "Individual" : {
        "icon" : "icon crm-icon Individual-icon",
        "valid_children" : []
      }
    },
    "themes" : {
      "name" : false,
      "stripes" : true,
      "icons" : false
    },
    "multiple" : false,
    "state" : { "key" : "groups_state" },
    "plugins" : ["types", "state"]
  });
  $('#jstree-groups').on("before_open.jstree", function (e, data) {
    //remove the jstree icon classes from contact nodes, as they 
    //interfere with the civicrm icon sprites
    $(".crm-icon").removeClass("jstree-icon jstree-themeicon jstree-themeicon-custom");
    //override the default click to open the contact view page
    $(".contact-link").on("click", function() {
      window.location = $(this).attr('href');
    });
  });
});
