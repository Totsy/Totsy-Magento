var checkoutPayment = {};

jQuery(document).ready(function() {
    var billAddySelect = jQuery("#billing-address-select");
    var newCardWrap = jQuery('.cc_info');
    //var newCardCtrls = jQuery( 'input, select', '.use-new-card-wrapper' );
    var billingAddress = jQuery('.totsy-selective-input-box', '#hpcheckout-billing-wrapper');
    var billFormInputs = jQuery('#hpcheckout-billing-form :input');
    checkoutPayment = (function() {
        var hasProfile = "";
        var isCollapsed = "";
        var lastUsedAddressId = "";
        return {
            hasProfile: "",
            isCollapsed: false,
            lastUsedAddressId: "",
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
                    //newCardWrap.hide();

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
            getCreditCardType: function(ccNum) {
                //start without knowing the credit card type
                var result = "unknown";
                //first check for MasterCard
                if (/^5[1-5]/.test(ccNum)) {
                    result = "mc";
                } //then check for Visa
                else if (/^4/.test(ccNum)) {
                    result = "vi";
                } //then check for AmEx
                else if (/^3[47]/.test(ccNum)) {
                    result = "ae";
                } else if (/^6011[0-9]{12}$/.test(ccNum) || (/^[0-9]{3}$/.test(ccNum))) {
                    result = "di";
                }
                return result;
            },
            setPaymentType: function() {
                if (jQuery(this).val() == '') {
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
                    this.disableAddress(false, 'hpcheckout-billing-form');
                    billFormInputs.each(function(i) {
                        jQuery(this).val('');
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
                    if (this.isCollapsed == false) {
                        billAddySelect.val(jQuery("#address_" + jQuery(this).val()).val()).change();
                        //Block Billing Inputs if credit card selected
                        this.disableAddress(true, 'hpcheckout-billing-form');
                        billAddySelect.attr('disabled', true);
                    } else {
                        this.disableAddress(false, 'hpcheckout-billing-form');
                        billAddySelect.removeAttr('disabled');
                    }
                }
            },
            autoDetectCard: function(ccNum) {                
                var ccEntered = this.getCreditCardType(jQuery("#" + ccNum).val());
                var isCard = false;
                jQuery("#paymentfactory_tokenize_cc_type .cc").children().each(function(i, k) {
                    var temp = jQuery("#" + k.id);
                    if (ccEntered == temp.attr('id')) {
                        //console.log("found a match");
                        temp.removeClass(temp.attr('class')).addClass(temp.attr('data-active'));
                        isCard = true;
                    } else {
                        temp.removeClass(temp.attr('data-active')).addClass(temp.attr('data-inactive'));
                    }
                });
                if (isCard == true) {
                    //unset saved card
                    jQuery('[id="payment[cybersource_subid]"]').attr("checked", false);
                    jQuery('[id="payment[cc_type]"]').val(ccEntered.toUpperCase());
                    //enable address dropdown
                    billAddySelect.attr('disabled', false);
                } else {
                    //if a saved card was selected, reselect it
                    if (jQuery('[id="payment[cybersource_subid]"]').val() !== "") {
                        jQuery('[id="payment[cybersource_subid]"]').val(this.lastUsedAddressId);
                        jQuery('[id="payment[cybersource_subid]"]').attr("checked", true);
                    }
                    jQuery('[id="payment[cc_type]"]').val("");
                    billAddySelect.attr('disabled', true);
                }
            },
            paymentToggle: function() {
                if (jQuery("#payment_form_paypal_express").length > 0) {
                    jQuery("#payment_form_paypal_express").hide();
                }
                jQuery("#cc_data").show();
                jQuery(".cc_info").css({
                    'opacity': 100
                });
                jQuery(".cards").css({
                    'opacity': 100
                });
                jQuery('input[name="payment[method]"]').val("paymentfactory_tokenize");
                jQuery('[id="paypal_payment"]').val("");
                hpcheckout.switchPaymentMethod('paymentfactory_tokenize');
                newCardWrap.show();
                jQuery("#add_payment_toggle").hide();
            },
            setPaypal: function() {
                jQuery("#add_payment_toggle").show();
                newCardWrap.hide();
                jQuery('#billing-address').hide();
                jQuery('#shipping-address').hide();
                //grey out card icons
                jQuery(".cards").css({
                    'opacity': .5
                });
                //uncheck saved card option
                jQuery('[id="payment[cybersource_subid]"]').attr("checked", false);
                //set hidden for variable 
                jQuery('input[name="payment[method]"]').val("paypal_express");
                jQuery('[id="paypal_payment"]').val("paypal_express");
                //switch payment to send right data to backend   
                hpcheckout.switchPaymentMethod('paypal_express');
            },
            useSavedCard: function() {
                //jQuery("#paymentfactory_tokenize_cc_type input").attr("checked", false);        
                this.disableAddress(true, 'hpcheckout-billing-form');
                jQuery('#billing-address-select').attr('disabled', true);
            }
        };
    })();
});