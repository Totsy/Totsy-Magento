var checkoutPayment = {};

jQuery(document).ready(function() {
    var billAddySelect = jQuery("#billing-address-select");
    var newCardWrap = jQuery('.cc_info');
    //var newCardCtrls = jQuery( 'input, select', '.use-new-card-wrapper' );
    var billingAddress = jQuery('.totsy-selective-input-box', '#hpcheckout-billing-wrapper');
    var billFormInputs = jQuery('#hpcheckout-billing-form :input');
    //a namespace for operations toggling the 2 views of the payment section
    checkoutPayment = (function() {
        var hasProfile = '';
        var isCollapsed = '';
        var lastUsedAddressId = '';
        return {
            hasProfile: '',
            isCollapsed: false,
            lastUsedAddressId: '',
            toggleViews: function() {
                if (this.hasProfile) {                
                    jQuery(".cc_save_card").appendTo(jQuery("#add_payment_save_card"));
                    var savedCardStyles = {
                        'width': 'auto',
                        'margin-left': '10px',
                        'vertical-align': 'middle',
                        'float': 'left'
                    };
                    jQuery('#billing-address-select').attr('disabled', true);
                    jQuery(".cc_save_card").css(savedCardStyles);
                    jQuery("#hpcheckout-payment-add-title").show();
                    jQuery("#use-card-method").show();
                    jQuery("#creditcard_cc_type_should_save_div").contents("");
                    jQuery(".use-new-card-wrapper").show();
                    jQuery("#cc_save_text").html("Save");
                    jQuery(".use-new-card-wrapper").appendTo(jQuery("#add_cc_types"));
                    newCardWrap.hide();
                } else {  
                    jQuery("[id='add_payment']").hide();
                    jQuery(".checkout-reward").css("padding-top", "0px");
                    jQuery(".use-new-card-wrapper").show();
                    jQuery("#use-card-method").hide();
                    jQuery("#cc_data").show();                    
                    
                    console.log("test");
                    jQuery("#hpcheckout-payment-add-title").hide();
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
            useSavedCard: function() {
                jQuery('#billing-address-select').attr('disabled', true);
            },
            setPaymentType: function(elem) {
                if (elem.id == "paypal_payment") {
                    jQuery("[name='payment[cc_type]']").attr("checked", false);
                    newCardWrap.hide();
                } else {
                    jQuery('input[name="payment[method]"]').val("creditcard");
                    jQuery("#paypal_payment").attr("checked", false);
                    jQuery('#billing-address-select').attr('disabled', false);
                    newCardWrap.show();
                }
            }
        };
    })();
});