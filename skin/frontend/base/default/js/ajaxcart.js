jQuery(document).ready(function ($) {
    'use strict';

    var ajaxCart = {

        init: function () {
            this.addProduct();
            this.removeProduct();
            this.initCartAnimation();
        },
        addProduct: function () {
            var self                = this,
                minicart            = '#mcart',
                modal               = '#ajaxModal',
                minicartDropDown    = '#mcartDropdown',
                minicartActive      = 'minicart_active',
                modalOverlay        = '#ajaxModalOverlay',
                modalTabs           = '#confirm-dialog-tabs',
                ajaxRunning         = false;


            $(document).on('click', '.js-add-to-cart', function (e) {
                e.preventDefault();
                var el=$(this);

                if (!self.ajaxRunning) {
                    self.ajaxRunning = true;
                    var href=el.attr('href');
                    if ($('#wishlist').length){
                        var $input=el.closest('tr').find('.qty'),
                        href = href + 'qty/' + encodeURIComponent($input.attr('value'))
                             + '/'
                             + "&isajax=1"
                             + "&wishlist=1";
                    }
                    else if (el.attr('id') === 'addToCart') {
                        href = $('#product_addtocart_form').attr('action') + 'qty/' + $('#qty').val() + '/isajax/1';
                    }
                    else{
                        href += '&ajax=1';
                    }
                    $.ajax({
                        type: "POST",
                        url: href,
                        beforeSend: function () {
                            if ($(modal).length) {
                                $(minicart).removeClass(minicartActive);
                                $(minicartDropDown).hide();
                                $(modal).remove();
                                $(modalOverlay).remove();
                            }
                            jQuery.fancybox.showLoading();
                        },
                        success: function (result) {
                            if (result.status == 'success') {
                                if ($(window).scrollTop() > 0) {
                                    $('html, body').animate({
                                        scrollTop: 0
                                    }, 1000);
                                }
                                $('body')
                                    .removeClass('body_visible').addClass('body_hidden')
                                    .append('<div id="ajaxModalOverlay" class="ajax-modal-overlay"></div>');
                                $(minicart).replaceWith(result['content'].cart);
                                $(minicart).addClass(minicartActive);
                                $(minicartDropDown).css('display', "block");
                                $(minicart).append(result['content'].popup);
                                if ($('.my-account').length) {
                                    $('.my-account').html(result['content'].wishlist);
                                }
                                jQuery(modalTabs).tabs({
                                    fx: {
                                        opacity: 'toggle',
                                        duration: 100
                                    }
                                });

                                if (jQuery('#confirm-dialog-tab-1').length && jQuery('#confirm-dialog-tab-2').length) {
                                    var $tabs = jQuery('#confirm-dialog-tab-1, #confirm-dialog-tab-2');

                                    $tabs.each(function(){
                                        var el=$(this);
                                        el.productSlider({
                                            row:        el.find('.product-grid-table-row'),
                                            cell:       el.find('.product-grid-table-cell__i'),
                                            showSlides: 3
                                        });
                                    });
                                }
                                else if (jQuery(modal).find('.product-grid-heading').length) {
                                    var $list = jQuery(modal).find('.ajax-modal-product-list');

                                    $list.productSlider({
                                        row: $list.find('.product-grid-table-row'),
                                        cell: $list.find('.product-grid-table-cell__i'),
                                        showSlides: 3
                                    });
                                }

                                $(modalOverlay).on('click', function () {
                                    $(minicart).removeClass(minicartActive);
                                    $(minicartDropDown).hide();
                                    $(modal).remove();
                                    $(this).remove();
                                    $('body').removeClass('body_hidden').addClass('body_visible');
                                });
                            }
                            else {
                                if (el.attr('id') === 'addToCart'){
                                    el
                                        .attr('data-message', result.message)
                                        .addClass('add-tooltip');
                                    setTimeout(function () {
                                        el
                                            .removeAttr('data-message')
                                            .removeClass('add-tooltip');
                                    }, 3000);
                                }
                                else if ($('#wishlist').length) {
                                    var message=result.message,
                                        tr=el.closest('.js-wishlist-item');
                                        while (tr.next().hasClass('tr-tooltip')){
                                            tr.next().remove();
                                        }
                                        tr.after("<tr class='tr-tooltip'><td class='td-tooltip' colspan='5'>" + message + "</td></tr>");
                                }
                                else {
                                    $('#errorPopup').html(
                                        "<div class='oggetto-popup__header'>" +
                                        "<a class='oggetto-popup__close-btn js-popup-close-btn' href='#'></a>" +
                                        "</div>" +
                                        "<p class='notify-popup__message'>" + result.message + "</p>"
                                    );
                                    $.fancybox({
                                        href: '#errorPopup',
                                        openEffect: 'fade',
                                        closeEffect: 'fade',
                                        closeBtn: false,
                                        autoSize: false,
                                        width: 'auto',
                                        height: 'auto'
                                    });
                                }
                            }
                        },
                        complete: function () {
                            jQuery.fancybox.hideLoading();
                            self.ajaxRunning = false;
                        }
                    });
                }
            });
        },
        removeProduct: function () {
            var self                    = this,
                minicart                = '#mcart',
                modal                   = '#ajaxModal',
                minicartDropDown        = '#mcartDropdown',
                minicartActive          = 'minicart_active',
                modalOverlay            = '#ajaxModalOverlay',
                modalTabs               = '#confirm-dialog-tabs',
                bigCart                 = '#bigCart',
                modalOverlay            = '#ajaxModalOverlay',
                modalTabs               = '#confirm-dialog-tabs',
                removeProduct           = '.js-remove-product',
                ajaxRunning             = false;

            $(document).on('click', removeProduct, function (e) {
                e.preventDefault();
                if (!self.ajaxRunning) {
                    self.ajaxRunning = true;
                    var href = $(this).attr('href') + 'ajax/1/big/1';
                    $.ajax({
                        type: "POST",
                        url: href,
                        beforeSend: function () {
                            jQuery.fancybox.showLoading();
                        },
                        success: function (result) {
                            if (result.status == 'success') {

//                            if big cart exists
                                if ($(bigCart).length) {
                                    $(bigCart).html(result['content'].big_cart);
                                    $(minicart).replaceWith(result['content'].cart);
                                }
//                            if modal exists
                                else if ($(modal).length) {
                                        $(modal).appendTo($('body'));
                                        $(minicart).replaceWith(result['content'].cart);
                                        if (!$(minicart).hasClass('no-count')){
                                            $(minicart)
                                                .addClass(minicartActive)
                                                .append($(modal));
                                            $(minicartDropDown).css('display', 'block');
                                        }
                                        else {
                                            $(modal).remove();
                                            $(modalOverlay).remove();
                                            $('body').removeClass('body_hidden').addClass('body_visible');
                                        }
                                }
                                else {
                                    $(minicart).replaceWith(result['content'].cart);
//                                if no modal and no goods
                                    if ($(minicart).hasClass('no-count')) {
                                        $(modalOverlay).remove();
                                        $('body').removeClass('body_hidden').addClass('body_visible');
                                    }
//                                if no modal and overlay exists
                                    else if ($(modalOverlay).length) {
                                        $(minicart).addClass(minicartActive);
                                        $(minicartDropDown).css('display', 'block');
                                    }
//                                if no modal and no overlay
                                    else {
                                        $(minicart)
                                            .removeClass(minicartActive)
                                            .css('border-color', "#ddd");
                                        $(minicartDropDown).css('display', 'block');
                                    }
                                }
                            }
                        },
                        complete: function () {
                            jQuery.fancybox.hideLoading();
                            self.ajaxRunning = false;
                        }
                    });
                }
            });
        },
        initCartAnimation: function () {
            var self                = this,
                minicart            = '#mcart',
                minicartDropDown    = '#mcartDropdown',
                minicartActive      = 'minicart_active',
                isAnimating         =  false;

            $('#heading').on('mouseenter mouseleave', minicart, function (e) {
                if ($(minicartDropDown).length) {
                    if (e.type === 'mouseenter') {
                        if (!$(minicart).hasClass(minicartActive) && !self.isAnimating) {
                            self.isAnimating = true;
                            $(this).css('border-color', '#ddd');
                            $(minicartDropDown).slideDown(200, function () {
                                self.isAnimating = false;
                            });
                        }
                    }
                    else {
                        if (!$(minicart).hasClass(minicartActive) && !self.isAnimating) {
                            self.isAnimating = true;
                            $(minicartDropDown).slideUp(200, function () {
                                $(minicart).css('border-color', 'transparent');
                                self.isAnimating = false;
                            });
                        }
                    }
                }
            });
        }
    };

    ajaxCart.init();
});