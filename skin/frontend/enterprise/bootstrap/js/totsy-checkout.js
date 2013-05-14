var checkoutPayment = {};
var bTest = true;
var hasCBProfile = false;
jQuery(document).ready(function() {
    var billAddySelect = jQuery("#billing-address-select");
    var shipAddySelect = jQuery("#shipping-address-select");
    var newCardWrap = jQuery('.cc_info');
    var billFormInputs = jQuery('#hpcheckout-billing-form :input');
    checkoutPayment = (function() {
        var hasProfile = '';
        var isCollapsed = '';
        var lastUsedAddressId = '';
        var isLitle = true;
        var isEnoughPointsToCoverAmount = '';
        var isRewardUsed = '';
        var zipSearchResults = '';
        return {
            hasProfile: '',
            isLitle: false,
            isCollapsed: false,
            lastUsedAddressId: '',
            isEnoughPointsToCoverAmount: '',
            isRewardUsed: '',
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
                    jQuery('[name="payment[cc_type]"]').attr("checked", false);
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
            useRewardsPoints: function() {
                var paymentMethod = "creditcard";
                if (checkoutPayment.isEnoughPointsToCoverAmount) {
                    jQuery('input[name="payment[method]"]').val('free');
                }
                jQuery('#reward-points-input').val(1);
                jQuery("#reward_placer").css("padding-top", "0px");
                //jQuery("#credits_and_card_save").hide();
                //once the entire order is covered, no point in showing this stuff
                jQuery("#use-card-method").hide();
                jQuery('#add_payment').hide();
                jQuery('#paidbyreward').show();
                jQuery('#applyreward').hide();
                jQuery('#' + paymentMethod + '_cc_cid').removeClass('required-entry');
                jQuery('#' + paymentMethod + '_expiration').removeClass('required-entry');
                jQuery('#' + paymentMethod + '_expiration_yr').removeClass('required-entry');
                hpcheckout.updatePayment();
                hpcheckout.update(true);
            },
            useSavedCard: function(elem) {
                //setting payment method depending on the payment gateway being used (Litle vs Cybersource)
                if (jQuery(elem).attr('name') == "payment[cybersource_subid]") {
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
            },
            autoDetectCard: function(elem) {
                var ccEntered = this.getCreditCardType(jQuery("#" + elem.id).val());
                var isCard = false;
                jQuery("[name='payment[cybersource_subid]']").attr("checked", false);
                jQuery("[name='payment[cc_vaulted]']").attr("checked", false);
                jQuery(".cc_types .cc").children().each(function(i, k) {
                    var temp = jQuery("#" + k.id);
                    if (ccEntered == temp.attr('id')) {
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
                    jQuery('[name="payment[cc_type]"]').val("");
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
            validateCardType: function() {
                var ccNum = jQuery("[name='payment[cc_number]']").val();
                var ccType = jQuery("[name='payment[cc_type]']:checked").val();
                //start without knowing the credit card type
                var result = "unknown";
                //first check for MasterCard
                if (/^5[1-5]/.test(ccNum)) {
                    result = "MC";
                } //then check for Visa
                else if (/^4/.test(ccNum)) {
                    result = "VI";
                } //then check for AmEx
                else if (/^3[47]/.test(ccNum)) {
                    result = "AE";
                } else if (/^6011[0-9]{12}$/.test(ccNum) || (/^[0-9]{3}$/.test(ccNum))) {
                    result = "DI";
                }
                if (result == ccType || !ccNum) {
                    jQuery("#cc-num-error-message").hide();
                    return true;
                } else {
                    //jQuery("[name='payment[cc_number]']").addClass('validate-cc-number validate-cc-type');
                    jQuery("#cc-num-error-message").show();
                    return false;
                };
            },
            disableZipLookup: function() {
                console.log(this.zipSearchResults);
                if (this.zipSearchResults == 2) {
                    jQuery("[id$=postcode]").unbind("keyup");
                }
            },
            getCityAndStateByZip: function(formId) {
                if (bTest == true) {
                    //register events to the right form by type. 'type' could be billing or shipping
                    var addressFormType = '';
                    var payment = this;
                                        
                    if (formId) {
                        addressFormType = formId;
                    }
                    if (addressFormType == "shipping") {
                        selectedAddress = shipAddySelect.val();
                    } else {
                        selectedAddress = billAddySelect.val();
                    }
                    if (selectedAddress == "" || typeof selectedAddress == "undefined") {
                        //hide these. fields when the user selects "new address"
                        jQuery("#" + addressFormType + "_city_and_state").fadeOut();
                        jQuery("#" + addressFormType + "_zip_info_message").fadeIn();
                        jQuery("[id='" + addressFormType + ":postcode']").bind("keyup change", function() {
                            if (this.value.length >= 5) {
                            
                                jQuery("#" + addressFormType + "_zip_info_message").fadeOut();
                                jQuery.ajax({
                                    url: "/customer/zipCodeInfo/lookup",
                                    dataType: "json",
                                    type: "POST",
                                    data: {
                                        zip: jQuery("[id='" + addressFormType + ":postcode']").val()
                                    },
                                    error: function(data) {
                                        jQuery("#" + addressFormType + "_city_and_state").show();
                                        //jQuery("#" + addressFormType + "_zip_info_message").hide();
                                    },
                                    success: function(response) {                                    
                                        if (typeof response[0] !== "undefined") {
                                            currentCityState = response[0];
                                            payment.zipSearchResults = 1;
                                        } else {
                                            payment.zipSearchResults = 2;
                                            jQuery("[id='" + addressFormType + ":city']").val("");
                                            jQuery("[id='" + addressFormType + ":region_id']").val("");
                                        }
                                    },
                                    complete: function() {
                                        jQuery("#" + addressFormType + "_city_and_state").fadeIn();
                                        jQuery("#" + addressFormType + "_zip_info_message").fadeOut();
                                        if (payment.zipSearchResults == 1) {
                                            //jQuery("#" + addressFormType + "_city_and_state_spinner").hide();
                                            jQuery("[id='" + addressFormType + ":city']").val(currentCityState['city']);
                                            jQuery("[id='" + addressFormType + ":region_id']").val(currentCityState['region_id']);
                                        } else {
                                            currentCityState = "";
                                            //jQuery("#" + addressFormType + "_city_and_state").fadeOut();
                                            //jQuery("#" + addressFormType + "_zip_info_message").fadeIn();
                                        }
                                    }
                                });
                            }
                        });
                    } else {
                        jQuery("#" + addressFormType + "_city_and_state").fadeIn();
                        jQuery("#" + addressFormType + "_zip_info_message").hide();
                    }
                }
            }
        };
    })();
});