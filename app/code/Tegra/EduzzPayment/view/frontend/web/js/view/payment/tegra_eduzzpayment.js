/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'tegra_eduzzpayment',
                component: 'Tegra_EduzzPayment/js/view/payment/method-renderer/tegra_eduzzpayment'
            }
        );
        
        return Component.extend({});
    }
);
