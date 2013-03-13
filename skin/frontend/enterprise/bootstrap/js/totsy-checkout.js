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
                        'width': '60px',
                        'margin-left': '10px',
                        'float': 'left'
                    };
                    jQuery('#billing-address-select').attr('disabled', true);
                    jQuery(".cc_save_card").css(savedCardStyles);
                    jQuery("#hpcheckout-payment-add-title").show();
                    jQuery("#use-card-method").show();
                    jQuery("#paymentfactory_tokenize_cc_save").contents("");
                    jQuery(".use-new-card-wrapper").show();
                    jQuery("#cc_save_text").html("Save");
                    jQuery(".use-new-card-wrapper").appendTo(jQuery("#add_cc_types"));
                    newCardWrap.hide();
                } else {
                    jQuery("[id='add_payment']").hide();
                    jQuery(".checkout-reward").css("padding-top", "0px");
                    jQuery(".use-new-card-wrapper").show();
                    jQuery("#use-card-method").hide();
                    jQuery("#hpcheckout-payment-add-title").hide();
                    jQuery("#cc_data").show();
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
                    //billingAddress.attr("disabled", true);
                    newCardWrap.hide();
                    if (checkoutPayment.isCollapsed == false) {
                        //billAddySelect.val(jQuery("#address_" + jQuery(elem).val()).val()).change();
                        //Block Billing Inputs if credit card selected
                        this.disableAddress(true, 'hpcheckout-billing-form');
                        billAddySelect.attr('disabled', true);
                    } else {
                        this.disableAddress(false, 'hpcheckout-billing-form');
                        billAddySelect.removeAttr('disabled');
                    }
                }
            },
            useSavedCard: function() {
                jQuery("#paymentfactory_tokenize_cc_type input").attr("checked", false);
                jQuery('#billing-address-select').attr('disabled', true);
            },
            setPaymentType: function(elem) {
                if (elem.id == "paypal_payment") {
                    newCardWrap.hide();
                } else {
                    jQuery("[id='payment[cc_vaulted]']").attr("checked", false);
                    jQuery('#billing-address-select').attr('disabled', false);
                    newCardWrap.show();
                }
            }
        };
    })();
});