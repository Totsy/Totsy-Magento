var checkoutPayment = {};

jQuery(document).ready(function() {
    var billAddySelect = jQuery("#billing-address-select");
    var newCardWrap = jQuery('.cc_info');
    var billFormInputs = jQuery('#hpcheckout-billing-form :input');
    //a namespace for operations toggling the 2 views of the payment section
    checkoutPayment = (function() {
        var hasProfile = '';
        var isCollapsed = '';
        var lastUsedAddressId = '';
        var isLitle = true;
        return {
            hasProfile: '',
            isLitle: false,
            isCollapsed: false,
            lastUsedAddressId: '',
            toggleViews: function() {
                if (this.hasProfile!=="1") { 
                    jQuery(".add_payment_separator").hide();
                    jQuery(".checkout-reward").css("padding-top", "0px");
                    jQuery(".use-new-card-wrapper").show();
                    jQuery("#use-card-method").hide();
                    jQuery("#cc_data").show();
                    jQuery("#hpcheckout-payment-add-title").hide(); 
                } else { 
                    jQuery(".cc_save_card").appendTo(jQuery("#add_payment_save_card"));
                    jQuery('#billing-address-select').attr('disabled', true);
                    jQuery(".cc_save_card").css({'width': 'auto','margin-left': '10px','float': 'left'});
                    jQuery("#hpcheckout-payment-add-title").show();
                    jQuery("#use-card-method").show();
                    jQuery("#creditcard_cc_type_should_save_div").contents("");
                    jQuery(".use-new-card-wrapper").show();
                    jQuery("#cc_save_text").html("Save");
                    jQuery(".use-new-card-wrapper").appendTo(jQuery("#add_cc_types"));
                    newCardWrap.hide();
                }
            },
            disableAddress: function(stateFlag, formId) {
                jQuery('#' + formId + ' :input').each(function(i) {
                    if (this.id !== "button_ship_to") {
                        jQuery("[id='" + this.id + "']").attr('disabled', stateFlag);
                    }
                });
            },
            setPaymentUI: function(elem) {
                if (jQuery(elem).val() == '') {
                    if (jQuery('#paypal_payment').length > 0) {
                        jQuery('#paypal_payment').attr("checked", false);
                    }
                    if (jQuery("#payment_form_paypal_express").length > 0) {
                        jQuery("#payment_form_paypal_express").show();
                    }
                    jQuery("#cc_data").show();
                    newCardWrap.show();
                    billAddySelect.removeAttr('disabled');
                    //Enable Billing Inputs if credit card not selected
                    checkoutPayment.disableAddress(false, 'hpcheckout-billing-form');
                    billFormInputs.each(function(i) {
                        jQuery(elem).val('');
                    });
                } else {
                    if (jQuery("#payment_form_paypal_express").length > 0) {
                        jQuery("#payment_form_paypal_express").hide();
                    }
                    jQuery('#billing-address').show();
                    jQuery('#shipping-address').show();
                    jQuery('.addresses').width(445);
                    newCardWrap.hide();
                    
                    jQuery("[name='payment[cc_type]']").attr("checked", false);
                    
                    if (checkoutPayment.isCollapsed == false) {
                        this.disableAddress(true, 'hpcheckout-billing-form');
                        billAddySelect.attr('disabled', true);
                    } else {
                        this.disableAddress(false, 'hpcheckout-billing-form');
                        billAddySelect.removeAttr('disabled');
                    }
                }
            },
            useSavedCard: function(elem) {
                //setting payment method depending on the payment gateway being used (Litle vs Cybersource)
                if ( jQuery(elem).attr('name')=="payment[cybersource_subid]" ) {
                    jQuery("[name='payment[cc_vaulted]']").attr("checked", false);
                    jQuery('input[name="payment[method]"]').val("paymentfactory_tokenize");
                    this.isLitle = false;
                } else {
                    jQuery("[name='payment[cybersource_subid]']").attr("checked", false);
                    jQuery('input[name="payment[method]"]').val("creditcard");
                    this.isLitle = true;
                }
            
                jQuery('#billing-address-select').attr('disabled', false);               
                jQuery('#billing-address-select').val(jQuery(elem).attr("data-billing-address-id"));
                
                //switch the address, but first remove the lock on the form fields
                this.disableAddress(false, 'hpcheckout-billing-form');                
                hpcheckout.switchAddress('billing-address-select');
                
                //lock the form fields again
                this.disableAddress(true, 'hpcheckout-billing-form'); 
                
                jQuery('#billing-address-select').attr('disabled', true);
            },
            setPaymentType: function(elem) {
                //unselect ANY saved cards
                jQuery("[name='payment[cc_vaulted]'],[name='payment[cybersource_subid]']").attr("checked", false);
            
                if (elem.id == "paypal_payment") {
                    jQuery("[name='payment[cc_type]']").attr("checked", false);
                    jQuery("#payment_form_paypal_express").appendTo(jQuery("#paypal_container"));
                    jQuery("#payment_form_paypal_express").show();
                    newCardWrap.hide();
                } else {
                    if (jQuery("#paypal_payment").length > 0) {
                        jQuery("#payment_form_paypal_express").hide();
                    }
                
                    if (jQuery("[name='payment[cybersource_subid]']").attr("checked")) {
                        this.isLitle = false;
                    } else {
                        this.isLitle = true;
                    }
                    
                    jQuery('#billing-address').show();
                    jQuery('#shipping-address').show();
                    jQuery("#paypal_payment").attr("checked", false);
                    jQuery('#billing-address-select').attr('disabled', false);
                    newCardWrap.show();
                }
            }
        };
    })();
 });