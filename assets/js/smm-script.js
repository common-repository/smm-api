jQuery(document).ready(function(){
jQuery(".n_item_product").click(function(){
        var href = jQuery(this).attr("href").split("#");
        ajax("n_item_product", href[1]);
        });

jQuery("[id*=parameter_]").click(function(){
    var href = jQuery(this).attr("href").split("#");
    ajax("server_display", href[1]);
		jQuery(".server-display-table").fadeIn("fast");
	});
jQuery(".server-display-table").click(function(){
    jQuery(".server-display-table").fadeOut("fast");
    });
jQuery("[id*=more_]").click(function(){
    var href = jQuery(this).attr("href").split("#");

jQuery(".item-display-table").fadeIn("fast");
    ajax("f_item_display", href[1]);
	});
jQuery(".item-display-table").click(function(){
    jQuery(".item-display-table").fadeOut("fast");
    });
jQuery('#f_api_server_list').on('change', function() {
    var value = jQuery(this).val();
	
    ajax("server_list", value);
});
jQuery('#_smapi_server_name_option').on('change', function() {
    var value = jQuery(this).val();
    var smmid = jQuery('.smapi_server_name').attr('id');
    ajax("server_product_list", value, smmid);
});


jQuery("#server_save").click(function(){

		ajax("server_save");
	});
jQuery("#server_demo").click(function(){

		ajax("server_demo");
	});

jQuery(".server_edit").click(function(){
        var href = jQuery(this).attr("href").split("#");
        ajax("server_edit", href[1]);

     jQuery(".server-entry-form").fadeIn("fast");

    });
jQuery(".server_delete").click(function(){
        var href = jQuery(this).attr("href").split("#");
        ajax("server_delete", href[1]);
        });
jQuery("#f_item_save").click(function(){
		ajax("f_item_save");
	});
jQuery("#server_clear").click(function(){
		jQuery(".server-entry-form").find("input[type=text], textarea").val("");
		jQuery("input[name=fsid]").val("");
	});
jQuery("#server_cancel").click(function(){
		jQuery(".server-entry-form").fadeOut("fast");
	});
jQuery("#add_new_server").click(function(){

		jQuery(".server-entry-form").fadeIn("fast");
		jQuery("input[name=fsid]").val("");

	});
jQuery(".f_item_edit").click(function(){
        var href = jQuery(this).attr("href").split("#");
        ajax("f_item_edit", href[1]);

     jQuery(".item-entry-form").fadeIn("fast");

    });
jQuery(".f_item_delete").click(function(){
        var href = jQuery(this).attr("href").split("#");
        ajax("f_item_delete", href[1]);
        });


jQuery("#f_item_clear").click(function(){
		jQuery(".item-entry-form").find("input[type=text], textarea").val("");
		jQuery("input[name=fid]").val("");
	});


jQuery("#f_item_cancel").click(function(){
		jQuery(".item-entry-form").fadeOut("fast");
	});
jQuery("#f_item_import").click(function(){
		jQuery(".item-entry-form").fadeOut("fast");
		ajax("f_item_import");
	});

jQuery("#add_new_api_item").click(function(){

		jQuery(".item-entry-form").fadeIn("fast");
		jQuery("input[name=f_meta_id]").val("");

	});

	function ajax(action,id,num=0){
		if(action =="f_item_save")
			data = jQuery("#f_item_info").serialize()+"&action="+action;
		else if(action == "f_item_edit"){
			data = "action="+action+"&f_meta_key="+id;

		}
		else if(action == "f_item_import"){
			data = "action="+action;

		}
		else if(action == "n_item_product"){
					data = "action="+action+"&f_meta_key="+id;
					}
		else if(action == "f_item_delete"){
			data = "action="+action+"&f_meta_key="+id;

		}
		else if(action == "f_item_display"){
			data = "action="+action+"&f_meta_key="+id;

		}
		else if(action == "server_product_list"){
			data = "action="+action+"&f_smapi_server_name_option="+id+"&smmid="+num;

		}
		else if(action == "var_server_product_list"){
			data = "action="+action+"&var_smapi_server_name_option="+id;

		}
		else if(action == "var_service_span_data"){
			data = "action="+action+"&var_smapi_service_id_option="+id;

		}
		else if(action == "server_list"){
			data = "action="+action+"&f_api_server_list="+id;

		}
		else if(action == "server_save"){
			data = jQuery("#server_info").serialize()+"&action="+action;
				}
		else if(action == "server_delete"){
			data = "action="+action+"&item_id="+id;
				}
		else if(action == "server_edit"){
			data = "action="+action+"&item_id="+id;

		}
		else if(action == "server_display"){
			data = "action="+action+"&item_id="+id;

		}
		else if(action == "server_demo"){
			data = "action="+action;

		}



		jQuery.ajax({
			type: "POST",
			url: ajaxurl,
			data : data,
			dataType: "json",
			success: function(response){

				if(response.success == "1"){

					if(action == "f_item_save"){
					SmmAdminNotice(response.notice, response.color);
					jQuery(".item-entry-form").fadeOut("fast",function(){location.reload();});
					}
					else if(action == "f_item_delete"){
					SmmAdminNotice(response.notice, response.color);
					location.reload();
					}
					else if(action == "n_item_product"){
					SmmAdminNotice(response.notice, response.color);
					}
					else if(action == "f_item_import"){
					//alert(response.table_api_service);
					var newdata = response.table_api_service;
                    var table = jQuery('.wp-list-table').DataTable({
                    data: newdata,
                    columns: [
                                { data: 'cb' },
                                { data: 'service' },
                                { data: 'name' },
                                { data: 'rate' },
                                { data: 'min' },
                                { data: 'max' },
                                { data: 'status' },
                                { data: 'sub' },
                                { data: 'view' }
                                ],
                    columnDefs: [ {
                                orderable: false,
                                className: 'select-checkbox',
                                targets:   0
                                } ],
                    select: {
                                style:    'single',
                                selector: 'td:first-child'
                                },
                    order: [[ 1, 'asc' ]] }
                                );
                    table.on( 'select', function ( e, dt, type, indexes ) {
                    if ( type === 'row' ) {
                        var rowdata = table.row( { selected: true } ).data();
                        rowdata.action = 'f_item_import';
                        var idstr = rowdata.service;
                        var strarray = idstr.split('<');
						var sid = strarray[0];
						rowdata.productid = jQuery('#' + sid).is(":checked");
                        rowdata.service = sid;
         //alert(JSON.stringify(rowdata));
                            jQuery.ajax({
                                            url: ajaxurl,
                                            type: 'POST',
                                             data: rowdata,
        //{ json: JSON.stringify(rowdata+data)},
                                             dataType: 'json',
                                            success: function(response){
                            SmmAdminNotice(response.notice, response.color);
                                        }
                    });


        // do something with the ID of the selected items
                    }
                    } );
					}

		else if(action == "f_item_edit"){
			jQuery('input[name=f_post_id]').val(response.f_post_id);
            jQuery('input[name=f_meta_key]').val(response.f_meta_key);
            jQuery('input[name=f_service_id]').val(response.f_service_id);
			jQuery('input[name=f_api_description]').val(response.f_api_description);
			jQuery('input[name=f_min_order]').val(response.f_min_order);
			jQuery('input[name=f_max_order]').val(response.f_max_order);
			jQuery('input[name=f_item_price]').val(response.f_item_price);

			jQuery('select[name=f_item_status]').val(response.f_item_status);


    		jQuery('input[name=f_item_post_count]').val(response.f_item_post_count);
			jQuery('input[name=f_item_post_delay]').val(response.f_item_post_delay);
					//jQuery('input[name=f_item_post_ex_date]').val(response.f_item_post_ex_date);

			jQuery('select[name=f_item_subscribe_check]').val(response.f_item_subscribe_check);


					}
		else if(action == "f_item_display"){

    					jQuery('#display_service_id').html(response.display_service_id) ;
    					jQuery('#display_api_description').html(response.display_api_description) ;
    					jQuery('#display_min_order').html(response.display_min_order) ;
    					jQuery('#display_max_order').html(response.display_max_order) ;
    					jQuery('#display_item_price').html(response.display_item_price) ;
    					jQuery('#display_item_status').html(response.display_item_status) ;
    					jQuery('#display_item_post_count').html(response.display_item_post_count) ;
    					jQuery('#display_item_post_delay').html(response.display_item_post_delay) ;
    					//jQuery('#display_api_item_post_ex_date').html(response.display_api_item_post_ex_date) ;
    					jQuery('#display_item_subscribe_check').html(response.display_item_subscribe_check) ;

    					}
    	else if(action == "server_product_list"){
					jQuery('select[name=_smapi_server_name_option]').val(response.f_smapi_server_name_option) ;
                    var sdata = response.option_data;
                    //alert(sdata);
                    jQuery('select[name=_smapi_service_id_option]').empty();
                    jQuery('select[name=_smapi_service_id_option]').append(sdata);
					
					}
		else if(action == "var_server_product_list"){
					//jQuery('select[name=var_smapi_server_name_option_'+num).val(response.var_smapi_server_name_option) ;
                    var sdata = response.option_data;
                    //alert(response.var_smapi_server_name_option);
                    //jQuery('select[name=var_smapi_service_id_option['+num+']').empty();
                    jQuery('#var_smapi_service_id_option_'+num).empty();
                    jQuery('#var_smapi_service_id_option_'+num).append(sdata);
					jQuery('#var_smapi_service_span_option_'+num).text(response.span_data);
					}
		else if(action == "var_service_span_data"){
					//changes span data for item selected
					var sdata = response.option_data;
                    
                   
					jQuery('#var_smapi_service_span_option_'+num).text(response.span_data);
					}			
    	else if(action == "server_list"){
					jQuery('select[name=f_api_server_list]').val(response.f_api_server_list) ;

					location.reload();
					}
		else if(action == "server_edit"){

			jQuery('input[name=fsid]').val(response.id);
			jQuery('input[name=fapi_url]').val(response.api_url);
			jQuery('input[name=fapi_key_handle]').val(response.api_key_handle);
			jQuery('input[name=fapi_key]').val(response.api_key);
		    jQuery('input[name=fapi_link_handle]').val(response.api_link_handle);
	        jQuery('input[name=fapi_service_handle]').val(response.api_service_handle);
            jQuery('input[name=fapi_quantity_handle]').val(response.api_quantity_handle);
            jQuery('input[name=fapi_order_response_handle]').val(response.api_order_response_handle);
			jQuery('input[name=fapi_error_response_handle]').val(response.api_error_response_handle);
			jQuery('input[name=fapi_retrieve_status_query]').val(response.api_retrieve_status_query);
			jQuery('input[name=fapi_status_order_handle]').val(response.api_status_order_handle);
			jQuery('select[name=fapi_server_status]').val(response.api_server_status);
    					}
		else if(action == "server_save"){
					SmmAdminNotice(response.notice, response.color);
					jQuery(".server-entry-form").fadeOut("fast",function(){location.reload();});
					}
					else if(action == "server_demo"){
					SmmAdminNotice(response.notice, response.color);
					jQuery(".server-entry-form").fadeOut("fast",function(){location.reload();});
					}
					else if(action == "server_delete"){
					SmmAdminNotice(response.notice, response.color);
					location.reload();
					}

    	else if(action == "server_display"){

    					jQuery('#display_api_url').html(response.display_api_url) ;
    					jQuery('#display_api_key_handle').html(response.display_api_key_handle) ;
    					jQuery('#display_api_key').html(response.display_api_key) ;
    					jQuery('#display_api_link_handle').html(response.display_api_link_handle) ;
    					jQuery('#display_api_service_handle').html(response.display_api_service_handle) ;
    					jQuery('#display_api_quantity_handle').html(response.display_api_quantity_handle) ;
    					jQuery('#display_api_order_response_handle').html(response.display_api_order_response_handle) ;
    					jQuery('#display_api_error_response_handle').html(response.display_api_error_response_handle) ;
    					jQuery('#display_api_retrieve_status_query').html(response.display_api_retrieve_status_query) ;
    					jQuery('#display_api_status_order_handle').html(response.display_api_status_order_handle) ;
    					jQuery('#display_api_server_status').html(response.display_api_server_status) ;
    					}

					}
			        if(response.success == "0"){
					var res= "";
					var str3 = "\n";
    					jQuery.each( response, function( key, value ) {
  					res +=  key + ": " + value + str3 ;
					});
				alert("EMPTY FIELDS ARE NOT ACCEPTABLE."+ str3 + res);
					}
				},
			error: function(xhr, status, error) {
                    
                    alert(xhr.responseText + data);
                    }
			});
			}
			/**
 * Create and show a dismissible admin notice
 */
    function SmmAdminNotice( msg, colour ) {


    /* create notice div  notice-info notice-success notice-error notice-warning*/

    var div = document.createElement( 'div' );
    div.classList.add( 'notice', 'inline', colour , 'is-dismissible');

    /* create paragraph element to hold message */

    var p = document.createElement( 'p' );

    /* Add message text */

    p.appendChild( document.createTextNode( msg ) );

    // Optionally add a link here

    /* Add the whole message to notice div */

    div.appendChild( p );

    /* Create Dismiss icon */

    var b = document.createElement( 'button' );
    b.setAttribute( 'type', 'button' );
    b.classList.add( 'notice-dismiss' );

    /* Add screen reader text to Dismiss icon */

    var bSpan = document.createElement( 'span' );
    bSpan.classList.add( 'screen-reader-text' );
    bSpan.appendChild( document.createTextNode( 'Dismiss this notice' ) );
    b.appendChild( bSpan );

    /* Add Dismiss icon to notice */

    div.appendChild( b );

    /* Insert notice after the first h1 */

    var h1 = document.getElementsByClassName( 'tablenav' )[0];
    h1.parentNode.insertBefore( div, h1.nextSibling);


    /* Make the notice dismissable when the Dismiss icon is clicked */

    p.addEventListener( 'click', function () {
        div.parentNode.removeChild( div );
    });
	b.addEventListener( 'click', function () {
        div.parentNode.removeChild( div );
    });


}
jQuery(document).on('change', 'select', function(){
    
    
    var smm_str         = jQuery(this).attr('id');
    var itemspan        = jQuery('option:selected', this).attr('data-desc');
    var item            = jQuery(this).find(":selected").val();
    if(itemspan == null){  
    var itemsret        = item .split("data-desc");
        itemspan        = itemsret[1];
    };
    var prefix          = "var_smapi_server_name_option_";
    var prefixitem      = "var_smapi_service_id_option_";
    var num             = parseInt(smm_str.substring(prefix.length), 10);
    var numb            = parseInt(smm_str.substring(prefixitem.length), 10);
    if (num >= 0 ) {
    ajax("var_server_product_list", item, num);
    }
    if (numb >= 0 ) {
    ajax("var_service_span_data", itemspan, numb);
    }
    
    
            
        });

});